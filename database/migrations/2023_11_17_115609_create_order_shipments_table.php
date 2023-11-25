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
        Schema::create('order_shipments', function (Blueprint $table) {
            $table->id();
            $table->integer('total_quantity');

            $table->json('last_update')->nullable();
            $table->string('status')->default('processing');
            $table->string('invoice_uid')->nullable();
            $table->boolean('cod')->default(false);

            // Shipping Related
            $table->string('tracking_id')->nullable();
            $table->json('tracking_data')->nullable();

            $table->string('provider_order_id')->nullable();
            $table->string('shipment_id')->nullable();
            $table->json('shipment_track_activities')->nullable();
            $table->json('details')->nullable();

            // order id
            $table->foreignId('order_id')->constrained('orders')->onUpdate('cascade')->onDelete('cascade');
            // pickup address
            $table->foreignId('pickup_address')->nullable()->constrained('addresses')->onUpdate('cascade')->onDelete('set null');
            // delivery address
            $table->foreignId('delivery_address')->nullable()->constrained('addresses')->onUpdate('cascade')->onDelete('set null');
            // shipping method
            $table->foreignId('shipping_provider_id')->nullable()->constrained('shipping_providers')->onUpdate('cascade')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_shipments');
    }
};
