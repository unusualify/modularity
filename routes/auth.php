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

    Route::get('register', 'RegisterController@showForm')->name('register.form');
    Route::post('register', 'RegisterController@register')->name('register');

    Route::get('login', 'LoginController@showForm')->name('login.form');
    Route::post('login', 'LoginController@login')->name('login');
    Route::post('logout', 'LoginController@logout')->name('logout');

    Route::get('login/oauth', 'LoginController@showPasswordForm')->name('login.oauth.showPasswordForm');
    Route::post('login/oauth', 'LoginController@linkProvider')->name('login.oauth.linkProvider');


    Route::get('/auth/{provider}/redirect', 'LoginController@redirectToProvider')->name('login.provider');
    Route::get('/auth/{provider}/callback', 'LoginController@handleProviderCallback')->name('loginHandleCallbackProvider');

    //Route::get('/withoutLogin', 'LoginController@completeRegisterForm');

    Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.reset.link');
    Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.reset.email');
    Route::get('password/reset/success', 'ResetPasswordController@success')->name('password.reset.success');
    Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
    // Route::get('password/welcome/{token}', 'ResetPasswordController@showWelcomeForm')->name('password.reset.welcome.form');
    Route::post('password/reset', 'ResetPasswordController@reset')->name('password.reset.update');

    Route::get('register/success', 'RegisterController@success')->name('register.success');

    //Register with email verification
    Route::get('pre/register', 'PreRegisterController@showEmailForm')->name('pre-register.email-form');
    Route::post('pre/register', 'PreRegisterController@sendVerificationLinkEmail')->name('pre-register');

    Route::get('complete/register/{token}', 'CompleteRegisterController@showCompleteRegisterForm')->name('complete.register.form');
    Route::post('complete/register', 'CompleteRegisterController@completeRegister')->name('complete.register');

}
