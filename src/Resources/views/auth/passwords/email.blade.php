@extends('twill::auth.layout', [
    'route' => route('admin.password.reset.email'),
    'screenTitle' => ___('auth.reset-password')
])

@section('form')
    <fieldset class="login__fieldset">
        <label class="login__label" for="email">{{ ___('auth.email') }}</label>
        <input type="email" name="email" id="email" class="login__input" required autofocus />
    </fieldset>

    <input class="login__button" type="submit" value="{{ ___('auth.reset-send') }}">
@stop
