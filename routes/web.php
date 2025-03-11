<?php

use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Http\Controllers\ChatController;
use Unusualify\Modularity\Http\Controllers\ProcessController;
use Unusualify\Modularity\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
| Middlewares : [ 'web.auth', ...\Unusualify\Modularity\Facades\ModularityRoutes::defaultMiddlewares(), \Unusualify\Modularity\Facades\ModularityRoutes::defaultPanelMiddlewares()]
|
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::resource('', 'DashboardController', ['as' => 'dashboard', 'names' => ['index' => 'dashboard']])->only(['index']);

Route::singleton('profile', 'ProfileController', ['names' => ['edit' => 'profile']]);
Route::group(['prefix' => 'profile', 'as' => 'profile.'], function () {
    Route::get('show', [ProfileController::class, 'display'])->name('show');
});
Route::put('profile/company', 'ProfileController@updateCompany')->name('profile.company');

Route::get('users/impersonate/stop', 'ImpersonateController@stopImpersonate')->name('impersonate.stop');
Route::get('users/impersonate/{id}', 'ImpersonateController@impersonate')->name('impersonate');

// system internal api routes (for ajax web routes)
Route::prefix('api')->group(function () {
    Route::group(['prefix' => 'chatable', 'as' => 'chatable.', 'controller' => ChatController::class], function () {
        Route::get('{chat}', 'index')->name('index');
        Route::get('{chat}/attachments', 'attachments')->name('attachments');
        Route::get('{chat}/pinned-message', 'pinnedMessage')->name('pinned-message');
        Route::post('{chat}', 'store')->name('store');
        Route::put('{chat_message}', 'update')->name('update');
        Route::get('show/{chat_message}', 'show')->name('show');
        Route::delete('destroy/{chat_message}', 'destroy')->name('destroy');
    });

    Route::group(['prefix' => 'process', 'as' => 'process.'], function () {
        Route::get('{process}', [ProcessController::class, 'show'])->name('show');
        Route::put('{process}', [ProcessController::class, 'update'])->name('update');
    });

    if (modularityConfig('enabled.media-library')) {
        Route::group(['prefix' => 'media-library', 'as' => 'media-library.'], function () {
            Route::post('sign-s3-upload', ['as' => 'sign-s3-upload', 'uses' => 'MediaLibraryController@signS3Upload']);
            Route::get('sign-azure-upload', ['as' => 'sign-azure-upload', 'uses' => 'MediaLibraryController@signAzureUpload']);
            Route::put('medias/single-update', ['as' => 'media.single-update', 'uses' => 'MediaLibraryController@singleUpdate']);
            Route::put('medias/bulk-update', ['as' => 'media.bulk-update', 'uses' => 'MediaLibraryController@bulkUpdate']);
            Route::put('medias/bulk-delete', ['as' => 'media.bulk-delete', 'uses' => 'MediaLibraryController@bulkDelete']);
            Route::get('medias/tags', ['as' => 'media.tags', 'uses' => 'MediaLibraryController@tags']);
            Route::resource('medias', 'MediaLibraryController', ['names' => 'media', 'only' => ['index', 'store', 'destroy']]);
        });
    }

    if (modularityConfig('enabled.file-library')) {
        Route::group(['prefix' => 'file-library', 'as' => 'file-library.'], function () {
            Route::post('sign-s3-upload', ['as' => 'sign-s3-upload', 'uses' => 'FileLibraryController@signS3Upload']);
            Route::get('sign-azure-upload', ['as' => 'sign-azure-upload', 'uses' => 'FileLibraryController@signAzureUpload']);
            Route::put('files/single-update', ['as' => 'file.single-update', 'uses' => 'FileLibraryController@singleUpdate']);
            Route::put('files/bulk-update', ['as' => 'file.bulk-update', 'uses' => 'FileLibraryController@bulkUpdate']);
            Route::put('files/bulk-delete', ['as' => 'file.bulk-delete', 'uses' => 'FileLibraryController@bulkDelete']);
            Route::get('files/tags', ['as' => 'file.tags', 'uses' => 'FileLibraryController@tags']);
            Route::resource('files', 'FileLibraryController', ['names' => 'file', 'only' => ['index', 'store', 'destroy']]);
        });
    }
});
