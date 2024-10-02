<?php

use Illuminate\Support\Facades\Route;

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
Route::middleware(['web.auth', 'unusual.core'])->group(function () {

    Route::middleware(('unusual.panel'))->group(function () {});

});
