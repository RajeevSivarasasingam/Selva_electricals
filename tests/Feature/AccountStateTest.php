<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('account cart and wishlist persist and appear only for their owner', function () {
    $this->seed();
    $product = Product::firstOrFail();
    $user = User::factory()->create();
    $other = User::factory()->create();
    $this->actingAs($user)->putJson(route('account.state'), [
        'user_id' => $user->id, 'cart' => [$product->slug => 3], 'wishlist' => [$product->slug],
    ])->assertOk();
    expect($user->fresh()->cart)->toBe([$product->slug => 3]);
    expect($user->fresh()->wishlist)->toBe([$product->slug]);
    $this->get('/')->assertOk()->assertSee($user->email)->assertSee('data-store-logout', false);
    expect($other->fresh()->cart)->toBeNull();
    $this->actingAs($other)->putJson(route('account.state'), [
        'user_id' => $user->id, 'cart' => [], 'wishlist' => [],
    ])->assertStatus(409);
    expect($user->fresh()->cart)->toBe([$product->slug => 3]);
    $this->actingAs($user)->putJson(route('account.state'), [
        'user_id' => $user->id, 'cart' => [], 'wishlist' => [],
    ])->assertOk();
    expect($user->fresh()->cart)->toBe([]);
});

test('guests and invalid quantities cannot write account selections', function () {
    $this->putJson(route('account.state'), ['cart' => [], 'wishlist' => []])->assertUnauthorized();
    $user = User::factory()->create();
    $this->actingAs($user)->putJson(route('account.state'), [
        'user_id' => $user->id, 'cart' => ['item' => 1000], 'wishlist' => [],
    ])->assertUnprocessable();
});

test('registration saves phone and admins see it', function () {
    $this->post('/register', [
        'first_name' => 'Test', 'last_name' => 'Customer', 'email' => 'phone@example.com',
        'phone' => '077 123 4567', 'password' => 'Example-password-123',
    ])->assertRedirect('/');
    $this->assertDatabaseHas('users', ['email' => 'phone@example.com', 'phone' => '077 123 4567']);
    $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/admin/users')
        ->assertOk()->assertSee('077 123 4567');
});

test('view storefront retains admin session and shows management link', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)->get('/admin')->assertOk();
    $this->get('/')->assertOk()->assertSee('Manage store')->assertSee('Sign out')->assertSee($admin->email);
    $this->assertAuthenticatedAs($admin);
    $this->get('/admin')->assertOk();
});

test('storefront displays the updated contact details', function () {
    $this->get('/')->assertOk()->assertSee('Selva Electricals')->assertSee('selvahel@gmail.com')
        ->assertSee('021 221 25955')->assertSee('077 764 2771')->assertSee('No 21, Palaly Road, Thirunelveli, Jaffna.');
});
