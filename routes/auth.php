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

if (modularousConfig('enabled.users-management')) {
    $securityEnabled = (bool) modularousConfig('security.enabled', false);
    $authMfaEnabled = (bool) modularousConfig('security.mfa.enabled', false);
    $loginMiddlewares = $securityEnabled
        ? ['throttle:' . modularousConfig('security.throttle.login', '8,1')]
        : [];
    $login2faMiddlewares = $authMfaEnabled
        ? ['throttle:' . modularousConfig('security.mfa.throttle', modularousConfig('security.throttle.login_2fa', '6,1'))]
        : [];

    Route::get('register', 'RegisterController@showForm')->name('register.form');
    Route::post('register', 'RegisterController@register')->name('register');

    Route::get('login', 'LoginController@showForm')->name('login.form');
    Route::post('login', 'LoginController@login')->middleware($loginMiddlewares)->name('login');
    Route::post('logout', 'LoginController@logout')->name('logout');

    Route::get('login/2fa', 'LoginController@showLogin2FaForm')->name('login-2fa.form');
    Route::post('login/2fa', 'LoginController@login2Fa')->middleware($login2faMiddlewares)->name('login-2fa');
    Route::get('step-up', 'StepUpController@showForm')->name('step-up.form');
    Route::match(['get', 'post'], 'step-up/resend', 'StepUpController@resend')->middleware($login2faMiddlewares)->name('step-up.resend');
    Route::post('step-up/verify', 'StepUpController@verify')->middleware($login2faMiddlewares)->name('step-up.verify');

    Route::get('login/oauth', 'LoginController@showPasswordForm')->name('login.oauth.showPasswordForm');
    Route::post('login/oauth', 'LoginController@linkProvider')->name('login.oauth.linkProvider');

    Route::get('/auth/{provider}/redirect', 'LoginController@redirectToProvider')->name('login.provider');
    Route::get('/auth/{provider}/callback', 'LoginController@handleProviderCallback')->name('loginHandleCallbackProvider');

    // #TODO add complete registration after email confirmatiosent
    // Route::get('/completeRegistration', 'LoginController@completeRegisterForm')->name('completeRegistration.form');
    // Route::post('/completeRegistration', 'LoginController@completeRegister')->name('completeRegistration');
    // Route::get('/withoutLogin', 'LoginController@completeRegisterForm');

    Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.reset.link');
    Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.reset.email');
    Route::get('password/reset/success', 'ResetPasswordController@success')->name('password.reset.success');
    Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
    // Route::get('password/welcome/{token}', 'ResetPasswordController@showWelcomeForm')->name('password.reset.welcome.form');
    Route::post('password/reset', 'ResetPasswordController@reset')->name('password.reset.update');

    Route::get('register/success', 'RegisterController@success')->name('register.success');

    // Register with email verification
    Route::get('pre/register', 'PreRegisterController@showEmailForm')->name('register.email_form');
    Route::post('pre/register', 'PreRegisterController@sendVerificationLinkEmail')->name('register.verification');
    Route::get('pre/register/success', 'PreRegisterController@showSuccessForm')->name('register.verification.success');

    Route::get('complete/register/{token}', 'CompleteRegisterController@showCompleteRegisterForm')->name('complete.register.form');
    Route::post('complete/register', 'CompleteRegisterController@completeRegister')->name('complete.register');

}
