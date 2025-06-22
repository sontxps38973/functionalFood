<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    public function up()
    {
         Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            // Mã code và mô tả
            $table->string('code')->unique();
            $table->string('description')->nullable();

            // Loại giảm giá: phần trăm hoặc cố định
            $table->enum('type', ['percent', 'fixed']);
            $table->decimal('value', 12, 2);
            $table->decimal('max_discount', 12, 2)->nullable();

            // Phạm vi áp dụng: toàn đơn, sản phẩm, danh mục
            $table->enum('scope', ['order', 'product', 'category'])->default('order');
            $table->json('target_ids')->nullable();

            // Điều kiện sử dụng
            $table->decimal('min_order_value', 12, 2)->nullable();
            $table->integer('usage_limit')->nullable(); // tổng số lượt dùng toàn hệ thống
            $table->integer('used_count')->default(0); // đã dùng bao nhiêu lần
            $table->boolean('only_once_per_user')->default(false);
            $table->boolean('first_time_only')->default(false);

            // Giới hạn cho nhóm người dùng (VIP,...)
            $table->json('allowed_user_groups')->nullable(); // ví dụ: ["vip", "gold"]

            // Thời gian áp dụng
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->json('time_rules')->nullable(); // flash sale, định kỳ...

            // Trạng thái
            $table->boolean('is_active')->default(true);

            // Hình thức áp dụng
            $table->boolean('requires_code_input')->default(true);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupons');
    }
}
