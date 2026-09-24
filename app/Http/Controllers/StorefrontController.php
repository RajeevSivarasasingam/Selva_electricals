<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class StorefrontController extends Controller
{
    public function __invoke(): View
    {
        return view('home', ['store' => $this->store()]);
    }

    public function about(): View
    {
        return view('about', ['store' => $this->store()]);
    }

    public function auth(): View
    {
        return view('auth', ['store' => $this->store()]);
    }

    /** @return array<string, mixed> */
    private function store(): array
    {
        $store = config('storefront');
        $store['categories'] = Category::withCount('products')->orderBy('id')->get()->map(fn (Category $category): array => [
            'id' => $category->slug,
            'name' => $category->name,
            'description' => $category->description ?? '',
            'stock' => $category->products_count,
            'image' => $this->imageUrl($category->image),
        ])->all();
        $store['products'] = Product::with('category')->orderByDesc('id')->get()->map(fn (Product $product): array => [
            'id' => $product->slug,
            'category' => $product->category?->slug ?? '',
            'name' => $product->name,
            'description' => $product->description ?? '',
            'brand' => $product->brand ?? '',
            'certification' => $product->certification ?? '',
            'specification' => $product->specification ?: ($product->description ?? ''),
            'price' => (float) $product->price,
            'original_price' => (float) ($product->original_price ?? $product->price),
            'badge' => $product->in_stock ? ($product->badge ?: 'In Stock') : 'Out of stock',
            'in_stock' => $product->in_stock,
            'image' => $this->imageUrl($product->image),
        ])->all();

        return $store;
    }

    private function imageUrl(?string $image): string
    {
        return preg_match('~^https?://~i', $image ?? '') ? $image : asset('images/figma/'.($image ?: '7ce94.png'));
    }
}
