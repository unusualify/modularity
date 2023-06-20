<?php

use Illuminate\Http\Request;
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
//     unusualConfig('enabled.users-management')
// );
if (unusualConfig('enabled.users-management')) {
    // Route::get('', 'LoginController@showLoginForm')->name('login.form');
    // Route::post('login', 'LoginController@login')->name('login');
    // Route::post('logout', 'LoginController@logout')->name('logout');

    // Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.reset.link');
    // Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.reset.email');
    // Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset.form');
    // Route::get('password/welcome/{token}', 'ResetPasswordController@showWelcomeForm')->name('password.reset.welcome.form');
    // Route::post('password/reset', 'ResetPasswordController@reset')->name('password.reset');

    // Route::get('users/impersonate/stop', 'ImpersonateController@stopImpersonate')->name('impersonate.stop');
    // Route::get('users/impersonate/{id}', 'ImpersonateController@impersonate')->name('impersonate');
}
