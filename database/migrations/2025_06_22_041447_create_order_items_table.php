<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemsTable extends Migration
{
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_variant_id')->nullable()->constrained()->onDelete('set null');

            // Thông tin sản phẩm lúc mua (lưu tĩnh để tránh thay đổi)
            $table->string('product_name'); // Tên sản phẩm
            $table->string('variant_name')->nullable(); // Tên biến thể (màu, size...)
            $table->string('sku')->nullable(); // Mã SKU
            $table->string('product_image')->nullable(); // Hình ảnh sản phẩm

            // Thông tin giá và số lượng
            $table->decimal('price', 12, 2); // Giá gốc tại thời điểm mua
            $table->decimal('discount_price', 12, 2)->default(0); // Giá giảm tại thời điểm mua
            $table->decimal('final_price', 12, 2); // Giá cuối cùng (price - discount_price)
            $table->integer('quantity'); // Số lượng mua
            $table->decimal('total', 12, 2); // Tổng tiền (final_price * quantity)

            // Thông tin vận chuyển
            $table->decimal('weight', 8, 3)->nullable(); // Trọng lượng (kg)
            $table->string('dimensions')->nullable(); // Kích thước (LxWxH cm)

            // Trạng thái item
            $table->enum('status', [
                'pending',      // Chờ xử lý
                'processing',   // Đang xử lý
                'shipped',      // Đã gửi
                'delivered',    // Đã giao
                'returned',     // Đã trả hàng
                'refunded'      // Đã hoàn tiền
            ])->default('pending');

            $table->timestamps();
            
            // Indexes
            $table->index(['order_id', 'status']);
            $table->index('product_id');
            $table->index('product_variant_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
}
