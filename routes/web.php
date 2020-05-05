<?php

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

Route::get('/notifications', function () {
    return view('notifications');
})->middleware('auth');

Auth::routes(['verify' => true]);

Route::get('/', 'HomeController@index')->name('home');

//Route::resource('posts', 'PostController');

Route::resource('categories', 'CategoryController');

Route::resource('users', 'UserController');

Route::post('me/apply/reporter', 'UserController@applyForReporter');

Route::resource('roles', 'RoleController');

Route::resource('incidents', 'IncidentController');

Route::post('/maps', 'MapController@store');

Route::get('/maps/{map}', 'MapController@show');

Route::get('/embeds/maps/{map}', 'MapController@showEmbed');

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
