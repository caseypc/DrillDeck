<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use Illuminate\Validation\ValidationException;
use App\Models\Shop;
use App\Services\CustomerBallAccess;
use App\Services\OrderWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ShopBoardController extends Controller
{
    private function shop(Request $request): Shop
    {
        $shop = $request->user()->shops()->first();
        abort_unless($shop, 403, 'An employee shop membership is required.');
        return $shop;
    }

    private function order(Shop $shop, Order $order): Order
    {
        abort_unless($order->shop_id === $shop->id, 404);
        return $order;
    }

    private function fields(Request $request, Shop $shop): array
    {
        return $request->validate([
            'customer_name' => ['nullable','required_without:customer_id','string','max:160'],
            'customer_id' => ['nullable','integer',Rule::exists('customers','id')->where('shop_id',$shop->id)],
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string','max:4000'],
            'priority' => ['required', Rule::in(['normal','rush'])],
            'due_at' => ['nullable','date'],
            'assigned_user_id' => [
                'nullable','integer',Rule::exists('shop_users','user_id')->where('shop_id', $shop->id),
            ],
        ]);
    }

    public function index(Request $request)
    {
        $shop = $this->shop($request);
        $columns = $shop->columns()->orderBy('sort_order')->with(['orders' => function ($query) {
            $query->with(['assignee', 'ball', 'customerProfile'])->orderBy('sort_order')->orderBy('id');
        }])->get();
        $employees = $shop->employees()->orderBy('name')->get();
        $customers = $shop->customers()->orderBy('name')->get();
        return view('shop.board', compact('shop','columns','employees','customers'));
    }

    public function show(Request $request, Order $order, CustomerBallAccess $access)
    {
        $shop = $this->shop($request);
        $order = $this->order($shop, $order);
        $order->load(['customerProfile','ball.customerProfile','ball.specs' => fn ($query) => $query->with('author')->orderByDesc('revision'), 'drillingSpecs']);
        $employees = $shop->employees()->orderBy('name')->get();
        $columns = $shop->columns()->orderBy('sort_order')->get();

        $candidateBalls = $shop->balls()->when(
            $order->customer_id !== null,
            fn ($query) => $query->where('customer_id',$order->customer_id),
            fn ($query) => $query->when(
                $order->customer_user_id !== null,
                fn ($q) => $q->where('customer_user_id',$order->customer_user_id)->whereNull('customer_id'),
                fn ($q) => $q->whereNull('customer_id')->whereNull('customer_user_id')
                    ->whereRaw('LOWER(TRIM(customer_name)) = ?', [mb_strtolower(trim($order->customer_name))])
            )
        )->orderBy('ball_name')->get();
        $customers = $shop->customers()->orderBy('name')->get();
        $fitting = $order->customerProfile?->latestFitting();
        $ballOptions=$shop->ballOptions()->where('active',true)->orderBy('kind')->orderBy('sort_order')->orderBy('id')->get();

        $history = DB::table('order_status_history')
            ->leftJoin('workflow_columns as destination', 'destination.id', '=', 'order_status_history.to_column_id')
            ->leftJoin('users as actor', 'actor.id', '=', 'order_status_history.actor_user_id')
            ->where('order_status_history.order_id', $order->id)
            ->orderByDesc('order_status_history.id')
            ->select('order_status_history.*', 'destination.name as destination_name', 'actor.name as actor_name')
            ->get();

        return view('shop.order', compact('shop','order','employees','columns','history','candidateBalls','customers','fitting','ballOptions'));
    }

    public function store(Request $request, OrderWorkflowService $workflow): RedirectResponse
    {
        $shop = $this->shop($request);
        $data = $this->fields($request, $shop);
        if (! empty($data['customer_id'])) {
            $profile = $shop->customers()->findOrFail($data['customer_id']);
            $data['customer_name'] = $profile->name;
            $data['customer_user_id'] = $profile->user_id;
        }
        $order = $workflow->create($shop, $data, $request->user()->id);
        return redirect()->route('shop.orders.show', $order)->with('status', 'Work order created.');
    }

    public function update(Request $request, Order $order, OrderWorkflowService $workflow): RedirectResponse
    {
        $shop = $this->shop($request);
        $this->order($shop, $order);
        $data = $this->fields($request, $shop);

        if ($order->customer_id !== null) {
            if (isset($data['customer_id']) && $order->customer_id !== (int) $data['customer_id']) {
                throw ValidationException::withMessages(['customer_id'=>'Customer cannot be reassigned on this ticket.']);
            }
            $data['customer_id'] = $order->customer_id;
            $data['customer_name'] = $order->customerProfile->name;
        } elseif (! empty($data['customer_id'])) {
            throw ValidationException::withMessages(['customer_id'=>'Link a profile using the customer profile panel first.']);
        }

        // Existing ball associations must never be silently transferred to a different customer.
        if ($order->ball && mb_strtolower(trim($data['customer_name'])) !== mb_strtolower(trim($order->customer_name))) {
            return back()->withErrors(['customer_name' => 'Unlink the bowling ball before changing the customer name.'])->withInput();
        }

        $workflow->update($order, $data, $request->user()->id);
        return back()->with('status', 'Work order updated.');
    }

    public function move(Request $request, Order $order, OrderWorkflowService $workflow): JsonResponse
    {
        $shop = $this->shop($request);
        $this->order($shop, $order);
        $data = $request->validate([
            'workflow_column_id' => [
                'required', 'integer',
                Rule::exists('workflow_columns', 'id')->where('shop_id', $shop->id),
            ],
            'sort_order' => ['nullable','integer','min:0'],
        ]);

        DB::transaction(function () use ($order,$data,$request,$workflow) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $workflow->transition($locked, $data['workflow_column_id'], $request->user()->id);
            if (array_key_exists('sort_order', $data)) {
                $locked->sort_order = $data['sort_order'];
                $locked->save();
            }
        });

        return response()->json(['ok' => true]);
    }
}
