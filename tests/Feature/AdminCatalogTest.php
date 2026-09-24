<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('an administrator creates a category visible on the storefront', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->post(route('admin.categories.store'), [
            'name' => 'Garden Equipment',
            'description' => 'Outdoor essentials',
            'image' => 'https://example.com/garden.jpg',
        ])->assertRedirect(route('admin.categories'));
    $this->assertDatabaseHas('categories', ['name' => 'Garden Equipment']);
    $this->get('/')->assertOk()->assertSee('Garden Equipment')->assertSee('https://example.com/garden.jpg', false);
});

test('an administrator creates a product with an offer and external image', function () {
    $this->seed();
    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->post(route('admin.products.store'), [
            'name' => 'New Test Lamp',
            'category_id' => Category::firstOrFail()->id,
            'description' => 'New lamp details',
            'image' => 'https://example.com/lamp.jpg',
            'price' => '2500.50',
            'offer_price' => '2000.25',
            'brand' => 'Lamp Brand',
            'in_stock' => '1',
        ])->assertRedirect(route('admin.products'));
    $this->assertDatabaseHas('products', ['name' => 'New Test Lamp', 'price' => 2000.25, 'original_price' => 2500.50]);
    $this->get('/')->assertOk()->assertSee('New Test Lamp')->assertSee('https://example.com/lamp.jpg', false)->assertSee('2,000.25')->assertSee('2,500.50')->assertSee('Lamp Brand');
});

test('invalid offers and unsafe image urls are rejected', function () {
    $this->seed();
    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->post(route('admin.products.store'), [
            'name' => 'Invalid Lamp',
            'category_id' => Category::firstOrFail()->id,
            'description' => 'Details',
            'image' => 'javascript:alert(1)',
            'price' => 100,
            'offer_price' => 200,
            'in_stock' => '1',
        ])->assertSessionHasErrors(['offer_price', 'image']);
    $this->assertDatabaseMissing('products', ['name' => 'Invalid Lamp']);
});

test('customers cannot create catalog entries', function (string $route) {
    $this->actingAs(User::factory()->create())->post(route($route), [])->assertForbidden();
})->with(['admin.categories.store', 'admin.products.store']);

test('guests cannot create catalog entries', function (string $route) {
    $this->post(route($route), [])->assertRedirect(route('login'));
})->with(['admin.categories.store', 'admin.products.store']);

test('empty database renders a usable storefront', function () {
    $this->get('/')->assertOk()->assertSee('Shop by Category');
});

test('product without an offer uses regular price and duplicate names remain distinct', function () {
    $this->seed();
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $payload = [
        'name' => 'Repeated Lamp',
        'category_id' => Category::firstOrFail()->id,
        'description' => 'Details',
        'image' => 'https://example.com/lamp.jpg',
        'price' => '100.75',
        'in_stock' => '0',
    ];
    $this->post(route('admin.products.store'), $payload)->assertSessionHasNoErrors();
    $this->post(route('admin.products.store'), $payload)->assertSessionHasNoErrors();
    $products = Product::where('name', 'Repeated Lamp')->get();
    expect($products)->toHaveCount(2);
    expect($products->pluck('slug')->unique())->toHaveCount(2);
    expect((float) $products->first()->price)->toBe(100.75);
    $this->get('/')->assertOk()->assertSee('Out of stock');
});

test('invalid category and missing name are rejected', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->post(route('admin.products.store'), ['category_id' => 9999])
        ->assertSessionHasErrors(['name', 'category_id', 'price', 'image']);
});

test('creation dialogs render on both admin pages', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $this->get(route('admin.products'))->assertOk()->assertSee('id="catalog-dialog"', false)->assertSee('Offer price');
    $this->get(route('admin.categories'))->assertOk()->assertSee('id="catalog-dialog"', false)->assertSee('Image URL');
});
