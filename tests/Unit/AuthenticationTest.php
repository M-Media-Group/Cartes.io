<?php

namespace Tests\Unit;

use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function testSeeLoginTest()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSeeText(config('app.name'));
    }

    public function testSeePasswordResetTest()
    {
        $response = $this->get('/password/reset');
        $response->assertStatus(200);
        $response->assertSeeText(config('app.name'));
    }

    public function testRedirectFromLoginPageWhenLoggedInTest()
    {
        $response = $this->actingAs(\App\Models\User::firstOrFail());
        $response = $this->get('/login');
        $response->assertStatus(302);
    }

    public function testRedirectLoggedOutUserTest()
    {
        $response = $this->get('/email/verify');
        $response->assertRedirect('/login');
    }

    public function testSeeRegisterTest()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    public function testLoginHoneypot()
    {
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)->post('/login', [
            'email' => 'test@test.com',
            'password' => 'test',
            'my_name' => 'test', // Honeypot field
        ]);
        $response->assertStatus(200);
    }

    public function testCSRFToken()
    {
        $response = $this->post('/login', []);
        $response->assertStatus(419);
    }

    public function testLoginShowsValidationErrors()
    {
        $response = $this->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class, \Spatie\Honeypot\ProtectAgainstSpam::class])->post('/login', []);
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email', 'password']);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)->post('/login', [
            'email' => \App\Models\User::firstOrFail()->email,
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
    }

    /**
     * Test registering a new user
     *
     * @return void
     */
    public function testRegisterNewUser()
    {
        $passAndEmail = 'asdad' . time() . '@test.com';
        $this->withoutExceptionHandling();
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class, \Spatie\Honeypot\ProtectAgainstSpam::class)->post('/register', [
            'username' => 'TestUser' . rand(1, 1000),
            'email' => $passAndEmail,
            'password' => $passAndEmail,
            'password_confirmation' => $passAndEmail,
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        // Assert the user is in the DB
        $this->assertDatabaseHas('users', [
            'email' => $passAndEmail,
        ]);
    }
}
