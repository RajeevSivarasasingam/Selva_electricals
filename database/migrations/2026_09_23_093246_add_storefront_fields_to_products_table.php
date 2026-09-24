<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('original_price', 12, 2)->nullable();
            $table->string('brand')->nullable();
            $table->string('certification')->nullable();
            $table->text('specification')->nullable();
            $table->text('image')->nullable()->change();
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->text('image')->nullable()->change();
        });
        foreach (config('storefront.products', []) as $product) {
            DB::table('products')->where('slug', $product['id'])->update([
                'original_price' => $product['original_price'] ?? null,
                'brand' => $product['brand'] ?? null,
                'certification' => $product['certification'] ?? null,
                'specification' => $product['specification'] ?? null,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['original_price', 'brand', 'certification', 'specification']);
        });
    }
};
