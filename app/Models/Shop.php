<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    protected $fillable = ['name','slug','owner_user_id'];
    public function getRouteKeyName(): string { return 'slug'; }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'shop_users')->withPivot('role')->withTimestamps();
    }

    public function columns(): HasMany { return $this->hasMany(WorkflowColumn::class); }
    public function orders(): HasMany { return $this->hasMany(Order::class); }
    public function rules(): HasMany { return $this->hasMany(WorkflowRule::class); }
    public function balls(): HasMany { return $this->hasMany(CustomerBall::class); }
    public function customers(): HasMany { return $this->hasMany(Customer::class); }
    public function ballOptions(): HasMany { return $this->hasMany(ShopBallOption::class); }

    public function seedDefaultColumns(): void
    {
        foreach (['Received','Measured','Drilling','Quality Check','Ready','Completed'] as $index => $name) {
            $this->columns()->create([
                'name' => $name, 'sort_order' => $index,
                'is_terminal' => $name === 'Completed',
                'customer_status' => match ($name) {
                    'Received' => 'received',
                    'Ready' => 'ready',
                    'Completed' => 'completed',
                    default => 'in_progress',
                },
            ]);
        }
    }
}
