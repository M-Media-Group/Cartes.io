<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('categories/{category}/related', 'CategoryController@related');

// Route::resource('users', 'UserController');

Route::get('maps/{map}/markers', 'MarkerController@indexByMap');

Route::get('maps/{map}', 'MapController@show');

Route::get('maps/{map}/related', 'MapController@related');

Route::get('maps', 'MapController@index');

Route::get('markers', 'MarkerController@index');

Route::middleware(['throttle:5|10,1'])->group(function () {
    // Route::apiResource('maps', 'MapController');
    Route::post('maps', 'MapController@store');
});

Route::middleware(['throttle:30'])->group(function () {
    Route::put('maps/{map}', 'MapController@update');
    Route::put('maps/{map}/markers/{marker}', 'MarkerController@update');
    Route::delete('maps/{map}/markers/{marker}', 'MarkerController@destroy');
    Route::delete('maps/{map}', 'MapController@destroy');
});

Route::middleware('throttle:15|120')->group(function () {
    Route::post('maps/{map}/markers', 'MarkerController@store');
});

Route::middleware(['throttle:15', 'auth:api'])->group(function () {
    Route::post('maps/{map}/markers/bulk', 'MarkerController@storeInBulk');
});
