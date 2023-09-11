@php
$passwordText = isset($welcome) && $welcome ? ___('authentication.choose-password') : ___('authentication.reset-password');
@endphp

@extends('twill::auth.layout', [
    'route' => route('admin.password.reset'),
    'screenTitle' => $passwordText
])

@section('form')
    <fieldset class="login__fieldset">
        <label class="login__label" for="email">{{ ___('authentication.email') }}</label>
        <input type="email" name="email" id="email" class="login__input" required autofocus value="{{ $email ?? '' }}" />
    </fieldset>

    <fieldset class="login__fieldset">
        <label class="login__label" for="password">{{ ___('authentication.password') }}</label>
        <input type="password" name="password" id="password" class="login__input" required />
    </fieldset>

    <fieldset class="login__fieldset">
        <label class="login__label" for="password_confirmation">{{ ___('authentication.password-confirmation') }}</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="login__input" required />
    </fieldset>

    <input type="hidden" name="token" value="{{ $token }}">

    <input class="login__button" type="submit" value="{{ $passwordText }}">
@stop
