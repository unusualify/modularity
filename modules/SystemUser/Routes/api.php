<?php

use Illuminate\Support\Facades\Route;
use Unusualify\Modularous\Facades\ModularousRoutes;

/*
|--------------------------------------------------------------------------
| Panel API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['api.auth', ...ModularousRoutes::defaultMiddlewares()])->group(function () {});
