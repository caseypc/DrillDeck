<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowRule extends Model
{
    protected $fillable = ['name','event','target_column_id','is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(WorkflowColumn::class, 'target_column_id');
    }
}
