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

Route::middleware('auth')
    ->prefix( getCurrentModuleUrlName() )
    ->name( getCurrentModuleSnakeName().'.')
    ->group(function(){


});

Route::middleware('auth')
    ->prefix('api')
    ->name('api.')
    ->namespace('API')
    ->group(function(){

    Route::prefix( getCurrentModuleUrlName() )
        ->name( getCurrentModuleSnakeName().'.' )
        ->group(function(){


    });
});

Route::webRoutes();
