<?php

namespace Tests\Unit;

use Tests\TestCase;

class MapTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeSingleMapTest()
    {
        $post = \App\Models\Map::firstOrFail();
        $response = $this->get('/maps/' . $post->uuid . '#2/43.7/7.3');
        $response->assertStatus(200);

        $response = $this->get('/api/maps/' . $post->uuid);
        $response->assertStatus(200);
        $response->assertDontSee('token');
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeSingleMapEmbedTest()
    {
        $post = \App\Models\Map::firstOrFail();
        $response = $this->get('/embeds/maps/' . $post->uuid . '#2/43.7/7.3');

        $response->assertStatus(200);
        $response->assertDontSee('map-token');
        $response->assertDontSee('map_token');
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeAllMapsTest()
    {
        $response = $this->get('/maps');
        $response->assertStatus(200);

        $response = $this->get('/api/maps');
        $response->assertStatus(200);
        $response->assertDontSee('token');
    }
}
