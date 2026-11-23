<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('assigned_user_id')->nullable()->after('customer_user_id')->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->index(['shop_id', 'assigned_user_id']);
        });

        Schema::create('workflow_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('event', 32);
            $table->foreignId('target_column_id')->constrained('workflow_columns')->restrictOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['shop_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_rules');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['shop_id','assigned_user_id']);
            $table->dropConstrainedForeignId('assigned_user_id');
            $table->dropColumn('completed_at');
        });
    }
};
