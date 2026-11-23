<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users');
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });
        Schema::create('shop_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 24);
            $table->timestamps();
            $table->unique(['shop_id', 'user_id']);
        });
        Schema::create('workflow_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('customer_status', 24)->default('in_progress');
            $table->boolean('is_terminal')->default(false);
            $table->timestamps();
            $table->index(['shop_id','sort_order']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('workflow_columns');
        Schema::dropIfExists('shop_users');
        Schema::dropIfExists('shops');
    }
};
