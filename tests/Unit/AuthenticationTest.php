<?php

namespace Tests\Unit;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * @todo change the firstOrCreates to use a factory
 */
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
        $response = $this->actingAs(\App\Models\User::firstOrCreate([
            'email' => 'test@test.com',
            'username' => 'test',
            'password' => 'test',
        ]));
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
            'email' => \App\Models\User::firstOrCreate([
                'email' => 'test@test.com',
                'username' => 'test',
                'password' => 'test',
            ])->email,
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
    }

    /**
     * Test registering a new user
     *
     * @return string
     */
    public function testRegisterNewUser()
    {
        Notification::fake();

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

        // Assert notification was sent
        $user = \App\Models\User::where('email', $passAndEmail)->first();
        Notification::assertSentTo($user, VerifyEmail::class);

        return $passAndEmail;
    }

    /**
     * Test able to log in
     *
     * @depends testRegisterNewUser
     * @param string $email The email to use to log in filled automatically by PHPunit using the @depends
     * @return void
     */
    public function testLoginNewUser(string $email)
    {
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)->post('/login', [
            'email' => $email,
            'password' => $email,
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();
    }

    /**
     * Test able to reset password
     *
     * @return void
     */
    public function testResetPassword()
    {
        Notification::fake();

        $user = \App\Models\User::firstOrCreate([
            'email' => 'test@test.com',
            'username' => 'test',
            'password' => 'test',
        ]);
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)->post('/password/email', [
            'email' => $user->email,
        ]);
        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        // Assert notification was sent
        Notification::assertSentTo($user, ResetPassword::class);
    }

    /**
     * Test able to get details about self
     *
     * @return void
     */
    public function testGetUserDetails()
    {
        $user = \App\Models\User::firstOrCreate([
            'email' => 'test@test.com',
            'username' => 'test',
            'password' => 'test',
        ]);

        /**
         * @var \Illuminate\Contracts\Auth\Authenticatable
         */
        $user = $user->assignRole('admin')->givePermissionTo('create markers in bulk');

        $response = $this->actingAs($user, 'api')->getJson('/api/user');

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $user->id,
            'email' => $user->email,
            'email_verified_at' => false,
            'is_public' => false,
            'username' => $user->username,
            'roles' => [
                [
                    'name' => 'admin',
                ],
            ],
            'permissions' => [
                [
                    'name' => 'create markers in bulk',
                ],
            ],
        ]);
    }

    /**
     * Test making yourself public, which should also send a notification
     *
     * @return void
     */
    public function testMakeUserPublic()
    {
        Notification::fake();

        $user = \App\Models\User::factory()->create([
            'username' => 'testForPublicProfile' . time(),
            'is_public' => false,
        ]);

        // Confirm user is not public
        $this->assertFalse($user->is_public);

        $this->actingAs($user, 'api');

        $response = $this->putJson('/api/user', [
            'email' => $user->email,
            'username' => $user->username,
            'is_public' => true
        ]);

        $response->assertStatus(200);

        // Assert notification was sent
        Notification::assertSentTo($user, \App\Notifications\ProfileMadePublic::class);
    }
}
