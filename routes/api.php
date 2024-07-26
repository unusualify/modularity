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

Route::group(['as' => 'api.', 'namespace' => 'API'], function(){
    Route::apiResource('languages', LanguageController::class);
});

if (unusualConfig('enabled.media-library')) {
    Route::group(['prefix' => 'media-library', 'as' => 'media-library.'], function () {
        Route::post('sign-s3-upload', ['as' => 'sign-s3-upload', 'uses' => 'MediaLibraryController@signS3Upload']);
        Route::get('sign-azure-upload', ['as' => 'sign-azure-upload', 'uses' => 'MediaLibraryController@signAzureUpload']);
        Route::put('medias/single-update', ['as' => 'media.single-update', 'uses' => 'MediaLibraryController@singleUpdate']);
        Route::put('medias/bulk-update', ['as' => 'media.bulk-update', 'uses' => 'MediaLibraryController@bulkUpdate']);
        Route::put('medias/bulk-delete', ['as' => 'media.bulk-delete', 'uses' => 'MediaLibraryController@bulkDelete']);
        Route::get('medias/tags', ['as' => 'media.tags', 'uses' => 'MediaLibraryController@tags']);
        Route::resource('medias', 'MediaLibraryController',  ['names' => 'media' ,'only' => ['index', 'store', 'destroy']]);
    });
}

if (unusualConfig('enabled.file-library')) {
    Route::group(['prefix' => 'file-library', 'as' => 'file-library.'], function () {
        Route::post('sign-s3-upload', ['as' => 'sign-s3-upload', 'uses' => 'FileLibraryController@signS3Upload']);
        Route::get('sign-azure-upload', ['as' => 'sign-azure-upload', 'uses' => 'FileLibraryController@signAzureUpload']);
        Route::put('files/single-update', ['as' => 'file.single-update', 'uses' => 'FileLibraryController@singleUpdate']);
        Route::put('files/bulk-update', ['as' => 'file.bulk-update', 'uses' => 'FileLibraryController@bulkUpdate']);
        Route::put('files/bulk-delete', ['as' => 'file.bulk-delete', 'uses' => 'FileLibraryController@bulkDelete']);
        Route::get('files/tags', ['as' => 'file.tags', 'uses' => 'FileLibraryController@tags']);
        Route::resource('files', 'FileLibraryController', ['names' => 'file','only' => ['index', 'store', 'destroy']]);
    });
}


Route::group([ 'prefix' => 'filepond'], function(){
    Route::post('process', ['as' => 'filepond.process', 'uses' => 'FilepondController@uploadTempFile'],);
    Route::delete('delete', ['as' => 'filepond.delete', 'uses' => 'FilepondController@deleteTempFile'],);
    Route::get('preview/{id}', ['as' => 'filepond.preview', 'uses' => 'FilepondController@previewAsset'],);
});
