<?php

namespace Tests\Unit;

use App\Helpers\MapImageGenerator;
use App\Jobs\FillMissingLocationGeocodes;
use App\Jobs\FillMissingMarkerElevation;
use App\Models\User;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

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
     * Test create a map from a GPX file.
     *
     * @return void
     */
    public function testCreateMapFromGpxTest()
    {
        $this->withoutExceptionHandling();

        // Skip any dispatched jobs
        $this->expectsJobs([
            FillMissingMarkerElevation::class,
            FillMissingLocationGeocodes::class,
        ]);

        // We need to clean up the database before we start
        DB::table('markers')->delete();
        DB::table('marker_locations')->delete();

        $file = new UploadedFile(base_path('tests/fixtures/ashland.gpx'), 'ashland.gpx', 'application/gpx+xml', null, true);

        $user = User::factory()->create();

        /**
         * @var \Illuminate\Contracts\Auth\Authenticatable
         */
        $user = $user->givePermissionTo('create markers in bulk');

        $this->actingAs($user, 'api');

        $response = $this->postJson('/api/maps/file', [
            'file' => $file,
            'user_id' => $user->id,
        ]);

        // Assert returns 201
        $response->assertStatus(201);

        // Assert that a map has been created
        $this->assertDatabaseHas('maps', [
            'title' => 'Rockbuster Duathlon at Ashland State Park',
            'description' => 'Team TopoGrafix tracklogs from the Rockbuster Duathlon at Ashland State Park, April 21st, 2002.  The course consisted of a two-mile run, an seven mile mountain bike course, and a final two-mile run.

Braxton carried an eTrex Venture in his Camelbak for the three laps on the mountain bike loop.  Vil carried his new eTrex Venture on the first run, but the GPS shut off during the first mountain bike loop due to battery vibration.
',
        ]);

        // Assert that the response contains the map token
        $response->assertJsonStructure([
            'uuid',
            'token',
        ]);

        // Get the map by its uuid
        $map = \App\Models\Map::where('uuid', $response->json('uuid'))->first();

        // The ashland DB file shoulda add 11 </trk>, 25 wpt, so we should have 36 markers
        $this->assertEquals(36, $map->markers()->count());

        // The DB file adds 348 trkpt, so we should have 348 locations + 25 wpt
        $this->assertEquals(373, $map->markerLocations()->count());

        // 271 of the locations should have an elevation
        $this->assertEquals(271, $map->markerLocations()->whereNotNull('elevation')->count());

        // 267 should have created_at and updated_at on 2002-04-21 (any time)
        $this->assertEquals(267, $map->markerLocations()->whereDate('marker_locations.created_at', '2002-04-21')->whereDate('marker_locations.updated_at', '2002-04-21')->count());

        // The others should have todays date
        $this->assertEquals(106, $map->markerLocations()->whereDate('marker_locations.created_at', now()->toDateString())->whereDate('marker_locations.updated_at', now()->toDateString())->count());

        // There should be 11 markers with the field "number". It doesn't matter what the value is, just that it exists. This field will be a key in the JSON column called "meta"
        $this->assertEquals(11, $map->markers()->whereNotNull('meta->number')->count());

        // The first marker ordered by ID should only have 1 location
        $this->assertEquals(1, $map->markers()->orderBy('id', 'asc')->first()->locations()->count());

        // The first marker (ordered by ID) should have a current location of lat="42.246950" lon="-71.461807"
        $this->assertEquals(42.246950, $map->markers()->orderBy('id', 'asc')->first()->currentLocation->y);
        $this->assertEquals(-71.461807, $map->markers()->orderBy('id', 'asc')->first()->currentLocation->x);

        // The last marker (ordered by ID) should have a current location of  lat="42.244620" lon="-71.468704", and elevation of 63.584351
        $this->assertEquals(42.244620, $map->markers()->orderBy('id', 'desc')->first()->currentLocation->y);
        $this->assertEquals(-71.468704, $map->markers()->orderBy('id', 'desc')->first()->currentLocation->x);
        // We must round up from 63.584351 to 64 for the elevation
        $this->assertEquals(64, $map->markers()->orderBy('id', 'desc')->first()->currentLocation->z);
        // The last marker should have a total of 59 locations
        $this->assertEquals(59, $map->markers()->orderBy('id', 'desc')->first()->locations()->count());
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

        // Set the user_id on the map
        $this->map->user_id = $user->id;
        $this->map->save();

        // Assert the map is claimed, e.g. the user_id is not null
        $this->assertDatabaseHas('maps', [
            'uuid' => $this->map->uuid,
            'user_id' => $user->id,
        ]);

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
