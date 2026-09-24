<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        $page = $request->route()->defaults['page'];
        $search = $request->validate(['search' => ['nullable', 'string', 'max:100']])['search'] ?? '';
        $records = match ($page) {
            'products' => Product::with('category')->where('name', 'like', '%'.$search.'%')->latest()->paginate(15)->withQueryString(),
            'categories' => Category::withCount('products')->where('name', 'like', '%'.$search.'%')->latest()->paginate(15)->withQueryString(),
            'orders' => Quotation::with('items')->where('name', 'like', '%'.$search.'%')->latest()->paginate(15)->withQueryString(),
            'users' => User::where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%');
            })->latest()->paginate(15)->withQueryString(),
            default => Quotation::latest()->limit(5)->get(),
        };

        $editing = null;
        if ($request->filled('edit') && in_array($page, ['products', 'categories'])) {
            $editing = ($page === 'products' ? Product::query() : Category::query())->findOrFail($request->integer('edit'));
        }

        return view('admin', [
            'editing' => $editing,
            'page' => $page,
            'categories' => Category::orderBy('name')->get(),
            'search' => $search,
            'records' => $records,
            'counts' => [
                'Products' => Product::count(),
                'Categories' => Category::count(),
                'Orders' => Quotation::count(),
                'Users' => User::count(),
            ],
        ]);
    }

    private function saveCategory(Request $request, ?Category $category = null): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'image' => ['required', 'url:http,https', 'max:2048'],
        ]);
        $data['slug'] = (Str::slug($data['name']) ?: 'category').'-'.Str::lower(Str::random(8));
        if ($category) {
            unset($data['slug']);
            $category->update($data);
        } else {
            Category::create($data);
        }

        return redirect()->route('admin.categories')->with('status', 'Category saved. The storefront is up to date.');
    }

    private function saveProduct(Request $request, ?Product $product = null): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['required', 'string', 'max:5000'],
            'image' => ['required', 'url:http,https', 'max:2048'],
            'price' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'offer_price' => ['nullable', 'numeric', 'decimal:0,2', 'min:0.01', 'lt:price'],
            'brand' => ['nullable', 'string', 'max:255'],
            'certification' => ['nullable', 'string', 'max:255'],
            'specification' => ['nullable', 'string', 'max:2000'],
            'badge' => ['nullable', 'string', 'max:100'],
            'in_stock' => ['required', 'boolean'],
        ]);
        $data['slug'] = (Str::slug($data['name']) ?: 'product').'-'.Str::lower(Str::random(8));
        $data['original_price'] = $data['price'];
        $data['price'] = $data['offer_price'] ?? $data['price'];
        unset($data['offer_price']);
        if ($product) {
            unset($data['slug']);
            $product->update($data);
        } else {
            Product::create($data);
        }

        return redirect()->route('admin.products')->with('status', 'Product saved. The storefront is up to date.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        return $this->saveCategory($request);
    }

    public function updateCategory(Request $request, Category $category): RedirectResponse
    {
        return $this->saveCategory($request, $category);
    }

    public function storeProduct(Request $request): RedirectResponse
    {
        return $this->saveProduct($request);
    }

    public function updateProduct(Request $request, Product $product): RedirectResponse
    {
        return $this->saveProduct($request, $product);
    }

    public function deleteProduct(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products')->with('status', 'Product deleted.');
    }

    public function deleteCategory(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return redirect()->route('admin.categories')->with('status', 'Move or delete the products in this category before deleting it.');
        }
        $category->delete();

        return redirect()->route('admin.categories')->with('status', 'Category deleted.');
    }

    public function deleteOrder(Quotation $quotation): RedirectResponse
    {
        $quotation->delete();

        return redirect()->route('admin.orders')->with('status', 'Quotation request deleted.');
    }

    public function deleteUser(Request $request, User $user): RedirectResponse
    {
        if ($user->is_admin || $user->id === $request->user()->id) {
            return redirect()->route('admin.users')->with('status', 'Administrator accounts cannot be deleted here.');
        }
        $user->delete();

        return redirect()->route('admin.users')->with('status', 'User deleted.');
    }
}
