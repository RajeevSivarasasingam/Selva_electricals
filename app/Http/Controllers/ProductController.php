<?php
namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;
class ProductController extends Controller {
 public function index(Request $request) { $q=Product::with('category')->where('in_stock',true); if($request->filled('category')) $q->whereHas('category',fn($c)=>$c->where('slug',$request->category)); if($request->filled('search')) $q->where('name','like','%'.$request->search.'%'); return response()->json($q->latest()->paginate(20)); }
 public function show(Product $product) { return response()->json($product->load('category')); }
}
