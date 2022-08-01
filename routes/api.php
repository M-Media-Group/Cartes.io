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
})->name('user');

Route::get('categories', 'CategoryController@index');

Route::get('categories/{category}/related', 'CategoryController@related');

Route::get('maps/{map}/related', 'MapController@related');

Route::apiResource('maps', 'MapController');

Route::middleware('throttle:markers')->group(function () {
    Route::get('markers', 'MarkerController@indexAll');
    Route::apiResource('maps.markers', 'MarkerController');
    Route::post('maps/{map}/markers/bulk', 'MarkerController@storeInBulk')->middleware('auth:api');
});
