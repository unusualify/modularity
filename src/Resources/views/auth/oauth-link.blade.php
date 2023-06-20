@extends('twill::auth.layout', [
    'route' => route('admin.login.oauth.linkProvider'),
    'screenTitle' => ___('auth.oauth-link-title', ['provider' => ucfirst($provider)]),
]),

@section('form')
    <fieldset class="login__fieldset">
        <label class="login__label" for="email">{{ ___('auth.email') }}</label>
        <input type="email" name="email" id="email" class="login__input" required autofocus tabindex="1" value="{{ $username }}" readonly="readonly" />
    </fieldset>

    <fieldset class="login__fieldset">
        <label class="login__label" for="password">{{ ___('auth.password') }}</label>
        <a href="{{ route('admin.password.reset.link') }}" class="login__help f--small" tabindex="5"><span>{{ ___('auth.login') }}</span></a>
        <input type="password" name="password" id="password" class="login__input" required tabindex="2" />
    </fieldset>

    <input class="login__button" type="submit" value="{{ ___('auth.login') }}" tabindex="3">

    <a href="{!! route('admin.login') !!}" class="">{{ ___('auth.back-to-login') }}</a>

@stop
