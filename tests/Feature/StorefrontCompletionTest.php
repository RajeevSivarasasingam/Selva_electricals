<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('quotation submissions are stored and visible to admins', function () {
    $this->seed();
    $product = Product::firstOrFail();
    $response = $this->postJson(route('quotations.store'), [
        'name' => 'Quotation customer', 'phone' => '0777642771', 'message' => 'Please supply these items',
        'items' => [['product_slug' => $product->slug, 'product_name' => 'Untrusted name', 'quantity' => 3]],
    ])->assertCreated();
    $this->assertDatabaseHas('quotation_items', ['quotation_id' => $response->json('id'), 'product_name' => $product->name, 'quantity' => 3]);
    $this->actingAs(User::factory()->create(['is_admin' => true]))->get('/admin/orders')->assertOk()->assertSee('Quotation customer');
});

test('invalid quotation does not create a record', function () {
    $this->postJson(route('quotations.store'), ['name' => 'Invalid'])->assertUnprocessable();
    $this->assertDatabaseCount('quotations', 0);
});

test('about includes shared storefront configuration and dialogs', function () {
    $this->get('/about')->assertOk()->assertSee('id="storefront-data"', false)->assertSee('id="quotation-form"', false)->assertSee('id="store-toast"', false);
});

test('profile edits save name and phone without granting privileges', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->put(route('profile.update'), [
        'name' => 'Updated user', 'phone' => '077 764 2771', 'is_admin' => true,
    ])->assertSessionHasNoErrors();
    expect($user->fresh()->name)->toBe('Updated user');
    expect($user->fresh()->phone)->toBe('077 764 2771');
    expect($user->fresh()->is_admin)->toBeFalse();
});

test('profile password changes require current password', function () {
    $user = User::factory()->create(['password' => 'Current-password-123']);
    $this->actingAs($user)->put(route('profile.update'), [
        'name' => $user->name, 'current_password' => 'wrong',
        'password' => 'New-password-123', 'password_confirmation' => 'New-password-123',
    ])->assertSessionHasErrors('current_password');
    $this->put(route('profile.update'), [
        'name' => $user->name, 'current_password' => 'Current-password-123',
        'password' => 'New-password-123', 'password_confirmation' => 'New-password-123',
    ])->assertSessionHasNoErrors();
    expect(Hash::check('New-password-123', $user->fresh()->password))->toBeTrue();
});

test('reset notification is dispatched and tokens cannot be reused', function () {
    Notification::fake();
    $user = User::factory()->create();
    $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');
    Notification::assertSentTo($user, ResetPassword::class);
    $token = Password::createToken($user);
    $payload = ['token' => $token, 'email' => $user->email, 'password' => 'Reset-password-123', 'password_confirmation' => 'Reset-password-123'];
    $this->post(route('password.update'), $payload)->assertRedirect(route('login'));
    expect(Hash::check('Reset-password-123', $user->fresh()->password))->toBeTrue();
    $this->post(route('password.update'), $payload)->assertSessionHasErrors('email');
});

test('forgot-password responses do not reveal whether an account exists', function () {
    Notification::fake();
    $user = User::factory()->create();
    $known = $this->post(route('password.email'), ['email' => $user->email])->assertSessionHas('status');
    $message = session('status');
    $this->post(route('password.email'), ['email' => 'unknown@example.com'])->assertSessionHas('status', $message);
});

test('profile requires login and reset pages render', function () {
    $this->get('/profile')->assertRedirect(route('login'));
    $this->get('/forgot-password')->assertOk();
    $this->get('/reset-password/sample?email=person@example.com')->assertOk();
});

test('login and registration render after password reset integration', function () {
    $this->get('/login')->assertOk()->assertSee('Forgot password?');
    $this->get('/register')->assertOk()->assertSee('register-first-name');
});
