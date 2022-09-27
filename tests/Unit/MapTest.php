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

        $response->assertStatus(301);

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

        $response->assertStatus(301);
        $response->assertDontSee('map-token');
        $response->assertDontSee('map_token');
    }

    /**
     * Test see related maps for a given map
     *
     * @return void
     */
    public function testSeeRelatedMapsTest()
    {
        $response = $this->get('/maps/' . $this->map->uuid . '/related');

        $response->assertStatus(301);

        $response = $this->getJson('/api/maps/' . $this->map->uuid . '/related');
        $response->assertStatus(200);
        $response->assertDontSee('token');
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeAllMapsTest()
    {
        $response = $this->get('/maps');
        $response->assertStatus(301);

        $response = $this->getJson('/api/maps');
        $response->assertStatus(200);
        $response->assertDontSee('token');
    }
    /**
     * Test created a map.
     *
     * @return void
     */
    public function testCreateMapTest()
    {
        $response = $this->postJson('/api/maps');

        // Assert returns 201
        $response->assertStatus(201);

        // Assert that a map has been created
        $this->assertDatabaseHas('maps', [
            'uuid' => $response->json('uuid'),
        ]);

        // Assert that the response contains the map token
        $response->assertJsonStructure([
            'uuid',
            'token',
        ]);
    }

    /**
     * Test deleting a map.
     *
     * @return void
     */
    public function testDeleteMapTest()
    {
        $response = $this->deleteJson('/api/maps/' . $this->map->uuid, [
            'token' => $this->map->token,
        ]);

        // Assert returns 200
        $response->assertStatus(200);

        // Assert there is a message of success = true
        $response->assertJson([
            'success' => true,
        ]);

        // Assert that the map has been deleted
        $this->assertDatabaseMissing('maps', [
            'uuid' => $this->map->uuid,
        ]);
    }
}
