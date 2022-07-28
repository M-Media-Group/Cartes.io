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

Route::get('maps/{map}/related', 'MapController@related');

Route::apiResource('maps', 'MapController');

Route::get('markers', 'MarkerController@index');

Route::middleware('throttle:markers')->group(function () {
    Route::put('maps/{map}/markers/{marker}', 'MarkerController@update');
    Route::delete('maps/{map}/markers/{marker}', 'MarkerController@destroy');
});

Route::middleware('throttle:markers')->group(function () {
    Route::post('maps/{map}/markers', 'MarkerController@store');
});

Route::middleware(['throttle:markers', 'auth:api'])->group(function () {
    Route::post('maps/{map}/markers/bulk', 'MarkerController@storeInBulk');
});
