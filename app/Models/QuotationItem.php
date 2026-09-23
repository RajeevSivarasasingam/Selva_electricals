<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class QuotationItem extends Model { protected $fillable = ['quotation_id','product_id','product_name','quantity']; public function product(){ return $this->belongsTo(Product::class); } }
