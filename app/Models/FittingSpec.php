<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FittingSpec extends Model
{
    protected $fillable = [
        'shop_id','created_by','revision','name','handedness','grip_style',
        'middle_span','middle_span_cut','middle_span_outer_cut',
        'ring_span','ring_span_cut','ring_span_outer_cut',
        'thumb_size','middle_finger_size','ring_finger_size','bridge',
        'middle_pitch','ring_pitch','thumb_pitch','pap','notes',
        'middle_insert_type','ring_insert_type','thumb_insert_type',
        'middle_pitch_lateral','middle_pitch_forward','middle_pitch_reverse',
        'ring_pitch_lateral','ring_pitch_forward','ring_pitch_reverse',
        'thumb_pitch_lateral','thumb_pitch_forward','thumb_pitch_reverse',
        'thumb_flexibility','thumb_oval_type','thumb_start_bit','thumb_oval_width',
        'thumb_oval_degree','thumb_insert_od','thumb_taper_size',
    ];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function drillingSpecs(): HasMany { return $this->hasMany(DrillingSpec::class); }
}
