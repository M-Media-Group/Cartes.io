<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Marker;
use App\Models\User;
use Tests\TestCase;

class MarkerTest extends TestCase
{

    protected $map;

    // The setup
    public function setUp(): void
    {
        parent::setUp();
        $this->withoutEvents();
        $this->map = \App\Models\Map::create([
            'users_can_create_markers' => 'yes'
        ]);
        // "lat": 45,
        // "lng": 45,
        // "description": "Hungry Pete",
        // "category": 1
        $this->map->markers()->create([
            'location' => new \Grimzy\LaravelMysqlSpatial\Types\Point(45, 45),
            'description' => 'Hungry Pete',
            'category_id' => 1,
        ]);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSeeAllMapMarkersTest()
    {
        $post = $this->map;

        $response = $this->getJson('/api/maps/' . $post->uuid);
        $response->assertOk();

        $response = $this->getJson('/api/maps/' . $post->uuid . '/markers');
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
        // Get raw factory data
        $marker = Marker::factory()->make();

        $marker['category'] = $marker['category_id'];
        $marker['lat'] = $marker['location']->getLat();
        $marker['lng'] = $marker['location']->getLng();
        $response = $this->postJson('/api/maps/' . $post->uuid . '/markers', $marker->toArray());
        $response->assertStatus(201);
        $response->assertSee('token');
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testFailCreateMarkerInBulkUnauthenticated()
    {
        $post = \App\Models\Map::whereHas('markers')->where('users_can_create_markers', 'yes')->firstOrFail();
        // Get raw factory data
        $marker = Marker::factory()->make();

        $marker['category'] = $marker['category_id'];
        $marker['lat'] = $marker['location']->getLat();
        $marker['lng'] = $marker['location']->getLng();
        $response = $this->postJson('/api/maps/' . $post->uuid . '/markers/bulk', ['markers' => $marker->toArray()]);
        $response->assertStatus(401);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testFailCreateMarkerInBulkUnauthorised()
    {
        $post = \App\Models\Map::whereHas('markers')->where('users_can_create_markers', 'yes')->firstOrFail();
        // Get raw factory data
        $marker = Marker::factory()->make();

        /**
         * @var \Illuminate\Contracts\Auth\Authenticatable
         */
        $user = User::factory()->create();

        $this->actingAs($user, 'api');

        $marker['category'] = $marker['category_id'];
        $marker['lat'] = $marker['location']->getLat();
        $marker['lng'] = $marker['location']->getLng();
        $response = $this->postJson('/api/maps/' . $post->uuid . '/markers/bulk', ['markers' => $marker->toArray()]);
        $response->assertStatus(403);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateMarkerInBulk()
    {
        $post = \App\Models\Map::whereHas('markers')->where('users_can_create_markers', 'yes')->firstOrFail();
        // Get raw factory data
        $marker = Marker::factory()->make();

        $marker['category'] = $marker['category_id'];
        $marker['lat'] = $marker['location']->getLat();
        $marker['lng'] = $marker['location']->getLng();

        $user = User::factory()->create();

        /**
         * @var \Illuminate\Contracts\Auth\Authenticatable
         */
        $user = $user->givePermissionTo('create markers in bulk');

        $this->actingAs($user, 'api');

        $markers = ['markers' => [$marker->toArray()]];

        $marker['category'] = $marker['category_id'];
        $marker['lat'] = $marker['location']->getLat();
        $marker['lng'] = $marker['location']->getLng();
        $response = $this->postJson('/api/maps/' . $post->uuid . '/markers/bulk', $markers);
        $response->assertStatus(200);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateMarkerWithCategoryName()
    {
        $post = \App\Models\Map::whereHas('markers')->where('users_can_create_markers', 'yes')->firstOrFail();

        $marker = Marker::factory()->make();

        $category = Category::factory()->make();

        $marker['category_name'] = $category->name;
        $marker['lat'] = $marker['location']->getLat();
        $marker['lng'] = $marker['location']->getLng();
        $response = $this->postJson('/api/maps/' . $post->uuid . '/markers', $marker->toArray());
        $response->assertStatus(201);
        $response->assertSee('token');
    }

    /**
     * Test update a marker description
     *
     * @return void
     */
    public function testUpdateMarkerDescription()
    {
        $marker = $this->map->markers()->firstOrFail();

        $response = $this->putJson('/api/maps/' . $this->map->uuid . '/markers/' . $marker->id . '?token=' . $marker->token, [
            'description' => 'New description',
        ]);

        $response->assertStatus(200);
        $response->assertSee('New description');
    }

    /**
     * Test delete marker.
     *
     * @return void
     */
    public function testDeleteMarker()
    {
        $marker = $this->map->markers()->firstOrFail();

        $response = $this->deleteJson('/api/maps/' . $this->map->uuid . '/markers/' . $marker->id . '?token=' . $marker->token);
        $response->assertStatus(200);
    }
}
