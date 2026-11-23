<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Services\CustomerBallAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderCustomerController extends Controller
{
    public function update(Request $request, Order $order, CustomerBallAccess $access): RedirectResponse
    {
        $shop = $access->shop($request);
        $access->order($shop,$order);
        $data = $request->validate([
            'customer_id' => ['required','integer',Rule::exists('customers','id')->where('shop_id',$shop->id)],
        ]);

        DB::transaction(function () use ($shop,$order,$data) {
            $locked = Order::where('shop_id',$shop->id)->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $profile = Customer::where('shop_id',$shop->id)->findOrFail($data['customer_id']);

            if ($locked->customer_id !== null && $locked->customer_id !== $profile->id) {
                throw ValidationException::withMessages(['customer_id'=>'Existing customer profiles cannot be reassigned on an order.']);
            }
            if ($locked->customer_user_id !== null && $locked->customer_user_id !== $profile->user_id) {
                throw ValidationException::withMessages(['customer_id'=>'The registered customer account does not match.']);
            }
            if ($locked->ball && $locked->ball->customer_id !== $profile->id) {
                throw ValidationException::withMessages(['customer_id'=>'Unlink the old ball or assign it to this profile first.']);
            }
            $locked->forceFill([
                'customer_id'=>$profile->id, 'customer_name'=>$profile->name,
                'customer_user_id'=>$profile->user_id ?? $locked->customer_user_id,
            ])->save();
        });

        return back()->with('status','Customer profile linked to order.');
    }
}
