<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ucfirst($page) }} | Selva Admin</title>
    @vite(['resources/css/app.css'])
</head>
<body class="admin-body">
    <a class="skip-link" href="#admin-main">Skip to content</a>
    <aside class="admin-sidebar">
        <a class="admin-brand" href="{{ route('admin.dashboard') }}"><img src="{{ asset('images/figma/7ce94.png') }}" width="44" height="44" alt=""><span>SELVA ELECTRICALS<small>Administration</small></span></a>
        <p class="admin-menu-label">WORKSPACE</p>
        <nav aria-label="Admin navigation">
            @foreach (['dashboard' => 'Dashboard', 'products' => 'Products', 'categories' => 'Categories', 'orders' => 'Orders', 'users' => 'Users'] as $key => $label)
                <a href="{{ route('admin.'.$key) }}" @class(['admin-nav-link', 'is-active' => $page === $key]) @if ($page === $key) aria-current="page" @endif><span aria-hidden="true">{{ ['dashboard' => '◈', 'products' => '▣', 'categories' => '▦', 'orders' => '▤', 'users' => '◎'][$key] }}</span>{{ $label }}</a>
            @endforeach
        </nav>
        <a class="admin-store-link" href="{{ route('home') }}">← View storefront</a>
        <div class="admin-sidebar-note">Selva Electricals Hardware<br><small>Jaffna, Sri Lanka</small></div>
    </aside>
    <div class="admin-workspace">
        <header class="admin-topbar">
            <span>Store management</span>
            <div class="admin-welcome">Welcome, Admin <details class="admin-profile"><summary><span class="admin-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><span>{{ auth()->user()->name }}</span><span aria-hidden="true">⌄</span></summary><div class="admin-profile-card"><strong>{{ auth()->user()->name }}</strong><p>{{ auth()->user()->email }}</p><p>Administrator</p><form action="{{ route('logout') }}" method="post">@csrf<button type="submit">Sign out</button></form></div></details></div>
        </header>
        <main id="admin-main" class="admin-main">
            <div class="admin-page-heading"><div><p class="admin-eyebrow">YOUR STORE AT A GLANCE</p><h1>{{ ucfirst($page) }}</h1><p>{{ ['dashboard' => 'Welcome back. Here is what is happening at your store.', 'products' => 'Browse your database product catalog and availability.', 'categories' => 'Explore your catalog categories and product counts.', 'orders' => 'Customer quotation requests received by the store.', 'users' => 'Registered customers and administrator accounts.'][$page] }}</p></div>@if (in_array($page, ['products', 'categories']))<button type="button" class="button button-amber" data-open-catalog>+ Add {{ $page === 'products' ? 'product' : 'category' }}</button>@endif<span class="admin-date">{{ now()->format('d M Y') }}</span></div>
            @if ($page === 'dashboard')
                <div class="admin-stats">
                    @foreach ($counts as $label => $count)<a href="{{ route('admin.'.strtolower($label)) }}" class="admin-stat"><span>{{ $label === 'Orders' ? 'Quotation requests' : $label }}</span><strong>{{ number_format($count) }}</strong><small>View {{ strtolower($label) }} →</small></a>@endforeach
                </div>
            @endif
            @if (session('status'))<p class="catalog-success" role="status">{{ session('status') }} <a href="{{ route('home') }}">View storefront →</a></p>@endif<section class="admin-panel">
                <div class="admin-panel-heading"><h2>{{ $page === 'dashboard' ? 'Recent quotation requests' : ucfirst($page) }}</h2>
                @if ($page !== 'dashboard')<form method="get" class="admin-search"><label class="sr-only" for="admin-search">Search {{ $page }}</label><input id="admin-search" name="search" value="{{ $search }}" placeholder="Search {{ $page }}…" maxlength="100"><button type="submit">Search</button>@if ($search !== '')<a href="{{ route('admin.'.$page) }}">Clear</a>@endif</form>@else<a href="{{ route('admin.orders') }}">View all →</a>@endif</div>
                @if ($page === 'orders')<p class="admin-notice">These records are quotation requests, not paid checkout orders.</p>@endif
                <div class="admin-table-wrap"><table class="admin-table">
                    <thead><tr>
                        @foreach (match ($page) { 'products' => ['Product', 'Category', 'Price', 'Availability'], 'categories' => ['Category', 'Description', 'Products'], 'users' => ['Name', 'Email', 'Role', 'Joined'], default => ['Reference', 'Customer', 'Contact', 'Status', 'Received'] } as $heading)<th scope="col">{{ $heading }}</th>@endforeach
                    </tr></thead>
                    <tbody>
                    @forelse ($records as $record)
                        <tr>
                        @if ($page === 'products')
                            <td><strong>{{ $record->name }}</strong><small>{{ $record->slug }}</small></td><td>{{ $record->category?->name ?? 'Uncategorized' }}</td><td class="admin-nowrap">Rs. {{ number_format((float) $record->price, 2) }}</td><td><span class="admin-badge">{{ $record->in_stock ? 'In stock' : 'Out of stock' }}</span></td>
                        @elseif ($page === 'categories')
                            <td><strong>{{ $record->name }}</strong><small>{{ $record->slug }}</small></td><td>{{ $record->description }}</td><td>{{ $record->products_count }}</td>
                        @elseif ($page === 'users')
                            <td><strong>{{ $record->name }}</strong></td><td>{{ $record->email }}</td><td><span class="admin-badge">{{ $record->is_admin ? 'Admin' : 'Customer' }}</span></td><td>{{ $record->created_at?->format('d M Y') }}</td>
                        @else
                            <td><strong>#Q{{ str_pad($record->id, 4, '0', STR_PAD_LEFT) }}</strong></td><td>{{ $record->name }}@if ($page === 'orders')<details class="admin-order-details"><summary>View request</summary><p>{{ $record->message ?: 'No additional message.' }}</p><ul>@forelse ($record->items as $item)<li>{{ $item->product_name }} × {{ $item->quantity }}</li>@empty<li>No product items supplied.</li>@endforelse</ul></details>@endif</td><td>{{ $record->phone }}<small>{{ $record->email }}</small></td><td><span class="admin-badge">{{ ucfirst($record->status) }}</span></td><td class="admin-nowrap">{{ $record->created_at?->format('d M Y') }}</td>
                        @endif
                        </tr>
                    @empty
                        <tr><td colspan="5" class="admin-empty"><strong>{{ $search !== '' ? 'No matching results' : 'No records yet' }}</strong><p>{{ $search !== '' ? 'Try another search.' : 'Records will appear here when they are added to the store.' }}</p></td></tr>
                    @endforelse
                    </tbody>
                </table></div>
                @if ($page !== 'dashboard' && $records->hasPages())<nav class="admin-pagination" aria-label="Pagination">@if ($records->previousPageUrl())<a href="{{ $records->previousPageUrl() }}">← Previous</a>@endif<span>Page {{ $records->currentPage() }} of {{ $records->lastPage() }}</span>@if ($records->nextPageUrl())<a href="{{ $records->nextPageUrl() }}">Next →</a>@endif</nav>@endif
            </section>
        </main>
    </div>
@include('admin-catalog-form')
</body>
</html>

