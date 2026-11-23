<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('workflow_column_id')->constrained()->restrictOnDelete();
            $table->string('customer_name', 160);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority', 16)->default('normal');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('due_at')->nullable();
            $table->timestamps();
            $table->index(['shop_id','workflow_column_id','sort_order']);
            $table->index(['shop_id','customer_user_id']);
        });
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_column_id')->nullable()->constrained('workflow_columns')->nullOnDelete();
            $table->foreignId('to_column_id')->constrained('workflow_columns')->restrictOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('orders');
    }
};
