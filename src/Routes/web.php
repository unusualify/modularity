<?php

use Illuminate\Support\Facades\Route;

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

// Route::group(['prefix' => 'user', 'as' => 'user.'], function(){
//     Route::resource('role', RoleController::class);

//     Route::resource('permission', PermissionController::class);
// });
Route::unusualWebRoutes();

Route::group(['prefix' => 'api/user', 'as' => 'api.user.', 'namespace' => 'API'], function(){
    Route::apiResource('role', RoleController::class);

    Route::apiResource('permission', PermissionController::class);
});

if (config('base.enabled.media-library')) {
    Route::group(['prefix' => 'media-library', 'as' => 'media-library.'], function () {
        Route::post('sign-s3-upload', ['as' => 'sign-s3-upload', 'uses' => 'MediaLibraryController@signS3Upload']);
        Route::get('sign-azure-upload', ['as' => 'sign-azure-upload', 'uses' => 'MediaLibraryController@signAzureUpload']);
        Route::put('medias/single-update', ['as' => 'medias.single-update', 'uses' => 'MediaLibraryController@singleUpdate']);
        Route::put('medias/bulk-update', ['as' => 'medias.bulk-update', 'uses' => 'MediaLibraryController@bulkUpdate']);
        Route::put('medias/bulk-delete', ['as' => 'medias.bulk-delete', 'uses' => 'MediaLibraryController@bulkDelete']);
        Route::get('medias/tags', ['as' => 'medias.tags', 'uses' => 'MediaLibraryController@tags']);
        Route::resource('medias', 'MediaLibraryController', ['only' => ['index', 'store', 'destroy']]);
    });
}
