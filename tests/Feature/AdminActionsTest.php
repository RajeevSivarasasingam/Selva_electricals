<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

test('product edits preserve identity and update the storefront', function () {
    $product = Product::firstOrFail();
    $slug = $product->slug;
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $this->get(route('admin.products', ['edit' => $product->id]))->assertOk()->assertSee('value="'.$product->name.'"', false);
    $this->put(route('admin.products.update', $product), [
        'name' => 'Edited item', 'category_id' => $product->category_id,
        'description' => 'Updated details', 'image' => 'https://example.com/new.jpg',
        'price' => 200, 'offer_price' => 150, 'in_stock' => 1,
    ])->assertRedirect(route('admin.products'));
    expect($product->fresh()->slug)->toBe($slug);
    expect((float) $product->fresh()->price)->toBe(150.0);
    $this->get('/')->assertSee('Edited item')->assertSee('https://example.com/new.jpg', false);
});

test('invalid product edits do not modify saved data', function () {
    $product = Product::firstOrFail();
    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->put(route('admin.products.update', $product), ['price' => -10])
        ->assertSessionHasErrors(['price', 'name']);
    expect($product->fresh()->price)->toBe($product->price);
});

test('category edits appear on the storefront and nonempty categories cannot be deleted', function () {
    $category = Product::firstOrFail()->category;
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $this->put(route('admin.categories.update', $category), [
        'name' => 'Updated category', 'description' => 'New category details',
        'image' => 'https://example.com/category.jpg',
    ])->assertRedirect(route('admin.categories'));
    $this->get('/')->assertSee('Updated category');
    $this->delete(route('admin.categories.destroy', $category))->assertSessionHas('status');
    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('products and empty categories can be deleted', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $product = Product::firstOrFail();
    $this->delete(route('admin.products.destroy', $product))->assertRedirect(route('admin.products'));
    $this->assertDatabaseMissing('products', ['id' => $product->id]);
    $this->post(route('admin.categories.store'), [
        'name' => 'Empty category', 'description' => 'Empty', 'image' => 'https://example.com/empty.jpg',
    ])->assertSessionHasNoErrors();
    $category = Category::where('name', 'Empty category')->firstOrFail();
    $this->delete(route('admin.categories.destroy', $category))->assertRedirect(route('admin.categories'));
    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('deleting an order deletes its quotation items', function () {
    $id = DB::table('quotations')->insertGetId(['name' => 'Customer', 'phone' => '0771234567', 'status' => 'new']);
    DB::table('quotation_items')->insert(['quotation_id' => $id, 'product_name' => 'Lamp', 'quantity' => 2]);
    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->delete(route('admin.orders.destroy', $id))->assertRedirect(route('admin.orders'));
    $this->assertDatabaseMissing('quotations', ['id' => $id]);
    $this->assertDatabaseMissing('quotation_items', ['quotation_id' => $id]);
});

test('customers can be deleted but administrators remain protected', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $customer = User::factory()->create();
    $this->actingAs($admin)->delete(route('admin.users.destroy', $customer))->assertRedirect(route('admin.users'));
    $this->assertDatabaseMissing('users', ['id' => $customer->id]);
    $this->delete(route('admin.users.destroy', $admin))->assertRedirect(route('admin.users'));
    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

test('nonadmins cannot edit or delete records', function () {
    $product = Product::firstOrFail();
    $category = Category::firstOrFail();
    $this->actingAs(User::factory()->create());
    $orderId = DB::table('quotations')->insertGetId(['name' => 'Customer', 'phone' => '0771234567', 'status' => 'new']);
    foreach (['products' => $product->id, 'categories' => $category->id, 'orders' => $orderId, 'users' => 1] as $type => $id) {
        $this->delete(route('admin.'.$type.'.destroy', $id))->assertForbidden();
    }
    $this->put(route('admin.products.update', $product), [])->assertForbidden();
    $this->put(route('admin.categories.update', $category), [])->assertForbidden();
});

test('search returns filtered results and action controls', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $product = Product::firstOrFail();
    $this->get(route('admin.products', ['search' => $product->name]))
        ->assertOk()->assertSee('id="admin-results"', false)->assertSee('Edit '.$product->name)->assertSee('Delete '.$product->name)->assertSee('aria-label="Sign out"', false);
    $this->get(route('admin.products', ['search' => 'nonexistent-test-record']))->assertSee('No matching results');
    $this->get(route('admin.products', ['edit' => 999999]))->assertNotFound();
});

test('orders and users have no edit endpoints', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));
    $this->put('/admin/orders/1', [])->assertStatus(405);
    $this->put('/admin/users/1', [])->assertStatus(405);
});
