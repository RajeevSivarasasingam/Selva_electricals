@props(['product', 'whatsapp'])
<article class="product-card" data-product-card="{{ $product['id'] }}" data-category-id="{{ $product['category'] }}">
    <div class="product-image-wrap">
        <button class="product-image-button" data-product="{{ $product['id'] }}" aria-label="View {{ $product['name'] }}"><img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" loading="lazy" width="400" height="290"></button>
        <span class="product-badge {{ $product['badge'] === 'In Stock' ? 'in-stock' : '' }}">{{ $product['badge'] }}</span>
        <button class="product-heart" data-wishlist="{{ $product['id'] }}" aria-label="Save {{ $product['name'] }} to wishlist" aria-pressed="false"><x-store-icon file="e8bbb.svg" :size="20" /></button>
    </div>
    <div class="product-meta"><span>{{ $product['brand'] }}</span><span>{{ $product['certification'] }}</span></div>
    <h3><button data-product="{{ $product['id'] }}">{{ $product['name'] }}</button></h3>
    <p class="product-specification">{{ $product['specification'] }}</p>
    <p class="product-price"><strong>Rs. {{ number_format($product['price'], 2) }}</strong>@if ($product['original_price'] > $product['price'])<del>Rs. {{ number_format($product['original_price'], 2) }}</del>@endif</p>
    <div class="product-actions"><button class="button button-navy" data-add="{{ $product['id'] }}" @disabled(!$product['in_stock'])><x-store-icon file="25f90.svg" :size="17" /> Add</button><a class="button button-green" href="https://wa.me/{{ $whatsapp }}?text={{ rawurlencode('Hello, I would like to inquire about '.$product['name'].'. Please confirm price and availability.') }}" target="_blank" rel="noopener"><x-store-icon file="49cd5.svg" :size="16" /> WhatsApp</a></div>
</article>

