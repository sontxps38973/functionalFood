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
            
            // Thông tin khách hàng
            $table->string('name');
            $table->string('phone');
            $table->string('address');
            $table->string('email');
            
            // Mã đơn hàng
            $table->string('order_number')->unique(); // Mã đơn hàng tự động tạo
            
            // Thông tin giá
            $table->decimal('subtotal', 12, 2); // Tổng tiền hàng
            $table->decimal('shipping_fee', 12, 2)->default(0); // Phí vận chuyển
            $table->decimal('tax', 12, 2)->default(0); // Thuế
            $table->decimal('discount', 12, 2)->default(0); // Giá trị giảm giá
            $table->decimal('total', 12, 2); // Tổng tiền cuối cùng

            // Mã giảm giá
            $table->foreignId('coupon_id')->nullable()->constrained()->onDelete('set null');

            // Trạng thái và thanh toán
            $table->enum('status', [
                'pending',      // Chờ xác nhận
                'confirmed',    // Đã xác nhận
                'processing',   // Đang xử lý
                'shipped',      // Đã gửi hàng
                'delivered',    // Đã giao hàng
                'cancelled',    // Đã hủy
                'refunded'      // Đã hoàn tiền
            ])->default('pending');
            
            $table->enum('payment_status', [
                'pending',      // Chờ thanh toán
                'paid',         // Đã thanh toán
                'failed',       // Thanh toán thất bại
                'refunded'      // Đã hoàn tiền
            ])->default('pending');
            
            $table->string('payment_method')->nullable(); // cod, bank_transfer, online_payment
            $table->string('payment_transaction_id')->nullable(); // Mã giao dịch thanh toán
            
            // Vận chuyển
            $table->string('tracking_number')->nullable(); // Mã vận đơn
            $table->string('shipping_method')->nullable(); // Phương thức vận chuyển
            $table->timestamp('shipped_at')->nullable(); // Thời gian gửi hàng
            $table->timestamp('delivered_at')->nullable(); // Thời gian giao hàng
            
            // Ghi chú
            $table->text('notes')->nullable(); // Ghi chú đơn hàng
            $table->text('admin_notes')->nullable(); // Ghi chú admin

            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('order_number');
            $table->index('payment_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}

