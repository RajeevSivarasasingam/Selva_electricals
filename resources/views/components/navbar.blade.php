@props(['store'])
<header id="home" class="site-header">
    <div class="utility-bar">
        <div class="container utility-inner">
            <span><x-store-icon file="03cc2.svg" :size="13" /> Quality Electrical & Hardware Products | Trusted Local Service in Sri Lanka</span>
            <a href="tel:{{ $store['phone_link'] }}"><x-store-icon file="7a1fe.svg" :size="13" /> {{ $store['phone'] }}</a>
            <span class="opening-hours"><x-store-icon file="43163.svg" :size="13" /> {{ $store['hours'] }}</span>
            <a class="utility-address" href="#contact"><x-store-icon file="02096.svg" :size="13" /> {{ $store['address'] }}</a>
        </div>
    </div>
    <div class="container header-main">
        <a class="store-brand" href="{{ route('home') }}" aria-label="Selva Electricals home"><img class="logo" src="{{ asset('images/figma/7ce94.png') }}" alt="" width="64" height="64"><span class="store-brand-name">Selva<span>Electricals</span></span></a>
        <form class="search-form" id="product-search" role="search">
            <label class="sr-only" for="search-category">Product category</label>
            <select id="search-category" name="category"><option value="all">All Categories</option>@foreach ($store['categories'] as $category)<option value="{{ $category['id'] }}">{{ $category['name'] }}</option>@endforeach</select>
            <label class="sr-only" for="search-query">Search products</label>
            <input id="search-query" name="q" type="search" placeholder="Search conduits, switches, water pumps, drills, PVC pipes…">
            <button type="submit" aria-label="Search products"><x-store-icon file="ebb8b.svg" :size="16" /></button>
        </form>
        <div class="header-actions">
            <a class="button button-green header-whatsapp" href="https://wa.me/{{ $store['whatsapp'] }}?text={{ rawurlencode('Hello Selva Electricals, I would like to make a product inquiry.') }}" target="_blank" rel="noopener"><x-store-icon file="49cd5.svg" :size="16" /> WhatsApp Inquiry</a>
            <button class="icon-button wishlist-toggle" data-open="wishlist" aria-label="Open wishlist"><x-store-icon file="9b68c.svg" :size="22" /><span class="count" data-wishlist-count>0</span></button>
            <button class="cart-summary" data-open="cart" aria-label="Open cart and quotation"><x-store-icon file="50a88.svg" :size="21" /><span class="count" data-cart-count>0</span><span><small>Estimate</small><strong data-cart-total>Rs. 0</strong></span></button>
            <x-store-profile />
        </div>
    </div>
    <div class="navigation-bar">
        <div class="container navigation-inner">
            <div class="category-dropdown">
                <button class="browse-button" id="browse-toggle" aria-expanded="false" aria-controls="category-menu"><x-store-icon file="6bc45.svg" :size="16" /><span>Browse All<br>Categories</span><x-store-icon file="d0e8d.svg" :size="10" /></button>
                <div id="category-menu" class="category-menu" hidden>@foreach ($store['categories'] as $category)<a href="#products" data-category="{{ $category['id'] }}">{{ $category['name'] }}</a>@endforeach</div>
            </div>
            <button class="mobile-menu-button" id="mobile-menu-toggle" aria-expanded="false" aria-controls="main-navigation">Menu</button>
            <nav id="main-navigation" aria-label="Main navigation">
                <a href="{{ route('home') }}#home" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a><a href="{{ route('home') }}#products" data-reset-products>Products</a><a href="{{ route('home') }}#categories">Categories</a>@if (count($store['products']))<button data-product="{{ $store['products'][0]['id'] }}">Product Details</button>@endif<a href="{{ route('home') }}#services">Services</a><a href="{{ route('about') }}" class="{{ request()->routeIs('about') ? 'active' : '' }}">About Us</a><a href="{{ route('home') }}#contact">Contact Us</a>
            </nav>
            <div class="nav-actions">@guest<a href="{{ route('login') }}">Login</a><a class="register-button" href="{{ route('register') }}">Register</a>@endguest<a class="hotline" href="tel:+{{ $store['whatsapp'] }}">Hotline: {{ $store['whatsapp_display'] }}</a><button class="direct-quote" data-open="quote"><x-store-icon file="4d4ab.svg" :size="13" /> Request Direct Quote</button></div>
        </div>
    </div>
</header>



