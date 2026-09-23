<?php
namespace App\Http\Controllers;
use App\Models\{Quotation,Product};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class QuotationController extends Controller {
 public function store(Request $request) { $data=$request->validate(['name'=>'required|string|max:120','phone'=>'required|string|max:30','email'=>'nullable|email','message'=>'nullable|string|max:2000','items'=>'nullable|array','items.*.product_id'=>'nullable|integer|exists:products,id','items.*.product_name'=>'required|string|max:255','items.*.quantity'=>'required|integer|min:1|max:999']); $quotation=DB::transaction(function() use($data){$items=$data['items']??[]; unset($data['items']); $q=Quotation::create($data); $q->items()->createMany($items); return $q;}); return response()->json(['message'=>'Quotation request received','id'=>$quotation->id],201); }
}
