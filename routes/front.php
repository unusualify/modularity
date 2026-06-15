<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Unusualify\Modularous\Http\Controllers\API\LanguageController;
use Unusualify\Modularous\Http\Controllers\Utility\CurrencyExchangeController;
use Unusualify\Modularous\Http\Controllers\Utility\FilepondController;

/*
|--------------------------------------------------------------------------
| Front Routes with 'web' middleware
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'api'], function () {
    Route::group(['prefix' => 'filepond', 'as' => 'filepond.'], function () {
        Route::post('process', [FilepondController::class, 'upload'])->name('process');
        Route::delete('revert', [FilepondController::class, 'revert'])->name('revert');
        Route::get('preview/{uuid}', [FilepondController::class, 'preview'])->name('preview');
    });

    // Route::group(['prefix' => 'chatable', 'as' => 'chatable.'], function () {
    //     Route::get('{chat}', [ChatableController::class, 'index'])->name('index');
    //     Route::get('{chat}/attachments', [ChatableController::class, 'attachments'])->name('attachments');
    //     Route::post('{chat}', [ChatableController::class, 'store'])->name('store');
    //     Route::put('{chat_message}', [ChatableController::class, 'update'])->name('update');
    //     Route::get('show/{chat_message}', [ChatableController::class, 'show'])->name('show');
    //     Route::delete('destroy/{chat_message}', [ChatableController::class, 'destroy'])->name('destroy');
    // });

    Route::controller(CurrencyExchangeController::class)
        ->prefix('currency')
        ->name('currency.')
        ->group(function () {
            Route::post('fetch-rates', 'fetchRates')->name('fetchRates');
            Route::post('convert', 'convert')->name('convert');
            Route::get('rate/{currency}', 'getRate')->name('getRate');
        });

    Route::group(['as' => 'api.'], function () {
        Route::apiResource('languages', LanguageController::class, ['only' => 'index']);
    });
});
if (!modularousConfig('cms_features.enabled')) {
    Route::get('/', function (Request $request) {
        return redirect()->route(Route::hasAdmin('login.form'));
    })->name('modularous.home');
}

