@extends('twill::auth.layout', [
    'route' => route('admin.login.oauth.linkProvider'),
    'screenTitle' => ___('authentication.oauth-link-title', ['provider' => ucfirst($provider)]),
]),

@section('form')
    <fieldset class="login__fieldset">
        <label class="login__label" for="email">{{ ___('authentication.email') }}</label>
        <input type="email" name="email" id="email" class="login__input" required autofocus tabindex="1" value="{{ $username }}" readonly="readonly" />
    </fieldset>

    <fieldset class="login__fieldset">
        <label class="login__label" for="password">{{ ___('authentication.password') }}</label>
        <a href="{{ route('admin.password.reset.link') }}" class="login__help f--small" tabindex="5"><span>{{ ___('authentication.login') }}</span></a>
        <input type="password" name="password" id="password" class="login__input" required tabindex="2" />
    </fieldset>

    <input class="login__button" type="submit" value="{{ ___('authentication.login') }}" tabindex="3">

    <a href="{!! route('admin.login') !!}" class="">{{ ___('authentication.back-to-login') }}</a>

@stop
