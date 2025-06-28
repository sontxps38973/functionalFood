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
        Schema::create('event_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('event_price', 10, 2); // Giá trong sự kiện
            $table->decimal('original_price', 10, 2); // Giá gốc
            $table->decimal('discount_price', 10, 2); // Giá sau giảm
            $table->integer('quantity_limit')->default(0); // Số lượng giới hạn (0 = không giới hạn)
            $table->integer('sold_quantity')->default(0); // Số lượng đã bán
            $table->enum('status', ['active', 'inactive', 'sold_out'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unique(['event_id', 'product_id']);
            $table->index(['event_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_products');
    }
};
