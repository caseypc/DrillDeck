<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerBall extends Model
{
    protected $fillable = [
        'customer_id','customer_user_id','bowling_ball_model_id','customer_name','ball_name','weight_lbs',
        'manufacturer','core_type','coverstock_type','surface','serial_number',
        'pin_distance','top_weight','color','notes','photo_path',
    ];

    public function shop(): BelongsTo { return $this->belongsTo(Shop::class); }
    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'customer_user_id'); }
    public function customerProfile(): BelongsTo { return $this->belongsTo(Customer::class, 'customer_id'); }
    public function modelCatalog(): BelongsTo { return $this->belongsTo(BowlingBallModel::class, 'bowling_ball_model_id'); }
    public function specs(): HasMany { return $this->hasMany(DrillingSpec::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }

    public function latestSpec(): ?DrillingSpec { return $this->specs()->orderByDesc('revision')->first(); }
}
