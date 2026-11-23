<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_ball_id')->nullable()->after('assigned_user_id')
                ->constrained('customer_balls')->nullOnDelete();
            $table->index(['shop_id', 'customer_ball_id']);
        });

        Schema::table('drilling_specs', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('customer_ball_id')
                ->constrained('orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('drilling_specs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['shop_id', 'customer_ball_id']);
            $table->dropConstrainedForeignId('customer_ball_id');
        });
    }
};
