<?php

namespace Tests\Unit;

use Tests\TestCase;

class MarkerTest extends TestCase
{

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeAllMapMarkersTest()
    {
        $post = \App\Models\Map::whereHas('markers')->firstOrFail();
        $response = $this->get('/maps/' . $post->uuid);
        $response->assertOk();

        $response = $this->get('/api/maps/' . $post->uuid . '/markers');
        $response->assertOk();
        $response->assertSee('location');
        $response->assertDontSee('token');
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testFailToCreateMarker()
    {
        $post = \App\Models\Map::whereHas('markers')->where('users_can_create_markers', 'yes')->firstOrFail();
        $response = $this->postJson('/api/maps/' . $post->uuid . '/markers', []);
        $response->assertStatus(422);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateMarker()
    {
        $post = \App\Models\Map::whereHas('markers')->where('users_can_create_markers', 'yes')->firstOrFail();
        $marker = factory(\App\Models\Marker::class)->raw();
        $marker['category'] = $marker['category_id'];
        $marker['lat'] = $marker['location']->getLat();
        $marker['lng'] = $marker['location']->getLng();
        $response = $this->postJson('/api/maps/' . $post->uuid . '/markers', $marker);
        $response->assertStatus(201);
        $response->assertSee('token');
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateMarkerWithCategoryName()
    {
        $post = \App\Models\Map::whereHas('markers')->where('users_can_create_markers', 'yes')->firstOrFail();
        $marker = factory(\App\Models\Marker::class)->raw();
        $marker['category_name'] = factory(\App\Models\Category::class)->raw()['name'];
        $marker['lat'] = $marker['location']->getLat();
        $marker['lng'] = $marker['location']->getLng();
        $response = $this->postJson('/api/maps/' . $post->uuid . '/markers', $marker);
        $response->assertStatus(201);
        $response->assertSee('token');
    }
}
