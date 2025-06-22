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
            $table->foreignId('product_variant_id')->nullable()->constrained()->onDelete('set null'); // Nếu có biến thể

            $table->string('product_name'); // Lưu tên lúc mua (đề phòng bị xóa)
            $table->string('variant_name')->nullable(); // Màu/sz... lưu tĩnh

            $table->decimal('price', 12, 2); // Giá tại thời điểm mua
            $table->integer('quantity'); // Số lượng mua
            $table->decimal('total', 12, 2); // price * quantity

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
}
