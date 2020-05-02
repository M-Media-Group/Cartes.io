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

Route::resource('users', 'UserController');

Route::get('incidents', 'IncidentController@index');

Route::middleware('throttle:3|10,1')->group(function () {
    // Route::apiResource('maps', 'MapController');
    Route::post('maps', 'MapController@store');
});

Route::middleware('throttle:20|60,1')->group(function () {
    Route::post('incidents', 'IncidentController@store');
    Route::put('maps/{map}', 'MapController@update');
});
