<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            // Đổi status boolean thành enum
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('comment');
            $table->boolean('flagged')->default(false)->after('status');
            $table->string('admin_note')->nullable()->after('flagged');
        });
        // Cập nhật dữ liệu cũ
        DB::statement("UPDATE product_reviews SET status = 'approved' WHERE status = 1");
        DB::statement("UPDATE product_reviews SET status = 'rejected' WHERE status = 0");
    }

    public function down()
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->boolean('status')->default(true);
            $table->dropColumn(['admin_note', 'flagged', 'status']);
        });
    }
}; 