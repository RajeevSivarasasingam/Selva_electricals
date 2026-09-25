@auth
<div class="store-account-controls">
    <details class="store-profile">
        <summary aria-label="Your profile"><span class="admin-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><span class="store-profile-name">{{ auth()->user()->name }}</span><span aria-hidden="true">⌄</span></summary>
        <div class="store-profile-menu"><strong>{{ auth()->user()->name }}</strong><p>{{ auth()->user()->email }}</p>@if (auth()->user()->phone)<p>{{ auth()->user()->phone }}</p>@endif
        @if (auth()->user()->is_admin)<a href="{{ route('admin.dashboard') }}">Manage store →</a>@endif
        </div>
    </details>
    <form method="post" action="{{ route('logout') }}" data-store-logout>@csrf<button class="admin-action-icon" type="submit" aria-label="Sign out" title="Sign out"><x-admin-icon name="logout" /></button></form>
</div>
@else
<a class="account-button" href="{{ route('login') }}" aria-label="Sign in"><x-store-icon file="59e02.svg" :size="16" /></a>
@endauth
