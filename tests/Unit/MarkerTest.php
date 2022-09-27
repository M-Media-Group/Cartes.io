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
        $this->map = new \App\Models\Map();
        $this->map->users_can_create_markers = 'yes';
        $this->map->save();
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

        $response = $this->getJson('/api/maps/' . $this->map->uuid);
        $response->assertOk();

        $response = $this->getJson('/api/maps/' . $this->map->uuid . '/markers');
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
        $response = $this->postJson('/api/maps/' . $this->map->uuid . '/markers', []);
        $response->assertStatus(422);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateMarker()
    {
        // Get raw factory data
        $marker = Marker::factory()->make();

        $marker['category'] = $marker['category_id'];
        $marker['lat'] = $marker['location']->getLat();
        $marker['lng'] = $marker['location']->getLng();
        $response = $this->postJson('/api/maps/' . $this->map->uuid . '/markers', $marker->toArray());
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
        // Get raw factory data
        $marker = Marker::factory()->make();

        $marker['category'] = $marker['category_id'];
        $marker['lat'] = $marker['location']->getLat();
        $marker['lng'] = $marker['location']->getLng();
        $response = $this->postJson('/api/maps/' . $this->map->uuid . '/markers/bulk', ['markers' => $marker->toArray()]);
        $response->assertStatus(401);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testFailCreateMarkerInBulkUnauthorised()
    {
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
        $response = $this->postJson('/api/maps/' . $this->map->uuid . '/markers/bulk', ['markers' => $marker->toArray()]);
        $response->assertStatus(403);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateMarkerInBulk()
    {
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
        $response = $this->postJson('/api/maps/' . $this->map->uuid . '/markers/bulk', $markers);
        $response->assertStatus(200);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateMarkerWithCategoryName()
    {

        $marker = Marker::factory()->make();

        $category = Category::factory()->make();

        $marker['category_name'] = $category->name;
        $marker['lat'] = $marker['location']->getLat();
        $marker['lng'] = $marker['location']->getLng();
        $response = $this->postJson('/api/maps/' . $this->map->uuid . '/markers', $marker->toArray());
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
        $marker = $this->map->markers()->firstOrCreate();

        $response = $this->putJson('/api/maps/' . $this->map->uuid . '/markers/' . $marker->id . '?token=' . $marker->token, [
            'description' => 'New description',
        ]);

        $response->assertStatus(200);
        $response->assertSee('New description');
    }

    /**
     * Test update a marker by setting is_spam fails when the user themselves is the owner
     *
     * @return void
     */
    public function testUpdateMarkerIsSpamNotAllowedByMarkerAuthor()
    {
        $marker = $this->map->markers()->firstOrCreate();

        $response = $this->putJson('/api/maps/' . $this->map->uuid . '/markers/' . $marker->id . '?token=' . $marker->token, [
            'is_spam' => true,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test update a marker by setting is_spam to true
     *
     * @return void
     */
    public function testUpdateMarkerIsSpam()
    {
        $marker = $this->map->markers()->firstOrCreate();

        $response = $this->putJson('/api/maps/' . $this->map->uuid . '/markers/' . $marker->id . '?map_token=' . $this->map->token, [
            'is_spam' => true,
        ]);

        $response->assertStatus(200);
        $response->assertSee('is_spam');
    }

    /**
     * Test delete marker.
     *
     * @return void
     */
    public function testDeleteMarker()
    {
        $marker = $this->map->markers()->firstOrCreate();

        $response = $this->deleteJson('/api/maps/' . $this->map->uuid . '/markers/' . $marker->id . '?token=' . $marker->token);
        $response->assertStatus(200);
    }
}
