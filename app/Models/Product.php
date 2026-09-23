<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Product extends Model { protected $fillable = ['slug','category_id','name','description','price','image','badge','featured','in_stock']; protected $casts = ['price'=>'decimal:2','featured'=>'boolean','in_stock'=>'boolean']; public function category(){ return $this->belongsTo(Category::class); } }
