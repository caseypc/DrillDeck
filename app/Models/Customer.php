<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    // Only server-side workflows set user_id; incoming profile forms never accept it.
    protected $fillable = ['user_id', 'name', 'email', 'phone', 'notes'];

    public function shop(): BelongsTo { return $this->belongsTo(Shop::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function balls(): HasMany { return $this->hasMany(CustomerBall::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }
    public function fittings(): HasMany { return $this->hasMany(FittingSpec::class); }

    public function latestFitting(): ?FittingSpec
    {
        return $this->fittings()->orderByDesc('revision')->first();
    }
}
