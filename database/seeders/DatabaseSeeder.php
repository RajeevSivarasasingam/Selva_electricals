<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\{Category,Product};
class DatabaseSeeder extends Seeder {
 public function run(): void { foreach(config('storefront.categories',[]) as $c) Category::updateOrCreate(['slug'=>$c['id']],['name'=>$c['name'],'description'=>$c['description']??null,'stock'=>$c['stock']??null,'image'=>$c['image']??null]); foreach(config('storefront.products',[]) as $p){$cat=Category::where('slug',$p['category'])->first(); Product::updateOrCreate(['slug'=>$p['id']],['category_id'=>$cat?->id,'name'=>$p['name'],'description'=>$p['description']??null,'price'=>(float)($p['price']??0),'image'=>$p['image']??null,'badge'=>$p['badge']??null,'featured'=>(bool)($p['featured']??false),'in_stock'=>true]);} }
}
