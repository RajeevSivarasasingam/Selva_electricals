<?php

use Tests\TestCase;

uses(TestCase::class);

test('the storefront renders its complete catalog and Jaffna contact details', function () {
    $this->withoutVite();

    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee('Quality Electrical & Hardware', false)
        ->assertSee('Shop by Category')
        ->assertSee('Featured Products')
        ->assertSee('More Than Just a Hardware Shop')
        ->assertSee('What Our Clients Say')
        ->assertSee(config('storefront.address'))
        ->assertDontSee('Our Colombo Counter');

    foreach (config('storefront.products') as $product) {
        $response->assertSee($product['name']);
    }

    foreach (config('storefront.categories') as $category) {
        $response->assertSee($category['name']);
    }
});

test('catalog data uses permanent local image assets and valid categories', function () {
    $categories = array_column(config('storefront.categories'), 'id');

    foreach (config('storefront.products') as $product) {
        expect($product['category'])->toBeIn($categories)
            ->and($product['price'])->toBeGreaterThan(0)
            ->and(file_exists(public_path('images/figma/'.$product['image'])))->toBeTrue();
    }

    foreach (config('storefront.categories') as $category) {
        expect(file_exists(public_path('images/figma/'.$category['image'])))->toBeTrue();
    }
});

test('storefront interactions have accessible controls and an empty initial estimate', function () {
    $this->withoutVite();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('id="product-search"', false)
        ->assertSee('aria-label="Filter products"', false)
        ->assertSee('data-cart-total>Rs. 0', false)
        ->assertSee('id="quotation-form"', false)
        ->assertSee('id="storefront-data"', false)
        ->assertSee('https://wa.me/'.config('storefront.whatsapp'), false);
});
