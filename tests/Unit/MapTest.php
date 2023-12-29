<?php

namespace Tests\Unit;

use App\Helpers\MapImageGenerator;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Tests\TestCase;

class MapTest extends TestCase
{

    protected $map;

    protected function setUp(): void
    {
        parent::setUp();

        $post = \App\Models\Map::create();
        $this->map = $post;

        $this->withoutExceptionHandling();
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
     * Test that it is possible to get the maps static image
     *
     * @todo we need to add separate tests for maps with and without markers
     * @todo add for maps with and without center
     * @todo add for base64 response request
     * @return void
     */
    public function testSeeSingleMapStaticImageTest()
    {
        // We get the default key from the MapImageGenerator
        $mapGenerator = new MapImageGenerator();
        $cacheKey = $mapGenerator->getCacheKey('map', $this->map->uuid);

        // Assert that the cache is empty
        $this->assertFalse(\Illuminate\Support\Facades\Cache::has($cacheKey));

        $response = $this->get('/api/maps/' . $this->map->uuid . '/images/static');

        //  We should get back a PNG image
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');

        // Assert that the cache is now populated
        $this->assertTrue(\Illuminate\Support\Facades\Cache::has($cacheKey));

        // Assert a second request was returned from the cache
        $response = $this->get('/api/maps/' . $this->map->uuid . '/images/static');
        $response->assertStatus(200);
        /** @todo actually test that it came from cache and not from elsewhere */
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
     * Test that a map that has two markers with the same category does not return two duplicate categories
     *
     * @return void
     */
    public function testSeeSingleMapWithDuplicateCategoriesTest()
    {

        $map = \App\Models\Map::create();

        $category = \App\Models\Category::factory()->create();

        \App\Models\Marker::createWithLocation([
            'map_id' => $map->id,
            'category_id' => $category->id,
            'location' => new Point(45, 45),
            'elevation' => 10,
            'zoom' => 10,
            'heading' => 10,
            'pitch' => 10,
            'roll' => 10,
            'speed' => 10,
        ]);

        \App\Models\Marker::createWithLocation([
            'map_id' => $map->id,
            'category_id' => $category->id,
            'location' => new Point(43, 43),
            'elevation' => 10,
            'zoom' => 10,
            'heading' => 10,
            'pitch' => 10,
            'roll' => 10,
            'speed' => 10,
        ]);

        $response = $this->getJson('/api/maps/' . $map->uuid . '?with[]=categories');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'categories');
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
     * Test searching for a map
     *
     * @return void
     */
    public function testSearchMapsTest()
    {
        $response = $this->getJson('/api/maps/search?q=map');
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
     * Test claiming a map
     *
     * @return void
     */
    public function testClaimMapTest()
    {
        // Act as a user
        $user = \App\Models\User::firstOrCreate([
            'username' => 'testuser',
            'email' => 'testuser@test.com',
            'password' => 'testuser',
        ]);

        $this->actingAs($user, 'api');

        // First assert the map is not claimed, e.g. the user_id is null
        $this->assertDatabaseHas('maps', [
            'uuid' => $this->map->uuid,
            'user_id' => null,
        ]);

        $response = $this->postJson('/api/maps/' . $this->map->uuid . '/claim', [
            'map_token' => $this->map->token,
        ]);

        // Assert returns 200
        $response->assertStatus(200);

        // Assert that the response contains the map token
        $response->assertJsonStructure([
            'uuid',
        ]);

        // Assert that in the database the map has been claimed by having user_id set
        $this->assertDatabaseHas('maps', [
            'uuid' => $this->map->uuid,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test un-claiming a map
     *
     * @return void
     */
    public function testUnclaimMapTest()
    {
        // Act as a user
        $user = \App\Models\User::firstOrCreate([
            'username' => 'testuser',
            'email' => 'testuser@test.com',
            'password' => 'testuser',
        ]);
        $this->actingAs($user, 'api');

        $response = $this->deleteJson('/api/maps/' . $this->map->uuid . '/claim');

        // Assert returns 200
        $response->assertStatus(200);

        // Assert that the response contains the map token
        $response->assertJsonStructure([
            'uuid',
        ]);

        // Assert that in the database the map has been claimed by having user_id set
        $this->assertDatabaseHas('maps', [
            'uuid' => $this->map->uuid,
            'user_id' => null,
        ]);
    }

    /**
     * Test updating a map
     *
     * @return void
     */
    public function testUpdateMapTest()
    {
        // Act as a user
        $user = \App\Models\User::firstOrCreate();
        $this->actingAs($user, 'api');

        $response = $this->putJson('/api/maps/' . $this->map->uuid, [
            'title' => 'Test Map',
        ]);

        // Assert returns 200
        $response->assertStatus(200);

        // Assert that the response contains the map token
        $response->assertJsonStructure([
            'uuid',
        ]);

        // Assert that in the database the map has been claimed by having user_id set
        $this->assertDatabaseHas('maps', [
            'uuid' => $this->map->uuid,
            'title' => 'Test Map',
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
