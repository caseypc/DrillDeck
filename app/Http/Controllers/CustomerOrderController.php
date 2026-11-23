<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Services\OrderWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = \App\Models\Order::query()
            ->where('customer_user_id', $request->user()->id)
            ->with(['shop','column'])
            ->latest()
            ->paginate(15);

        $shops = Shop::query()->orderBy('name')->get(['id','name','slug']);
        return view('customer.orders', compact('orders','shops'));
    }

    public function store(Request $request, Shop $shop, OrderWorkflowService $workflow): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string','max:4000'],
        ]);
        $profile = $shop->customers()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['name' => $request->user()->name, 'email' => $request->user()->email]
        );
        $workflow->create($shop, [
            'customer_id' => $profile->id,
            ...$data,
            'customer_user_id' => $request->user()->id,
            'customer_name' => $request->user()->name,
            'priority' => 'normal',
        ], $request->user()->id);

        return redirect()->route('portal.index')->with('status', 'Your request has been submitted.');
    }
}
