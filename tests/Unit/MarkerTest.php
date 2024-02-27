<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Marker;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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

        $marker->currentLocation()->create(['location' => new \MatanYadaev\EloquentSpatial\Objects\Point(45, 45)]);
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

        $response->assertDontSee(['token', 'user_id', 'map_id', 'bulk_insert_id']);

        // Assert see in each marker that a key of coordinates exists
        $response->assertJsonStructure([
            '*' => [
                'id',
                'description',
                'link',
                'created_at',
                'zoom',
                'expires_at',
                'meta',
                'elevation',
                'address',
                'locations_count',
                'heading',
                'pitch',
                'roll',
                'speed',
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
     * Test that its possible to see markes in GeoJSON format when format=geojson is passed
     *
     * @return void
     */
    public function testSeeAllMapMarkersInGeoJsonFormat()
    {
        $response = $this->getJson('/api/maps/' . $this->map->uuid . '/markers?format=geojson');
        $response->assertOk();

        $response->assertDontSee(['token', 'user_id', 'map_id', 'bulk_insert_id']);

        $response->assertJsonStructure([
            'type',
            'features' => [
                '*' => [
                    'type',
                    'geometry' => [
                        'type',
                        // The coordinates should be an array of three numbers, the first two being the lat and lng and the third being the elevation
                        'coordinates' => [
                            0,
                            0,
                            0,
                        ],
                    ],
                    'id',
                    'properties' => [
                        'description',
                        'link',
                        'created_at',
                        'zoom',
                        'expires_at',
                        'meta',
                        'address',
                        'locations_count',
                        'heading',
                        'pitch',
                        'roll',
                        'speed',
                        'category' => [
                            'name',
                        ],
                    ],
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

        // Random lat and lng
        $marker['lat'] = 40.139;
        $marker['lng'] = 44.139;

        $response = $this->postJson('/api/maps/' . $this->map->uuid . '/markers', $marker->toArray());
        $response->assertStatus(201);
        $response->assertSee(['token', 'location', 'id', 'address', 'elevation']);

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

        // Random lat and lng
        $marker['lat'] = 27.139;
        $marker['lng'] = 27.139;


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

        // Random lat and lng
        $marker['lat'] = 45.139;
        $marker['lng'] = 45.139;

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
            'user_id' => $user->id
        ]);

        $this->assertDatabaseHas('marker_locations', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test creating markers in bulk by where the markers have many locations
     *
     * @return void
     */
    public function testCreateMarkerInBulkWithMultipleLocations()
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

        // Create a first location with a location, heading, pitch, roll, speed and zoom
        $firstLocation = [
            'lat' => 43,
            'lng' => 43,
            'heading' => 145,
            'pitch' => 55,
            'roll' => 25,
            'speed' => 35,
            'zoom' => 5,
            'elevation' => 15,
        ];

        // The second location will just have a location
        $secondLocation = [
            'lat' => 49.1,
            'lng' => 49.1,
            'heading' => 115,
        ];

        $locations = [$firstLocation, $secondLocation];

        $marker['locations'] = $locations;

        $markers = ['markers' => [$marker->toArray()]];

        $response = $this->postJson('/api/maps/' . $this->map->uuid . '/markers/bulk', $markers);
        $response->assertStatus(200);

        // Assert added to DB
        $this->assertDatabaseHas('markers', [
            'description' => $marker['description'],
            'category_id' => $marker['category_id'],
            'user_id' => $user->id
        ]);

        // Assert marker locations are added to the database
        foreach ($locations as $location) {
            $this->assertDatabaseHas('marker_locations', [
                'user_id' => $user->id,
                'heading' => $location['heading'] ?? null,
                'pitch' => $location['pitch'] ?? null,
                'roll' => $location['roll'] ?? null,
                'speed' => $location['speed'] ?? null,
                'zoom' => $location['zoom'] ?? null,
            ]);
        }
    }


    /**
     * Test creating markers in bulk by uploading a GPX file
     *
     * @return void
     */
    public function testCreateMarkerInBulkWithGpxFile()
    {
        // We need to clean up the database before we start
        DB::table('markers')->delete();
        DB::table('marker_locations')->delete();

        $map = new \App\Models\Map();
        $map->users_can_create_markers = 'yes';
        $map->options = ['links' => 'optional'];
        $map->save();

        $user = User::factory()->create();

        /**
         * @var \Illuminate\Contracts\Auth\Authenticatable
         */
        $user = $user->givePermissionTo('create markers in bulk');

        $this->actingAs($user, 'api');

        $gpx = file_get_contents(base_path('tests/fixtures/ashland.gpx'));

        $file = UploadedFile::fake()->createWithContent('ashland.gpx', $gpx, 'application/gpx+xml');

        $response = $this->postJson('/api/maps/' . $map->uuid . '/markers/file', ['file' => $file]);
        $response->assertStatus(200);

        // The ashland DB file shoulda add 11 </trk>, 25 wpt, so we should have 36 markers
        $this->assertEquals(36, $map->markers()->count());

        // The DB file adds 348 trkpt, so we should have 348 locations + 25 wpt
        $this->assertEquals(373, $map->markerLocations()->count());
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

        // Random lat and lng
        $marker['lat'] = 20.139;
        $marker['lng'] = 54.139;

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

        // We need to make sure no new location is created when we update the marker
        $markerLocationCount = $marker->locations()->count();

        $response = $this->putJson('/api/maps/' . $this->map->uuid . '/markers/' . $marker->id . '?token=' . $marker->token, [
            'description' => 'New description',
        ]);

        $response->assertStatus(200);
        $response->assertSee('New description');

        $this->assertDatabaseHas('markers', [
            'id' => $marker->id,
            'description' => 'New description',
        ]);

        // Assert no new location was created
        $this->assertEquals($markerLocationCount, $marker->locations()->count());
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

        $response->assertJsonStructure([
            '*' => [
                'location',
                'created_at',
                'heading',
                'pitch',
                'roll',
                'speed',
                'zoom',
                'elevation',
            ],
        ]);
    }

    /**
     * Test see computed marker location fields of `inbound_course`, `outbound_course` and `groundspeed`
     *
     * @return void
     */
    public function testSeeMarkerLocationComputedFields()
    {
        $marker = $this->map->markers()->firstOrCreate();

        // Wait 0.5 second - this prevents a divide by zero error later on
        sleep(1);

        // We need to add a second location to the marker
        $marker->locations()->create([
            'location' => new \MatanYadaev\EloquentSpatial\Objects\Point(45.1, 45.1),
        ]);

        // We'll use the difference in time between the two points to compute the time elapsed.
        $time = $marker->locations()->first()->created_at->diffInMilliseconds($marker->locations()->latest()->first()->created_at) / 1000;

        // The diff between 45.0 and 45.1 is around 13614 meters in haversine. We can a
        $expectedSpeed = round(13614.576970222 / $time, 9);

        // The diff between 45,45 to 45.1,45.1 gives a course of of 35. something.
        // We will round this to 35
        $expectedCourse = 35.20542639685857;
        $expectedInboundCourse = 215.27619879066634;

        $response = $this->getJson('/api/maps/' . $this->map->uuid . '/markers/' . $marker->id . '/locations?computed_data=true');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            '*' => [
                'inbound_course', // Represents course taken from the previous location to get to this location
                // 'outbound_course', // Represents course taken from this location to get to the next location
                'groundspeed', // Represents the groundspeed between this location and the previous location
            ],
        ]);

        // The first location should have no inbound course or groundspeed. The course should be around 35
        $response->assertJsonFragment([
            'inbound_course' => null,
            // 'outbound_course' => $expectedCourse,
            'groundspeed' => null,
        ]);

        // The second location should have an inbound course of around 35, an outbound course of null and a groundspeed of around 13614
        $response->assertJsonFragment([
            'inbound_course' => $expectedInboundCourse,
            // 'outbound_course' => null,
            'groundspeed' => $expectedSpeed,
        ]);
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

        // We need to make sure no new location is created when we update the marker
        $markerLocationCount = $marker->locations()->count();

        $response = $this->putJson('/api/maps/' . $this->map->uuid . '/markers/' . $marker->id . '?map_token=' . $this->map->token, [
            'is_spam' => true,
        ]);

        $response->assertStatus(200);
        $response->assertSee('is_spam');

        $this->assertDatabaseHas('markers', [
            'id' => $marker->id,
            'is_spam' => true,
        ]);

        // Assert no new location was created
        $this->assertEquals($markerLocationCount, $marker->locations()->count());
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
