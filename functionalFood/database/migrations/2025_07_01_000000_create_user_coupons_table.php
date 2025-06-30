<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
            $table->timestamp('received_at')->nullable(); // Thời điểm được tặng/lưu
            $table->boolean('is_used')->default(false);   // Đã dùng hay chưa
            $table->timestamp('used_at')->nullable();     // Thời điểm dùng (nếu có)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_coupons');
    }
}; 