<?php

namespace Tests\Feature;

//use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    //use RefreshDatabase;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSeeText(config('app.name'));
    }
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testNotFoundTest()
    {
        $response = $this->get('/404');

        $response->assertNotFound();
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeAllPostsTest()
    {
        $response = $this->get('/posts');

        $response->assertStatus(200);
        $response->assertSeeText(config('app.name'));
        $response->assertSeeText('Next');
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeLoginTest()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSeeText(config('app.name'));
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testRedirectLoggedOutUserTest()
    {
        $response = $this->get('/home');

        $response->assertRedirect('/login');
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeRegisterTest()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSeeText(config('app.name'));
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeHomepageTest()
    {
        $response = $this->actingAs(\App\User::firstOrFail())->get('/home');

        $response->assertStatus(200);
        $response->assertSeeText(config('app.name'));
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeSinglePostTest()
    {
        $post = \App\Post::firstOrFail();
        $response = $this->get('/posts/' . $post->slug);

        $response->assertStatus(200);
        $response->assertSeeText(config('app.name'));
    }
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeCreatePostTest()
    {
        $response = $this->actingAs(\App\User::firstOrFail())->get('/posts/create');

        $response->assertStatus(200);
        $response->assertSeeText(config('app.name'));
    }
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeEditPostTest()
    {
        $post = \App\Post::firstOrFail();
        $response = $this->actingAs(\App\User::firstOrFail())->get('/posts/' . $post->slug . '/edit');

        $response->assertStatus(200);
        $response->assertSeeText(config('app.name'));
    }
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeAllCategoriesTest()
    {
        $response = $this->get('/categories');

        $response->assertStatus(200);
        $response->assertSeeText(config('app.name'));
    }
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeSingleCateogryTest()
    {
        $category = \App\Category::firstOrFail();
        $response = $this->get('/categories/' . $category->slug);

        $response->assertStatus(200);
        $response->assertSeeText(config('app.name'));
    }
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeCreateCateogryTest()
    {
        $response = $this->actingAs(\App\User::firstOrFail())->get('/categories/create');

        $response->assertStatus(200);
        $response->assertSeeText(config('app.name'));
    }
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeEditCateogryTest()
    {
        $category = \App\Category::firstOrFail();
        $response = $this->actingAs(\App\User::firstOrFail())->get('/categories/' . $category->slug . '/edit');

        $response->assertStatus(200);
        $response->assertSeeText(config('app.name'));
    }
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeSingleUserTest()
    {
        $user = \App\User::firstOrFail();
        $response = $this->get('/users/' . $user->username);

        $response->assertStatus(200);
        $response->assertSeeText(config('app.name'));
    }
}
