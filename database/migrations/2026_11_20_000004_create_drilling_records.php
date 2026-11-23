<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bowling_ball_models', function (Blueprint $table) {
            $table->id();
            $table->string('manufacturer', 120);
            $table->string('name', 160);
            $table->string('coverstock')->nullable();
            $table->string('core')->nullable();
            $table->decimal('rg', 5, 3)->nullable();
            $table->decimal('differential', 5, 3)->nullable();
            $table->timestamps();
            $table->index(['manufacturer','name']);
        });
        Schema::create('customer_balls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('bowling_ball_model_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name', 160);
            $table->string('ball_name', 160);
            $table->unsignedTinyInteger('weight_lbs')->nullable();
            $table->timestamps();
            $table->index(['shop_id','customer_name']);
        });
        Schema::create('drilling_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_ball_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->unsignedInteger('revision')->default(1);
            $table->string('handedness', 12)->nullable();
            $table->string('grip_style', 32)->nullable();
            $table->string('middle_span', 50)->nullable();
            $table->string('ring_span', 50)->nullable();
            $table->string('thumb_size', 50)->nullable();
            $table->string('bridge', 50)->nullable();
            $table->string('pap', 100)->nullable();
            $table->string('layout', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['customer_ball_id','revision']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('drilling_specs');
        Schema::dropIfExists('customer_balls');
        Schema::dropIfExists('bowling_ball_models');
    }
};
