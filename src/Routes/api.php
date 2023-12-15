<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Api Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['as' => 'api.lang.', 'namespace' => 'API'], function(){
    Route::apiResource('languages', LanguageController::class);
});

if (config(unusualBaseKey() . '.enabled.media-library')) {
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

if (config(unusualBaseKey() . '.enabled.file-library')) {
    Route::group(['prefix' => 'file-library', 'as' => 'file-library.'], function () {
        Route::post('sign-s3-upload', ['as' => 'sign-s3-upload', 'uses' => 'FileLibraryController@signS3Upload']);
        Route::get('sign-azure-upload', ['as' => 'sign-azure-upload', 'uses' => 'FileLibraryController@signAzureUpload']);
        Route::put('files/single-update', ['as' => 'files.single-update', 'uses' => 'FileLibraryController@singleUpdate']);
        Route::put('files/bulk-update', ['as' => 'files.bulk-update', 'uses' => 'FileLibraryController@bulkUpdate']);
        Route::put('files/bulk-delete', ['as' => 'files.bulk-delete', 'uses' => 'FileLibraryController@bulkDelete']);
        Route::get('files/tags', ['as' => 'files.tags', 'uses' => 'FileLibraryController@tags']);
        Route::resource('files', 'FileLibraryController', ['only' => ['index', 'store', 'destroy']]);
    });
}
