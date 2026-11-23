<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BowlingBallModel extends Model
{
    protected $fillable = ['manufacturer', 'name', 'coverstock', 'core', 'rg', 'differential'];
    public function customerBalls(): HasMany { return $this->hasMany(CustomerBall::class); }
}
