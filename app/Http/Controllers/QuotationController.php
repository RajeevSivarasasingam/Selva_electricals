<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Quotation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+() .-]{7,30}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000', 'required_without:items'],
            'items' => ['nullable', 'array', 'max:200'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.product_slug' => ['nullable', 'string', 'exists:products,slug'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);
        $quotation = DB::transaction(function () use ($data): Quotation {
            $items = $data['items'] ?? [];
            unset($data['items']);
            $quotation = Quotation::create($data);
            foreach ($items as $item) {
                $product = isset($item['product_slug'])
                    ? Product::where('slug', $item['product_slug'])->first()
                    : (isset($item['product_id']) ? Product::find($item['product_id']) : null);
                $quotation->items()->create([
                    'product_id' => $product?->id,
                    'product_name' => $product?->name ?? $item['product_name'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return $quotation;
        });

        return response()->json(['message' => 'Quotation request received', 'id' => $quotation->id], 201);
    }
}
