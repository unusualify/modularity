@extends("{$BASE_KEY}::auth.layout", [
    'route' => route('login'),
    'screenTitle' => ___('auth.login-title')
])

@section('form')
    <fieldset class="login__fieldset">
        <label class="login__label" for="email">{{ ___('auth.email') }}</label>
        <input type="email" name="email" id="email" class="login__input" required autofocus tabindex="1" value="{{ old('email') }}" />
    </fieldset>

    <fieldset class="login__fieldset">
        <label class="login__label" for="password">{{ ___('auth.password') }}</label>
        {{-- <a href="{{ route('admin.password.reset.link') }}" class="login__help f--small" tabindex="5"><span>{{ ___('auth.forgot-password') }}</span></a> --}}
        <input type="password" name="password" id="password" class="login__input" required tabindex="2" />
    </fieldset>

    <input class="login__button" type="submit" value="{{ ___('auth.login') }}" tabindex="3">

    @if (unusualConfig('enabled.users-oauth', false))
        @foreach(unusualConfig('oauth.providers', []) as $index => $provider)
            <a href="{!! route('admin.login.redirect', $provider) !!}" class="login__socialite login__{{$provider}}" tabindex="{{ 4 + $index }}">
                @includeIf("{$BASE_KEY}::auth.icons." . $provider)
                <span>Sign in with {{ ucfirst($provider)}}</span>
            </a>
        @endforeach
    @endif

@stop
