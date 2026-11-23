<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 160);
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['shop_id', 'user_id']);
            $table->index(['shop_id', 'name']);
        });

        Schema::table('customer_balls', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->index(['shop_id', 'customer_id']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->index(['shop_id', 'customer_id']);
        });

        Schema::create('fitting_specs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('revision');
            $table->string('handedness', 12)->nullable();
            $table->string('grip_style', 32)->nullable();
            $table->string('middle_span', 50)->nullable();
            $table->string('ring_span', 50)->nullable();
            $table->string('thumb_size', 50)->nullable();
            $table->string('middle_finger_size', 50)->nullable();
            $table->string('ring_finger_size', 50)->nullable();
            $table->string('bridge', 50)->nullable();
            $table->string('middle_pitch', 100)->nullable();
            $table->string('ring_pitch', 100)->nullable();
            $table->string('thumb_pitch', 100)->nullable();
            $table->string('pap', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['customer_id', 'revision']);
        });

        Schema::table('drilling_specs', function (Blueprint $table) {
            $table->foreignId('fitting_spec_id')->nullable()->constrained('fitting_specs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('drilling_specs', fn (Blueprint $table) => $table->dropConstrainedForeignId('fitting_spec_id'));
        Schema::dropIfExists('fitting_specs');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['shop_id', 'customer_id']);
            $table->dropConstrainedForeignId('customer_id');
        });
        Schema::table('customer_balls', function (Blueprint $table) {
            $table->dropIndex(['shop_id', 'customer_id']);
            $table->dropConstrainedForeignId('customer_id');
        });
        Schema::dropIfExists('customers');
    }
};
