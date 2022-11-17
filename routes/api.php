<?php

use App\Http\Middleware\SetAuthDriverToApi;
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

//  Optionally authenticated routes
Route::middleware(SetAuthDriverToApi::class)->group(function () {
    Route::get('categories/search', 'CategoryController@search');
    Route::get('categories', 'CategoryController@index');

    // If we use the CRUD controller, we need to set the parameter name to model
    // Route::apiResource('categories', 'CategoryController')->parameters([
    //     'categories' => 'model'
    // ]);

    Route::get('categories/{category}/related', 'CategoryController@related');

    Route::get('maps/{map}/related', 'MapController@related');

    Route::get('maps/search', 'MapController@search');
    Route::apiResource('maps', 'MapController');

    Route::apiResource('users', 'UserController')->only(['index', 'show']);

    Route::middleware('throttle:markers')->group(function () {
        Route::get('markers', 'MarkerController@indexAll');
        Route::apiResource('maps.markers', 'MarkerController');
        Route::post('maps/{map}/markers/bulk', 'MarkerController@storeInBulk')->middleware('auth:api');
    });
});

// Authenticated routes
Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->name('user');

    Route::put('/user', 'UserController@updateSelf');

    Route::post('maps/{map}/claim', 'MapController@claim');
    Route::delete('maps/{map}/claim', 'MapController@unclaim');

    Route::apiResource('users', 'UserController')->except(['create', 'index', 'show']);
});

Route::get('{any}', function () {
    // Abort with method not allowed if method is not found
    abort(404);
})->where('any', '.*');
