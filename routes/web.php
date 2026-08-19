<?php

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Unusualify\Modularous\Http\Controllers\ArtisanRunner\ArtisanRunnerController;
use Unusualify\Modularous\Http\Controllers\ArtisanRunner\ArtisanRunnerToolController;
use Unusualify\Modularous\Http\Controllers\ModuleRouteInspect\ModuleRouteInspectController;
use Unusualify\Modularous\Http\Controllers\ModuleRouteInspect\ModuleRouteInspectToolController;
use Unusualify\Modularous\Http\Controllers\Utility\ChatController;
use Unusualify\Modularous\Http\Controllers\Utility\ProcessController;
use Unusualify\Modularous\Http\Controllers\Utility\TagController;

/*
|--------------------------------------------------------------------------
| Web Routes
| Middlewares : [ 'web.auth', ...\Unusualify\Modularous\Facades\ModularousRoutes::defaultMiddlewares(), \Unusualify\Modularous\Facades\ModularousRoutes::defaultPanelMiddlewares()]
|
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/email/verify/{id}/{hash}', 'VerificationController@verify')
    ->middleware(['signed'])
    ->name('verification.verify');

Route::get('/email/verification-notification', 'VerificationController@send')
    ->middleware(['throttle:6,1,email-verification'])
    ->name('verification.send');

Route::prefix('register')->as('register.')
    ->middleware(['web', 'modularous.core'])
    ->withoutMiddleware(['modularous.panel', 'web.auth'])
    ->group(function () {

        Route::get('/password/generate/{token}', 'PasswordController@showForm')
            ->name('password.generate.form');
        Route::post('/password/generate', 'PasswordController@savePassword')
            ->name('password.generate');
    });

Route::singleton('profile', 'ProfileController', ['names' => ['edit' => 'profile']]);
Route::put('profile/company', 'ProfileController@updateCompany')->name('profile.company');
Route::put('profile/ui-preferences', 'Utility\UIPreferencesController@update')->name('profile.ui-preferences');

Route::resource('', 'DashboardController', ['as' => 'dashboard', 'names' => ['index' => 'dashboard']])->only(['index']);

Route::get('artisan-runner', ArtisanRunnerToolController::class)->name('artisan-runner');
Route::get('module-route-inspect', ModuleRouteInspectToolController::class)->name('module-route-inspect');

Route::get('users/impersonate/stop', 'Utility\ImpersonateController@stopImpersonate')->name('impersonate.stop');
Route::get('users/impersonate/{id}', 'Utility\ImpersonateController@impersonate')->name('impersonate');

// system internal api routes (for ajax web routes)
Route::prefix('api')->group(function () {
    Route::withoutMiddleware(['modularous.panel', 'web.auth', 'modularous.core'])->get('modal-service/{key}', function (Request $request, string $key) {
        $modalService = Session::get($key);

        if (! $modalService) {
            return response()->json(['error' => 'Modal service data not found'], 404);
        }

        // Remove from session after retrieval to prevent reuse
        Session::forget($key);

        return response()->json(['modalService' => $modalService]);
    })->name('modal_service.get');
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

    // ############# ArtisanRunner API routes ##############
    Route::group(['prefix' => 'artisan-runner', 'as' => 'artisan-runner.', 'controller' => ArtisanRunnerController::class], function () {
        Route::get('commands', 'commands')->name('commands');
        Route::get('commands/{name}', 'definition')
            ->where('name', '.*')
            ->name('commands.show');
        Route::post('runs', 'run')->name('runs');
        Route::post('runs/{runId}/answer', 'answer')->name('runs.answer');
    });
    // ############# End of ArtisanRunner API routes ##############

    // ############# Module Route Inspect API routes ##############
    Route::group(['prefix' => 'module-route-inspect', 'as' => 'module-route-inspect.', 'controller' => ModuleRouteInspectController::class], function () {
        Route::get('/', 'inspect')->name('inspect');
        Route::patch('status', 'setStatus')->name('status');
        Route::post('heal', 'heal')->name('heal');
    });
    // ############# End of Module Route Inspect API routes ##############

    Route::group(['prefix' => 'tag', 'as' => 'tag.', 'controller' => TagController::class], function () {
        Route::get('index', 'index')->name('index');
        Route::put('update', 'update')->name('update');
    });

    if (modularousConfig('enabled.media-library')) {
        Route::group(['prefix' => 'media-library', 'as' => 'media-library.'], function () {
            Route::post('sign-s3-upload', ['as' => 'sign-s3-upload', 'uses' => 'Utility\MediaLibraryController@signS3Upload']);
            Route::get('sign-azure-upload', ['as' => 'sign-azure-upload', 'uses' => 'Utility\MediaLibraryController@signAzureUpload']);
            Route::put('medias/single-update', ['as' => 'media.single-update', 'uses' => 'Utility\MediaLibraryController@singleUpdate']);
            Route::put('medias/bulk-update', ['as' => 'media.bulk-update', 'uses' => 'Utility\MediaLibraryController@bulkUpdate']);
            Route::put('medias/bulk-delete', ['as' => 'media.bulk-delete', 'uses' => 'Utility\MediaLibraryController@bulkDelete']);
            Route::get('medias/tags', ['as' => 'media.tags', 'uses' => 'Utility\MediaLibraryController@tags']);
            Route::resource('medias', 'Utility\MediaLibraryController', ['names' => 'media', 'only' => ['index', 'store', 'destroy']]);
        });
    }

    if (modularousConfig('enabled.file-library')) {
        Route::group(['prefix' => 'file-library', 'as' => 'file-library.'], function () {
            Route::post('sign-s3-upload', ['as' => 'sign-s3-upload', 'uses' => 'Utility\FileLibraryController@signS3Upload']);
            Route::get('sign-azure-upload', ['as' => 'sign-azure-upload', 'uses' => 'Utility\FileLibraryController@signAzureUpload']);
            Route::put('files/single-update', ['as' => 'file.single-update', 'uses' => 'Utility\FileLibraryController@singleUpdate']);
            Route::put('files/bulk-update', ['as' => 'file.bulk-update', 'uses' => 'Utility\FileLibraryController@bulkUpdate']);
            Route::put('files/bulk-delete', ['as' => 'file.bulk-delete', 'uses' => 'Utility\FileLibraryController@bulkDelete']);
            Route::get('files/tags', ['as' => 'file.tags', 'uses' => 'Utility\FileLibraryController@tags']);
            Route::resource('files', 'Utility\FileLibraryController', ['names' => 'file', 'only' => ['index', 'store', 'destroy']]);
        });
    }
});

Route::post('modularous/metrics', 'Utility\MetricController')->name('modularous.metrics');
