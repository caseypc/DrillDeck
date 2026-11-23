<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shop_ball_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 32);
            $table->string('label', 160);
            $table->foreignId('parent_id')->nullable()->constrained('shop_ball_options')->nullOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index(['shop_id', 'kind', 'active']);
        });

        Schema::table('customer_balls', function (Blueprint $table) {
            $table->string('manufacturer', 120)->nullable();
            $table->string('core_type', 120)->nullable();
            $table->string('coverstock_type', 120)->nullable();
            $table->string('surface', 120)->nullable();
            $table->string('serial_number', 120)->nullable();
            $table->string('pin_distance', 50)->nullable();
            $table->string('top_weight', 50)->nullable();
            $table->string('color', 120)->nullable();
            $table->string('notes', 4000)->nullable();
            $table->string('photo_path')->nullable();
        });

        Schema::table('drilling_specs', function (Blueprint $table) {
            $table->string('drilling_angle', 30)->nullable();
            $table->string('pin_to_pap', 30)->nullable();
            $table->string('val_angle', 30)->nullable();
            $table->string('surface_finish', 120)->nullable();
            $table->string('weight_hole_position', 50)->nullable();
            $table->string('weight_hole_size', 50)->nullable();
            $table->string('weight_hole_depth', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('drilling_specs', function (Blueprint $table) {
            $table->dropColumn(['drilling_angle','pin_to_pap','val_angle','surface_finish',
                'weight_hole_position','weight_hole_size','weight_hole_depth']);
        });
        Schema::table('customer_balls', function (Blueprint $table) {
            $table->dropColumn(['manufacturer','core_type','coverstock_type','surface',
                'serial_number','pin_distance','top_weight','color','notes','photo_path']);
        });
        Schema::dropIfExists('shop_ball_options');
    }
};
