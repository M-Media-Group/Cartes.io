<?php

namespace Tests\Unit;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class WebRouteTest extends TestCase
{

    /**
     * Blacklist routes that should not be tested.
     *
     * @var array
     */
    protected $blacklistedRoutes = [
        'telescope/telescope-api/monitored-tags',
        // 'oauth/authorize',
        // 'oauth/tokens',
        // 'oauth/clients',
        // 'oauth/scopes',
        // 'oauth/personal-access-tokens',
    ];

    /**
     * The routes in the app
     *
     * @var \Illuminate\Routing\RouteCollectionInterface
     */
    private $routes;

    /**
     * The user
     *
     * @var User
     */
    private $user;

    /**
     * Used for omitting dynamic urls that have {} in uri
     *
     * @see http://laravel-tricks.com/tricks/adding-a-sitemap-to-your-laravel-application#comment-1830836789
     * @var string
     */
    private $dynamicRegex = '/{\\S*}/';

    protected function setUp(): void
    {
        parent::setUp();

        $this->routes = Route::getRoutes();

        // Create a new User using the factory
        $this->user = User::factory()->create();
    }

    /**
     * Verify that all public GET routes are available and do not throw an error. The ones that require authnetication should throw a 403
     * Note: this is an example test to show how you can use the testNonSkippedUrls method. Feel free to add your own or replace this one!
     *
     * @return void
     */
    public function testAllGETRoutesWithoutDynamicParams()
    {
        $this->testNonSkippedUrls(function ($response, $route) {
            // If the route is protected by auth middleware, it should return a 302 since we are not logged in
            if (in_array('auth:api', $route->computedMiddleware)) {
                $this->assertEquals(302, $response->getStatusCode(), $route->uri() . 'failed to load');
                $this->assertStringContainsString('Unauthenticated.', $response->exception->getMessage(), $route->uri() . 'failed to redirect to login');
                $this->assertStringContainsString('/login', $response->headers->get('Location'), $route->uri() . 'failed to redirect to login');
            }
        });
    }

    /**
     * Verify that all public GET routes are available and do not throw an error. The ones that require authnetication should throw a 403
     * Note: this is an example test to show how you can use the testNonSkippedUrls method. Feel free to add your own or replace this one!
     *
     * @return void
     */
    public function testAllGETRoutesWithoutDynamicParamsLoggedIn()
    {
        $this->be($this->user);

        $this->testNonSkippedUrls(function ($response, $route) {
            // If the route is protected by auth middleware, it should return a 302 since we are not logged in
            if (in_array('auth:api', $route->computedMiddleware)) {
                $this->assertEquals(302, $response->getStatusCode(), $route->uri() . 'failed to load');
                $this->assertStringContainsString('Unauthenticated.', $response->exception->getMessage(), $route->uri() . 'failed to redirect to login');
                $this->assertStringContainsString('/login', $response->headers->get('Location'), $route->uri() . 'failed to redirect to login');
            }
        });
    }

    /**
     *Verify that a 404 page is not found (e.g. actually loads a 404)
     *
     * @return void
     */
    public function testNotFoundTest()
    {
        $response = $this->get('/404');

        $response->assertNotFound();
    }

    /**
     * Function to test nonSkippedUrls that accepts a callback of the assertions to run on the response of each route.
     *
     * @param Closure $assertions
     * @return void
     */
    private function testNonSkippedUrls($assertions = null)
    {
        // $this->withoutEvents();

        $testedUrls = [];
        foreach ($this->nonSkippedRoutes() as $route) {
            $response = $this->callRoute($route)['response'];
            try {
                $this->assertNotEquals(500, $response->getStatusCode(), $route->uri() . ' failed to load');
                $this->assertNotEquals(404, $response->getStatusCode(), $route->uri() . ' failed to load');
                $assertions($response, $route);
                $testedUrls[] = $route->uri();
            } catch (\Exception $e) {
                fwrite(STDERR, print_r("\n" . $e . "\n", true));
                // Mark the test as failed if the assertion fails
                $this->fail($route->uri() . ' failed to load with error ' . $e->getMessage());
            }
        }
        $this->assertIsArray($testedUrls, 'No URLS tested');
        $this->assertNotEmpty($testedUrls, 'No URLS tested');
        fwrite(STDERR, print_r('tested urls: ' . implode(', ', $testedUrls) . "\n", true));
    }

    private function microtimeFloat()
    {
        [$usec, $asec] = explode(' ', microtime());
        return (float) $usec + (float) $asec;
    }

    private function shouldSkip(\Illuminate\Routing\Route $route): bool
    {
        return preg_match($this->dynamicRegex, $route->uri()) ||
            !in_array('GET', $route->methods()) ||
            in_array($route->uri(), $this->blacklistedRoutes);
    }

    private function callRoute(\Illuminate\Routing\Route $route): array
    {
        $start = $this->microtimeFloat();
        $response = $this->call('GET', $route->uri());
        $response = $this->call('GET', $route->uri());
        $end = $this->microtimeFloat();
        $time = round($end - $start, 3);
        $this->assertLessThan(15, $time, 'too long time for ' . $route->uri());
        return [
            'response' => $response,
            'time' => $time
        ];
    }

    private function nonSkippedRoutes(): array
    {
        $routes = [];
        foreach ($this->routes as &$route) {
            if (!$this->shouldSkip($route)) {
                $routes[] = $route;
            }
        }
        return $routes;
    }
}
