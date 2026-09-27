<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $mode === 'profile' ? 'Your profile' : 'Password reset' }} | Selva Electricals</title>@vite(['resources/css/app.css'])</head>
<body class="auth-page">
<header class="auth-header"><div class="container"><a href="{{ route('home') }}" class="store-brand"><img class="logo" src="{{ asset('images/figma/7ce94.png') }}" alt="" width="64" height="64"><span class="store-brand-name">Selva<span>Electricals</span></span></a><a href="{{ route('home') }}">← Storefront</a></div></header>
<main class="account-settings">
    <section class="auth-card"><div class="auth-card-content">
    <h1>{{ ['profile' => 'Your profile', 'forgot' => 'Forgot your password?', 'reset' => 'Choose a new password'][$mode] }}</h1>
    @if (session('status'))<p class="catalog-success" role="status">{{ session('status') }}</p>@endif
    @if ($errors->any())<div class="catalog-errors" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form class="auth-form" method="post" action="{{ route(['profile' => 'profile.update', 'forgot' => 'password.email', 'reset' => 'password.update'][$mode]) }}">
        @csrf
        @if ($mode === 'profile')
            @method('PUT')
            <p>{{ auth()->user()->email }}</p>
            <label for="name">Name</label><input id="name" name="name" autocomplete="name" maxlength="255" value="{{ old('name', auth()->user()->name) }}" required>
            <label for="phone">Phone</label><input id="phone" name="phone" type="tel" autocomplete="tel" maxlength="30" value="{{ old('phone', auth()->user()->phone) }}">
            <p class="form-hint">Leave the password fields blank to keep your current password.</p>
            <label for="current_password">Current password</label><input id="current_password" name="current_password" type="password" autocomplete="current-password">
        @else
            <label for="email">Email</label><input id="email" name="email" type="email" autocomplete="email" value="{{ old('email', $email ?? '') }}" required>
        @endif
        @if ($mode === 'reset')<input type="hidden" name="token" value="{{ $token }}">@endif
        @if ($mode !== 'forgot')
            <label for="password">New password</label><input id="password" name="password" type="password" autocomplete="new-password" minlength="12" maxlength="128" @required($mode === 'reset')>
            <label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="12" maxlength="128" @required($mode === 'reset')>
        @endif
        <button class="button button-navy" type="submit">{{ ['profile' => 'Save profile', 'forgot' => 'Send reset link', 'reset' => 'Reset password'][$mode] }}</button>
    </form>
    @if ($mode !== 'profile')<p><a href="{{ route('login') }}">Back to sign in</a></p>@endif
    </div></section>
</main>
</body>
</html>
