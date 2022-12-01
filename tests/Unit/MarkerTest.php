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
        $marker = $this->map->markers()->create([
            'description' => 'Hungry Pete',
            'category_id' => 1,
        ]);

        $marker->currentLocation()->create(['location' => new \Grimzy\LaravelMysqlSpatial\Types\Point(45, 45)]);
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

        $response->assertDontSee('token');

        // Assert see in each marker that a key of coordinates exists
        $response->assertJsonStructure([
            '*' => [
                'description',
                'link',
                'elevation',
                'location' => [
                    'coordinates',
                ],
                'category' => [
                    'name',
                ],
            ],
        ]);
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

        $response = $this->postJson('/api/maps/' . $this->map->uuid . '/markers', $marker->toArray());
        $response->assertStatus(201);
        $response->assertSee(['token', 'location', 'id']);

        // Assert added to DB
        $this->assertDatabaseHas('markers', [
            'id' => $response->decodeResponseJson()['id'],
            'description' => $marker['description'],
        ]);

        $this->assertDatabaseHas('marker_locations', [
            'marker_id' => $response->decodeResponseJson()['id'],
        ]);
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


        $user = User::factory()->create();

        /**
         * @var \Illuminate\Contracts\Auth\Authenticatable
         */
        $user = $user->givePermissionTo('create markers in bulk');

        $this->actingAs($user, 'api');

        $markers = ['markers' => [$marker->toArray()]];

        $response = $this->postJson('/api/maps/' . $this->map->uuid . '/markers/bulk', $markers);

        $response->assertStatus(200);

        // Assert added to DB
        $this->assertDatabaseHas('markers', [
            'description' => $marker['description'],
            'category_id' => $marker['category_id'],
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('marker_locations', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateMarkerInBulkWithAllMapOptions()
    {
        $map = new \App\Models\Map();
        $map->users_can_create_markers = 'yes';
        $map->options = ['links' => 'optional'];
        $map->save();

        // Get raw factory data
        $marker = Marker::factory()->make();

        $marker['category'] = $marker['category_id'];


        $user = User::factory()->create();

        /**
         * @var \Illuminate\Contracts\Auth\Authenticatable
         */
        $user = $user->givePermissionTo('create markers in bulk');

        $this->actingAs($user, 'api');

        $markers = ['markers' => [$marker->toArray()]];

        $response = $this->postJson('/api/maps/' . $this->map->uuid . '/markers/bulk', $markers);
        $response->assertStatus(200);

        // Assert added to DB
        $this->assertDatabaseHas('markers', [
            'description' => $marker['description'],
            'category_id' => $marker['category_id'],
        ]);
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

        $response = $this->postJson('/api/maps/' . $this->map->uuid . '/markers', $marker->toArray());
        $response->assertStatus(201);
        $response->assertSee('token');

        // Assert category was created
        $this->assertDatabaseHas('categories', [
            'name' => $category->name,
        ]);

        // Get the new category ID
        $category = Category::where('name', $category->name)->first();

        // Assert category was assigned to marker
        $this->assertDatabaseHas('markers', [
            'category_id' => $category->id,
        ]);
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

        $this->assertDatabaseHas('markers', [
            'id' => $marker->id,
            'description' => 'New description',
        ]);
    }

    /**
     * Test getting a markers locations
     *
     * @return void
     */
    public function testShowMarkerLocations()
    {
        $marker = $this->map->markers()->firstOrCreate();

        $response = $this->getJson('/api/maps/' . $this->map->uuid . '/markers/' . $marker->id . '/locations');

        $response->assertStatus(200);
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

        $this->assertDatabaseHas('markers', [
            'id' => $marker->id,
            'is_spam' => true,
        ]);
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

        $this->assertDatabaseMissing('markers', [
            'id' => $marker->id,
        ]);
    }
}
