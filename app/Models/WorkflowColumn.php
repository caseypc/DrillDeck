<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowColumn extends Model
{
    protected $fillable = ['name','sort_order','customer_status','is_terminal'];
    protected function casts(): array { return ['is_terminal' => 'boolean']; }
    public function shop(): BelongsTo { return $this->belongsTo(Shop::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }
}
