<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrillingSpec extends Model
{
    protected $fillable = [
        'shop_id','order_id','fitting_spec_id','created_by','revision',
        'handedness','grip_style','middle_span','ring_span','thumb_size',
        'bridge','pap','layout','notes','drilling_angle','pin_to_pap','val_angle',
        'surface_finish','weight_hole_position','weight_hole_size','weight_hole_depth',
    ];

    public function ball(): BelongsTo { return $this->belongsTo(CustomerBall::class, 'customer_ball_id'); }
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function author(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function fitting(): BelongsTo { return $this->belongsTo(FittingSpec::class, 'fitting_spec_id'); }
}
