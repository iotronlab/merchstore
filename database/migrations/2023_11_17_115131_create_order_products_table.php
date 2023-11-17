<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnUpdate()->nullOnDelete();
            $table->integer('quantity')->unsigned();
            $table->integer('base_amount');  // subtotal  base_price
            $table->integer('discount_amount');  // subtotal discount
            $table->integer('tax_amount');      // subtotal tax
            $table->integer('total_amount');      // subtotal final price
            $table->boolean('has_tax')->storedAs('IF(tax_amount > 0, true, false)');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_products');
    }
};