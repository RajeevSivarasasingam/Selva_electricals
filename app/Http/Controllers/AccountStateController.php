<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountStateController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cart' => ['present', 'array', 'max:200'],
            'cart.*' => ['integer', 'min:1', 'max:99'],
            'wishlist' => ['present', 'array', 'max:200'],
            'wishlist.*' => ['string', 'max:255', 'distinct'],
        ]);
        abort_unless((int) $request->input('user_id') === $request->user()->id, 409, 'Your account session changed. Please refresh.');
        $ids = Product::pluck('slug')->all();
        $user = $request->user();
        $user->cart = array_intersect_key($data['cart'], array_flip($ids));
        $user->wishlist = array_values(array_intersect($data['wishlist'], $ids));
        $user->save();

        return response()->json(['saved' => true]);
    }
}
