<?php

use Illuminate\Support\Facades\Route;
use Unusualify\Modularity\Http\Controllers\API\LanguageController;
use Unusualify\Modularity\Http\Controllers\FilepondController;

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

Route::group(['prefix' => 'api'], function(){
    Route::group(['prefix' => 'filepond', 'as' => 'filepond.'], function(){
        Route::post('process', [FilepondController::class, 'upload'])->name('process');
        Route::delete('revert', [FilepondController::class, 'revert'])->name('revert');
        Route::get('preview/{folder}', [FilepondController::class, 'preview'])->name('preview');
    });

    Route::group(['as' => 'api.'], function(){
        Route::apiResource('languages', LanguageController::class, ['only' => 'index']);
    });
});
Route::get('/', function(\Illuminate\Http\Request $request){
    return redirect()->route(Route::hasAdmin('login.form'));
})->name('modularity.home');

