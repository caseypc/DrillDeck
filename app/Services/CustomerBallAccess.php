<?php

namespace App\Services;

use App\Models\CustomerBall;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerBallAccess
{
    public function shop(Request $request): Shop
    {
        $shop = $request->user()?->shops()->first();
        abort_unless($shop, 403, 'A pro shop employee membership is required.');
        return $shop;
    }

    public function ball(Shop $shop, CustomerBall $ball): CustomerBall
    {
        abort_unless($ball->shop_id === $shop->id, 404);
        return $ball;
    }

    public function order(Shop $shop, Order $order): Order
    {
        abort_unless($order->shop_id === $shop->id, 404);
        return $order;
    }

    public function belongsToOrder(CustomerBall $ball, Order $order): bool
    {
        if ($ball->shop_id !== $order->shop_id) return false;

        if ($order->customer_id !== null || $ball->customer_id !== null) {
            return $order->customer_id !== null
                && $ball->customer_id !== null
                && $order->customer_id === $ball->customer_id;
        }

        // Legacy records without profiles still obey original ownership policy.
        if ($order->customer_user_id !== null) {
            return $ball->customer_user_id === $order->customer_user_id;
        }

        return $ball->customer_user_id === null
            && mb_strtolower(trim($ball->customer_name)) === mb_strtolower(trim($order->customer_name));
    }

    public function assertMatches(CustomerBall $ball, Order $order): void
    {
        if (! $this->belongsToOrder($ball, $order)) {
            throw ValidationException::withMessages([
                'customer_ball_id' => 'The bowling ball does not belong to this work order customer.',
            ]);
        }
    }
}
