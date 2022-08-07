<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::get('/privacy-policy', function () {
    return view('privacy');
});

Route::get('/terms-and-conditions', function () {
    return view('toc');
});

Route::get('/apply', function () {
    return view('write');
})->middleware('auth');

Route::get('csrf-token', function () {
    return csrf_token();
});

// Route::get('/about', function () {
//     return view('about');
// });

Route::middleware(ProtectAgainstSpam::class)->group(function () {
    Auth::routes(['verify' => true]);
});

Route::resources([
    'roles' => 'RoleController',
    'categories' => 'CategoryController',
]);

Route::post('me/apply/reporter', 'UserController@applyForReporter');

Route::get('/embeds/maps/{map}', 'MapController@showEmbed');

if (config('app.spa_url')) {
    // Catch all and redirect to web-app
    Route::get('{any}', function () {
        return redirect(config('app.spa_url'), 301);
    })->where('any', '.*');
}
