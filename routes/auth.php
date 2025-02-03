<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AUTH Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// dd(
//     modularityConfig('enabled.users-management')
// );
// Auth::routes();

if (modularityConfig('enabled.users-management')) {

    Route::get('register', 'RegisterController@showLoginForm')->name('register.form');
    Route::post('register', 'RegisterController@register')->name('register');

    Route::get('login', 'LoginController@showLoginForm')->name('login.form');
    Route::post('login', 'LoginController@login')->name('login');
    Route::post('logout', 'LoginController@logout')->name('logout');

    Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.reset.link');
    Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.reset.email');
    Route::get('password/reset/success', 'ResetPasswordController@success')->name('password.reset.success');
    Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
    // Route::get('password/welcome/{token}', 'ResetPasswordController@showWelcomeForm')->name('password.reset.welcome.form');
    Route::post('password/reset', 'ResetPasswordController@reset')->name('password.reset.update');

    Route::get('users/impersonate/stop', 'ImpersonateController@stopImpersonate')->name('impersonate.stop');
    Route::get('users/impersonate/{id}', 'ImpersonateController@impersonate')->name('impersonate');

    Route::get('register/success', 'RegisterController@success')->name('register.success');

}
