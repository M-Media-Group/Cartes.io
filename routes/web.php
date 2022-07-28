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

//Route::get('/', 'PostController@index');

Route::get('/privacy-policy', function () {
    return view('privacy');
});

Route::get('/terms-and-conditions', function () {
    return view('toc');
});

Route::get('/apply', function () {
    return view('write');
})->middleware('auth');

Route::get('/about', function () {
    return view('about');
});

Route::middleware(ProtectAgainstSpam::class)->group(function () {
    Auth::routes(['verify' => true]);
});

Route::get('/', 'HomeController@index')->name('home');

//Route::resource('posts', 'PostController');

Route::resources([
    'users' => 'UserController',
    'roles' => 'RoleController',
    'maps' => 'MapController',
    'categories' => 'CategoryController',
]);

Route::post('me/apply/reporter', 'UserController@applyForReporter');

Route::get('/embeds/maps/{map}', 'MapController@showEmbed');

Route::get('/ar/maps/{map}', 'MapController@showAr');

// Route::get('{slug?}', function () {
//     //http://feeds.bbci.co.uk/news/world/rss.xml
//     $feed = Feeds::make([
//         'https://www.reddit.com/r/breakingnews/.rss', 'https://www.reddit.com/r/news/.rss', 'http://feeds.bbci.co.uk/news/world/rss.xml', 'https://news.google.com/rss/topics/CAAqJggKIiBDQkFTRWdvSUwyMHZNRGx1YlY4U0FtVnVHZ0pWVXlnQVAB?hl=en-US&gl=US&ceid=US:en',
//     ]);
//     $data = [
//         'title' => $feed->get_title(),
//         'permalink' => $feed->get_permalink(),
//         'items' => $feed->get_items(),
//     ];

//     return View::make('map', $data);
// });
