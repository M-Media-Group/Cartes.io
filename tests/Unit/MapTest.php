<?php

namespace Tests\Unit;

use Tests\TestCase;

class MapTest extends TestCase
{

    protected $map;

    protected function setUp(): void
    {
        parent::setUp();

        $post = \App\Models\Map::firstOrCreate();
        $this->map = $post;
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeSingleMapTest()
    {
        $response = $this->get('/maps/' . $this->map->uuid);

        $response->assertStatus(200);

        $response = $this->getJson('/api/maps/' . $this->map->uuid);
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
        $response = $this->get('/embeds/maps/' . $this->map->uuid . '#2/43.7/7.3');

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
        $response->assertStatus(200, 'Maps index page should return a 200');

        $response = $this->getJson('/api/maps');
        $response->assertStatus(200);
        $response->assertDontSee('token');
    }
}
