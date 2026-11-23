<?php

namespace App\Services;

use App\Models\CustomerBall;
use App\Models\DrillingSpec;
use App\Models\FittingSpec;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DrillingRevisionService
{
    public function create(Shop $shop, CustomerBall $ball, array $fields, int $actorId, ?Order $order = null): DrillingSpec
    {
        return DB::transaction(function () use ($shop, $ball, $fields, $actorId, $order) {
            $lockedBall = CustomerBall::where('shop_id', $shop->id)
                ->whereKey($ball->id)->lockForUpdate()->firstOrFail();

            if ($order) {
                $lockedOrder = Order::where('shop_id', $shop->id)->whereKey($order->id)
                    ->lockForUpdate()->firstOrFail();

                if ($lockedOrder->customer_ball_id !== $lockedBall->id) {
                    throw ValidationException::withMessages(['order_id' => 'This order is not linked to that ball.']);
                }
            }

            $fittingId = $fields['fitting_spec_id'] ?? null;
            if ($fittingId !== null) {
                $valid = FittingSpec::where('shop_id', $shop->id)
                    ->where('customer_id', $lockedBall->customer_id)
                    ->whereKey($fittingId)->exists();
                if (! $lockedBall->customer_id || ! $valid) {
                    throw ValidationException::withMessages([
                        'fitting_spec_id' => 'Choose a fitting revision belonging to this ball’s customer.',
                    ]);
                }
            }

            return $lockedBall->specs()->create([
                ...$fields,
                'shop_id' => $shop->id,
                'created_by' => $actorId,
                'order_id' => $order?->id,
                'revision' => ((int) $lockedBall->specs()->max('revision')) + 1,
            ]);
        }, 3);
    }
}
