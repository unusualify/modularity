@extends("{$MODULARITY_VIEW_NAMESPACE}::auth.layout", [
    'route' => route('login.form'),
    'screenTitle' => ___('authentication.login-title')
])

@section('form')
    <fieldset class="login__fieldset">
        <label class="login__label" for="email">{{ ___('authentication.email') }}</label>
        <input type="email" name="email" id="email" class="login__input" required autofocus tabindex="1" value="{{ old('email') }}" />
    </fieldset>

    <fieldset class="login__fieldset">
        <label class="login__label" for="password">{{ ___('authentication.password') }}</label>
        {{-- <a href="{{ route('admin.password.reset.link') }}" class="login__help f--small" tabindex="5"><span>{{ ___('authentication.forgot-password') }}</span></a> --}}
        <input type="password" name="password" id="password" class="login__input" required tabindex="2" />
    </fieldset>

    <input class="login__button" type="submit" value="{{ ___('authentication.login') }}" tabindex="3">

    @if (unusualConfig('enabled.users-oauth', false))
        @foreach(unusualConfig('oauth.providers', []) as $index => $provider)
            <a href="{!! route('admin.login.redirect', $provider) !!}" class="login__socialite login__{{$provider}}" tabindex="{{ 4 + $index }}">
                @includeIf("{$MODULARITY_VIEW_NAMESPACE}::auth.icons." . $provider)
                <span>Sign in with {{ ucfirst($provider)}}</span>
            </a>
        @endforeach
    @endif

@stop
