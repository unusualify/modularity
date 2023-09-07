@extends('twill::auth.layout', [
    'route' => route('admin.login-2fa'),
    'screenTitle' => ___('authentication.verify-login'),
])

@section('form')
    <fieldset class="login__fieldset">
        <label class="login__label" for="verify-code">{{ ___('authentication.otp') }}</label>
        <input type="number" name="verify-code" class="login__input" required autofocus tabindex="1" />
    </fieldset>

    <input class="login__button" type="submit" value="{{ ___('authentication.login') }}" tabindex="3">
@stop
