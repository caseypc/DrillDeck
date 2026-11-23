<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shop_ball_options', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0);
            $table->index(['shop_id', 'kind', 'sort_order']);
        });

        // Preserve preexisting shop options as already initialized. This stops a
        // deleted shop default reappearing on the next db:seed execution.
        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('ball_catalog_seeded')->default(false);
        });

        $groupings = DB::table('shop_ball_options')->select('shop_id', 'kind')
            ->distinct()->get();

        foreach ($groupings as $group) {
            $options = DB::table('shop_ball_options')
                ->where('shop_id', $group->shop_id)
                ->where('kind', $group->kind)
                ->orderBy('label')->orderBy('id')->pluck('id');

            foreach ($options as $position => $id) {
                DB::table('shop_ball_options')->where('id', $id)
                    ->update(['sort_order' => $position]);
            }
            DB::table('shops')->where('id', $group->shop_id)
                ->update(['ball_catalog_seeded' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('shop_ball_options', function (Blueprint $table) {
            $table->dropIndex(['shop_id', 'kind', 'sort_order']);
            $table->dropColumn('sort_order');
        });
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('ball_catalog_seeded');
        });
    }
};
