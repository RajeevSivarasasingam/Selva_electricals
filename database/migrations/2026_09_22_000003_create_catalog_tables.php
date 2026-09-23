<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('categories', function (Blueprint $table) { $table->id(); $table->string('slug')->unique(); $table->string('name'); $table->text('description')->nullable(); $table->string('stock')->nullable(); $table->string('image')->nullable(); $table->timestamps(); });
        Schema::create('products', function (Blueprint $table) { $table->id(); $table->string('slug')->unique(); $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete(); $table->string('name'); $table->text('description')->nullable(); $table->decimal('price', 12, 2)->default(0); $table->string('image')->nullable(); $table->string('badge')->nullable(); $table->boolean('featured')->default(false); $table->boolean('in_stock')->default(true); $table->timestamps(); });
        Schema::create('quotations', function (Blueprint $table) { $table->id(); $table->string('name'); $table->string('phone'); $table->string('email')->nullable(); $table->text('message')->nullable(); $table->string('status')->default('new'); $table->timestamps(); });
        Schema::create('quotation_items', function (Blueprint $table) { $table->id(); $table->foreignId('quotation_id')->constrained()->cascadeOnDelete(); $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete(); $table->string('product_name'); $table->unsignedInteger('quantity')->default(1); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('quotation_items'); Schema::dropIfExists('quotations'); Schema::dropIfExists('products'); Schema::dropIfExists('categories'); }
};
