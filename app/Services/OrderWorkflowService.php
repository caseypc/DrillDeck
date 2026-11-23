<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderWorkflowService
{
    /**
     * Call from within a database transaction with an order row locked.
     * All stage changes, including automated changes, share a single audit path.
     */
    public function transition(Order $order, int $targetColumnId, ?int $actorUserId): void
    {
        $target = DB::table('workflow_columns')
            ->where('shop_id', $order->shop_id)
            ->where('id', $targetColumnId)
            ->first();

        if (! $target) {
            throw ValidationException::withMessages(['workflow_column_id' => 'Invalid workflow column for this shop.']);
        }

        if ((int) $order->workflow_column_id === $targetColumnId) {
            return;
        }

        $previous = $order->workflow_column_id;
        $order->workflow_column_id = $targetColumnId;
        $order->sort_order = 0;
        $order->completed_at = $target->is_terminal ? now() : null;
        $order->save();

        DB::table('order_status_history')->insert([
            'order_id' => $order->id,
            'from_column_id' => $previous,
            'to_column_id' => $targetColumnId,
            'actor_user_id' => $actorUserId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * One active rule per event per shop; no cascading triggers.
     * A rule is an optional override, not a second ticket status.
     */
    public function applyEvent(Order $order, string $event, ?int $actorUserId): void
    {
        $target = DB::table('workflow_rules')
            ->where('shop_id', $order->shop_id)
            ->where('event', $event)
            ->where('is_active', true)
            ->value('target_column_id');

        if ($target !== null) {
            $this->transition($order, (int) $target, $actorUserId);
        }
    }

    public function create(Shop $shop, array $attributes, int $actorUserId): Order
    {
        return DB::transaction(function () use ($shop, $attributes, $actorUserId) {
            $initial = $shop->columns()->orderBy('sort_order')->firstOrFail();
            $order = $shop->orders()->create([
                ...$attributes,
                'workflow_column_id' => $initial->id,
                'sort_order' => 0,
            ]);

            DB::table('order_status_history')->insert([
                'order_id' => $order->id,
                'from_column_id' => null,
                'to_column_id' => $initial->id,
                'actor_user_id' => $actorUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->applyEvent($order, 'created', $actorUserId);

            if ($order->assigned_user_id !== null) {
                $this->applyEvent($order, 'assigned', $actorUserId);
            }

            if ($order->priority === 'rush') {
                $this->applyEvent($order, 'rush', $actorUserId);
            }

            return $order;
        });
    }

    public function update(Order $order, array $attributes, int $actorUserId): Order
    {
        return DB::transaction(function () use ($order, $attributes, $actorUserId) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $beforeAssignee = $locked->assigned_user_id;
            $beforePriority = $locked->priority;
            $locked->fill($attributes)->save();

            if (array_key_exists('assigned_user_id', $attributes) && $attributes['assigned_user_id'] !== null
                && (int) $beforeAssignee !== (int) $attributes['assigned_user_id']) {
                $this->applyEvent($locked, 'assigned', $actorUserId);
            }

            if ($beforePriority !== 'rush' && $locked->priority === 'rush') {
                $this->applyEvent($locked, 'rush', $actorUserId);
            }

            return $locked->refresh();
        });
    }
}
