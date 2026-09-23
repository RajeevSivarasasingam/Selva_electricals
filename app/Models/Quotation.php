<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Quotation extends Model { protected $fillable = ['name','phone','email','message','status']; public function items(){ return $this->hasMany(QuotationItem::class); } }
