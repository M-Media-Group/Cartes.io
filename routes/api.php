<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('categories', 'CategoryController@index');

// Route::resource('users', 'UserController');

Route::get('maps/{map}/incidents', 'IncidentController@index');

Route::get('maps/{map}', 'MapController@show');

Route::get('maps', 'MapController@index');

Route::middleware('throttle:4|10,1')->group(function () {
    // Route::apiResource('maps', 'MapController');
    Route::post('maps', 'MapController@store');
});

Route::middleware('throttle:30')->group(function () {
    Route::put('maps/{map}', 'MapController@update');
    Route::delete('maps/{map}/incidents/{incident}', 'IncidentController@destroy');
    Route::delete('maps/{map}', 'MapController@destroy');
});

Route::middleware('throttle:15|120')->group(function () {
    Route::post('maps/{map}/incidents', 'IncidentController@store');
});
