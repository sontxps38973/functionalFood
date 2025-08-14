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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_reference')->nullable()->after('payment_transaction_id'); // VNPay transaction reference
            $table->timestamp('paid_at')->nullable()->after('payment_reference'); // Thời gian thanh toán thành công
            $table->string('payment_error')->nullable()->after('paid_at'); // Mã lỗi thanh toán nếu có
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_reference', 'paid_at', 'payment_error']);
        });
    }
};
