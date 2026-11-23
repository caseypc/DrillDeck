<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fitting_specs', function (Blueprint $table) {
            foreach ([
                'name','middle_span_cut','middle_span_outer_cut','ring_span_cut','ring_span_outer_cut',
                'middle_insert_type','ring_insert_type','thumb_insert_type',
                'middle_pitch_lateral','middle_pitch_forward','middle_pitch_reverse',
                'ring_pitch_lateral','ring_pitch_forward','ring_pitch_reverse',
                'thumb_pitch_lateral','thumb_pitch_forward','thumb_pitch_reverse',
                'thumb_flexibility','thumb_oval_type','thumb_start_bit','thumb_oval_width',
                'thumb_oval_degree','thumb_insert_od','thumb_taper_size',
            ] as $field) $table->string($field, 120)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('fitting_specs', function (Blueprint $table) {
            $table->dropColumn([
                'name','middle_span_cut','middle_span_outer_cut','ring_span_cut','ring_span_outer_cut',
                'middle_insert_type','ring_insert_type','thumb_insert_type',
                'middle_pitch_lateral','middle_pitch_forward','middle_pitch_reverse',
                'ring_pitch_lateral','ring_pitch_forward','ring_pitch_reverse',
                'thumb_pitch_lateral','thumb_pitch_forward','thumb_pitch_reverse',
                'thumb_flexibility','thumb_oval_type','thumb_start_bit','thumb_oval_width',
                'thumb_oval_degree','thumb_insert_od','thumb_taper_size',
            ]);
        });
    }
};
