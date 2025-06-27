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
        Schema::create('customer_ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // VD: Đồng, Bạc, Vàng
            $table->integer('level')->unique(); // để sắp xếp tăng dần theo rank
            $table->decimal('min_total_spent', 12, 2); // số tiền phải đạt
            $table->timestamps();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('customer_rank_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    /** 
     * Reverse the migrations.
     */
public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['customer_rank_id']);
        $table->dropColumn('customer_rank_id');
    });

    Schema::dropIfExists('customer_ranks');
}
};
