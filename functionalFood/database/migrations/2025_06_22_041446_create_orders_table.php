<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0); // Giá trị giảm giá
            $table->decimal('total', 12, 2);

            $table->foreignId('coupon_id')->nullable()->constrained()->onDelete('set null'); // Mã đã dùng

            $table->string('status')->default('pending'); // pending, paid, shipped, etc.
            $table->string('payment_method')->nullable(); // cod, momo, vnpay, etc.

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}

