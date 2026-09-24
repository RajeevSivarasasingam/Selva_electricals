<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('guests cannot visit admin pages', function (string $page) {
    $this->get($page)->assertRedirect(route('login'));
})->with(['/admin', '/admin/products', '/admin/categories', '/admin/orders', '/admin/users']);

test('customers cannot visit admin pages', function (string $page) {
    $this->actingAs(User::factory()->create())->get($page)->assertForbidden();
})->with(['/admin', '/admin/products', '/admin/categories', '/admin/orders', '/admin/users']);

test('administrators can view all five database pages', function (string $page, string $heading) {
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)->get($page)->assertOk()->assertSee($heading)->assertSee('Welcome, Admin')->assertSee($admin->email);
})->with([
    ['/admin', 'Dashboard'],
    ['/admin/products', 'Products'],
    ['/admin/categories', 'Categories'],
    ['/admin/orders', 'Orders'],
    ['/admin/users', 'Users'],
]);

test('admin login redirects to the dashboard and logout ends access', function () {
    $admin = User::factory()->create(['is_admin' => true, 'password' => 'Test-password-123']);
    $this->post('/login', ['email' => $admin->email, 'password' => 'Test-password-123'])->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($admin);
    $this->post('/logout')->assertRedirect(route('login'));
    $this->assertGuest();
    $this->get('/admin')->assertRedirect(route('login'));
});

test('invalid passwords cannot authenticate', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $this->post('/login', ['email' => $admin->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('public registration cannot grant administrator access', function () {
    $this->post('/register', [
        'first_name' => 'New',
        'last_name' => 'Customer',
        'email' => 'customer@example.com',
        'password' => 'Customer-password-123',
        'is_admin' => true,
    ])->assertRedirect(route('home'));
    $user = User::where('email', 'customer@example.com')->firstOrFail();
    expect($user->is_admin)->toBeFalse();
    expect(Hash::check('Customer-password-123', $user->password))->toBeTrue();
    $this->get('/admin')->assertForbidden();
});

test('login attempts are throttled', function () {
    for ($attempt = 0; $attempt < 6; $attempt++) {
        $this->post('/login', ['email' => 'missing@example.com', 'password' => 'wrong']);
    }
    $this->post('/login', ['email' => 'missing@example.com', 'password' => 'wrong'])->assertStatus(429);
});

test('admin setup does not overwrite an existing account', function () {
    $user = User::factory()->create();
    $this->artisan('admin:create', ['email' => $user->email])->assertExitCode(1);
    expect($user->fresh()->is_admin)->toBeFalse();
});

test('admin pages show seeded products and support searching', function () {
    $this->seed();
    $admin = User::factory()->create(['is_admin' => true]);
    $product = Product::firstOrFail();
    $this->actingAs($admin)->get('/admin/products?'.http_build_query(['search' => $product->name]))->assertOk()->assertSee($product->name);
    $this->get('/admin/products?search=no-matching-product-9384')->assertOk()->assertSee('No matching results');
});

test('admin setup creates an administrator', function () {
    $this->artisan('admin:create', ['email' => 'admin@example.com'])->assertExitCode(0);
    $admin = User::where('email', 'admin@example.com')->firstOrFail();
    expect($admin->is_admin)->toBeTrue();
    expect($admin->password)->not->toBeEmpty();
});
