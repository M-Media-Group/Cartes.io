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
    public function testSeeHomepageTest()
    {
        $response = $this->actingAs(\App\Models\User::firstOrFail())->get('/');
        $response->assertStatus(301);
    }
}
