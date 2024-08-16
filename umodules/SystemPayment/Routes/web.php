<?php

use Illuminate\Support\Facades\Route;
use Modules\SystemPayment\Http\Controllers\PaymentController;
use Modules\SystemPayment\Http\Controllers\PriceController;
use Unusualify\Priceable\Models\Price;

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
Route::middleware(['web.auth', 'unusual.core'])->group(function(){

    Route::middleware(('unusual.panel'))->group(function(){

    });
    Route::controller(PriceController::class)->group(function(){

        Route::post('/pay', 'pay')->name('payment');
    });

});
