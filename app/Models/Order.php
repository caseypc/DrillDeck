<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'customer_id', 'customer_user_id', 'customer_name', 'title', 'description',
        'workflow_column_id', 'sort_order', 'priority', 'due_at', 'assigned_user_id',
    ];

    protected function casts(): array
    {
        return ['due_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function shop(): BelongsTo { return $this->belongsTo(Shop::class); }
    public function column(): BelongsTo { return $this->belongsTo(WorkflowColumn::class, 'workflow_column_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'customer_user_id'); }
    public function customerProfile(): BelongsTo { return $this->belongsTo(Customer::class, 'customer_id'); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_user_id'); }
    public function ball(): BelongsTo { return $this->belongsTo(CustomerBall::class, 'customer_ball_id'); }
    public function drillingSpecs(): HasMany { return $this->hasMany(DrillingSpec::class); }
}
