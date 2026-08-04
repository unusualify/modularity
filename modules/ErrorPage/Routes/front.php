<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Front General Routes
|--------------------------------------------------------------------------
|
| Here is where you can register routes for your application. No prefix,
| no route_name, no extra middleware but only 'web' middleware!
|
| Controller namespace Modules\${MODULENAME}\Http\Controllers\Front
|
*/
Route::prefix(curtModuleUrlPrefix(__FILE__))->name(curtModuleRouteNamePrefix(__FILE__) . '.')->group(function () {
    Route::middleware('auth')->group(function () {});
});
