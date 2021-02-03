<?php

namespace Tests\Unit;

use Route;
use Tests\TestCase;

class WebRouteTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testAllRoutesWithoutDynamicParams()
    {
        $routeCollection = Route::getRoutes();
        $this->withoutEvents();
        $blacklist = [
            // 'about',
        ];
        $dynamicReg = "/{\\S*}/"; //used for omitting dynamic urls that have {} in uri (http://laravel-tricks.com/tricks/adding-a-sitemap-to-your-laravel-application#comment-1830836789)
        $this->be(\App\Models\User::firstOrFail());
        foreach ($routeCollection as $route) {
            if (!preg_match($dynamicReg, $route->uri()) &&
                in_array('GET', $route->methods()) &&
                !in_array($route->uri(), $blacklist)
            ) {
                $start = $this->microtimeFloat();
                // fwrite(STDERR, print_r('test ' . $route->uri() . "\n", true));
                $response = $this->call('GET', $route->uri());
                $end = $this->microtimeFloat();
                $temps = round($end - $start, 3);
                // fwrite(STDERR, print_r('time: ' . $temps . "\n", true));
                $this->assertLessThan(15, $temps, "too long time for " . $route->uri());
                try {
                    $this->assertNotEquals(500, $response->getStatusCode(), $route->uri() . "failed to load");
                    $this->assertNotEquals(404, $response->getStatusCode(), $route->uri() . "failed to load");
                } catch (\Exception $e) {
                    fwrite(STDERR, print_r("\n" . $e . "\n", true));
                    continue;
                }
            }

        }
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testNotFoundTest()
    {
        $response = $this->get('/404');

        $response->assertNotFound();
    }

    public function microtimeFloat()
    {
        list($usec, $asec) = explode(" ", microtime());

        return ((float) $usec + (float) $asec);

    }
}
