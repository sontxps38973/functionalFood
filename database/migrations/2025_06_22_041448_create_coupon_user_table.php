<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponUserTable extends Migration
{
    public function up()
    {
        Schema::create('coupon_user', function (Blueprint $table) {
            $table->id();

            // Liên kết coupon và user
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Số lần user này đã dùng mã (dùng để cộng dồn nếu được dùng nhiều lần)
            $table->integer('usage_count')->default(1);

            // Thời điểm dùng gần nhất
            $table->timestamp('used_at')->nullable();

            // Đơn hàng sử dụng mã này (nếu có)
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupon_user');
    }
}
