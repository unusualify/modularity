<?php

use Illuminate\Support\Facades\Route;
use Modules\SystemPayment\Http\Controllers\PriceController;

/*
|--------------------------------------------------------------------------
| Panel Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group, prefix, and route name alias.
|
| Now create something great!
|
*/
Route::middleware(['web.auth', ...\Unusualify\Modularity\Facades\ModularityRoutes::defaultMiddlewares()])->group(function () {

    Route::middleware((\Unusualify\Modularity\Facades\ModularityRoutes::defaultPanelMiddlewares()))->group(function () {});

    Route::middleware(modularityConfig('payment_middlewares', []))->group(function () {
        Route::controller(PriceController::class)->group(function () {
            Route::post('/pay', 'pay')->name('pay');
            Route::post('/checkout', 'checkout')->name('checkout');
        });
    });
});
Route::controller(PriceController::class)->group(function () {
    Route::group([
        'excluded_middleware' => ['web'],
    ], function () {
        Route::post('/response', 'response')->name('payment.response');
    });

});
