<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerBall;
use App\Models\Order;
use App\Services\CustomerBallAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use App\Models\ShopBallOption;

class CustomerBallController extends Controller
{
    public function index(Request $request, CustomerBallAccess $access)
    {
        $shop = $access->shop($request);
        $balls = $shop->balls()->with(['customerProfile'])
            ->withCount('specs')->orderByDesc('id')->paginate(20);
        $customers = $shop->customers()->orderBy('name')->get(['id', 'name']);
        $ballOptions = $shop->ballOptions()->where('active',true)->orderBy('kind')->orderBy('sort_order')->orderBy('id')->get();
        return view('shop.balls.index', compact('shop', 'balls', 'customers','ballOptions'));
    }

    public function show(Request $request, CustomerBall $ball, CustomerBallAccess $access)
    {
        $shop = $access->shop($request);
        $access->ball($shop, $ball);
        $ball->load(['customerProfile.fittings' => fn ($query) => $query->orderByDesc('revision'),
            'modelCatalog', 'specs' => fn ($query) => $query->with(['author','order','fitting'])
                ->orderByDesc('revision'), 'orders' => fn ($query) => $query->latest()->limit(20)]);
        $customers = $shop->customers()->orderBy('name')->get(['id','name']);
        return view('shop.balls.show', compact('shop', 'ball', 'customers'));
    }

    public function store(Request $request, CustomerBallAccess $access): RedirectResponse
    {
        $shop = $access->shop($request);
        $data = $request->validate([
            'order_id' => ['nullable','integer', Rule::exists('orders','id')->where('shop_id', $shop->id)],
            'customer_id' => ['nullable','integer', Rule::exists('customers','id')->where('shop_id',$shop->id)],
            'customer_name' => ['required_without_all:order_id,customer_id','nullable','string','max:160'],
            'customer_user_id' => ['prohibited'],
            'ball_name' => ['required','string','max:160'],
            'manufacturer' => ['nullable','string','max:120'],
            'manufacturer_custom' => ['nullable','string','max:120'],
            'ball_name_custom' => ['nullable','string','max:160'],
            'core_type' => ['nullable','string','max:120'],
            'core_type_custom' => ['nullable','string','max:120'],
            'coverstock_type' => ['nullable','string','max:120'],
            'coverstock_type_custom' => ['nullable','string','max:120'],
            'surface' => ['nullable','string','max:120'],
            'surface_custom' => ['nullable','string','max:120'],
            'serial_number' => ['nullable','string','max:120'],
            'pin_distance' => ['nullable','string','max:50'],
            'top_weight' => ['nullable','string','max:50'],
            'color' => ['nullable','string','max:120'],
            'notes' => ['nullable','string','max:4000'],
            'weight_lbs' => ['nullable','integer','between:6,16'],
            'bowling_ball_model_id' => ['nullable','integer','exists:bowling_ball_models,id'],
        ]);

        foreach (['manufacturer','ball_name','core_type','coverstock_type','surface'] as $field) {
            if (($data[$field] ?? null) === '__other__') {
                if (blank($data[$field.'_custom'] ?? null)) {
                    throw ValidationException::withMessages([$field.'_custom'=>'Enter the custom value.']);
                }
                $data[$field] = trim($data[$field.'_custom']);
            }
        }
        $ball = DB::transaction(function () use ($shop, $data) {
            $order = isset($data['order_id'])
                ? Order::where('shop_id',$shop->id)->whereKey($data['order_id'])->lockForUpdate()->firstOrFail()
                : null;
            $profile = isset($data['customer_id'])
                ? Customer::where('shop_id',$shop->id)->findOrFail($data['customer_id'])
                : ($order?->customer_id ? Customer::where('shop_id',$shop->id)->findOrFail($order->customer_id) : null);

            if ($order && isset($data['customer_id']) && (int) $order->customer_id !== (int) $data['customer_id']) {
                throw ValidationException::withMessages(['customer_id'=>'Choose the customer assigned to this order.']);
            }
            if ($order && $profile && $order->customer_id === null) {
                throw ValidationException::withMessages(['customer_id'=>'Link the work order to a customer profile first.']);
            }

            $ball = $shop->balls()->create([
                'customer_id' => $profile?->id,
                'customer_name' => $profile?->name ?? $order?->customer_name ?? $data['customer_name'],
                'customer_user_id' => $profile?->user_id ?? $order?->customer_user_id,
                'ball_name' => $data['ball_name'],
                'manufacturer' => $data['manufacturer'] ?? null,
                'core_type' => $data['core_type'] ?? null,
                'coverstock_type' => $data['coverstock_type'] ?? null,
                'surface' => $data['surface'] ?? null,
                'serial_number' => $data['serial_number'] ?? null,
                'pin_distance' => $data['pin_distance'] ?? null,
                'top_weight' => $data['top_weight'] ?? null,
                'color' => $data['color'] ?? null,
                'notes' => $data['notes'] ?? null,
                'weight_lbs' => $data['weight_lbs'] ?? null,
                'bowling_ball_model_id' => $data['bowling_ball_model_id'] ?? null,
            ]);

            if ($order) {
                $order->forceFill(['customer_ball_id'=>$ball->id])->save();
            }
            return $ball;
        });

        return isset($data['order_id'])
            ? redirect()->route('shop.orders.show', $data['order_id'])->with('status','Bowling ball added and linked to order.')
            : redirect()->route('shop.balls.show', $ball)->with('status','Customer bowling ball added.');
    }

    public function assignCustomer(Request $request, CustomerBall $ball, CustomerBallAccess $access): RedirectResponse
    {
        $shop = $access->shop($request);
        $access->ball($shop, $ball);
        $data = $request->validate([
            'customer_id' => ['required','integer',Rule::exists('customers','id')->where('shop_id',$shop->id)],
        ]);

        DB::transaction(function () use ($shop,$ball,$data) {
            $locked = CustomerBall::where('shop_id',$shop->id)->whereKey($ball->id)->lockForUpdate()->firstOrFail();
            $profile = Customer::where('shop_id',$shop->id)->findOrFail($data['customer_id']);
            if ($locked->customer_id !== null && $locked->customer_id !== $profile->id) {
                throw ValidationException::withMessages(['customer_id'=>'A linked ball cannot be reassigned to another customer.']);
            }
            if ($locked->customer_user_id && $locked->customer_user_id !== $profile->user_id) {
                throw ValidationException::withMessages(['customer_id'=>'The customer account does not match this ball.']);
            }
            // Explicit staff-led reconciliation of legacy walk-in orders, never by name alone.
            $linkedOrders = $locked->orders()->lockForUpdate()->get();
            foreach ($linkedOrders as $order) {
                if ($order->customer_id !== null && $order->customer_id !== $profile->id) {
                    throw ValidationException::withMessages(['customer_id'=>'A linked order belongs to another customer profile.']);
                }
                if ($order->customer_user_id !== null && $order->customer_user_id !== $profile->user_id) {
                    throw ValidationException::withMessages(['customer_id'=>'A linked order has a different customer account.']);
                }
                if ($order->customer_id === null
                    && mb_strtolower(trim($order->customer_name)) !== mb_strtolower(trim($locked->customer_name))) {
                    throw ValidationException::withMessages(['customer_id'=>'Reconcile the linked order customer identity first.']);
                }
            }
            foreach ($linkedOrders as $order) {
                $order->forceFill([
                    'customer_id'=>$profile->id, 'customer_name'=>$profile->name,
                    'customer_user_id'=>$profile->user_id ?? $order->customer_user_id,
                ])->save();
            }
            $locked->update([
                'customer_id'=>$profile->id,
                'customer_name'=>$profile->name,
                'customer_user_id'=>$profile->user_id,
            ]);
        });

        return back()->with('status','Ball assigned to customer profile.');
    }

    public function uploadPhoto(Request $request, CustomerBall $ball, CustomerBallAccess $access): RedirectResponse
    {
        $shop=$access->shop($request);
        $access->ball($shop,$ball);
        $request->validate(['photo'=>['required','image','mimes:jpg,jpeg,png,webp','max:5120']]);
        $path=$request->file('photo')->store('drilldeck/shops/'.$shop->id.'/balls','local');
        $old=$ball->photo_path;
        $ball->update(['photo_path'=>$path]);
        if($old)Storage::disk('local')->delete($old);
        return back()->with('status','Bowling ball photo saved.');
    }

    public function photo(Request $request, CustomerBall $ball, CustomerBallAccess $access)
    {
        $shop=$access->shop($request);
        $access->ball($shop,$ball);
        abort_unless($ball->photo_path && Storage::disk('local')->exists($ball->photo_path),404);
        return Storage::disk('local')->response($ball->photo_path);
    }

    public function link(Request $request, Order $order, CustomerBallAccess $access): RedirectResponse
    {
        $shop = $access->shop($request);
        $access->order($shop, $order);
        $data = $request->validate([
            'customer_ball_id' => ['required','integer',Rule::exists('customer_balls','id')->where('shop_id',$shop->id)],
        ]);
        DB::transaction(function () use ($shop,$order,$data,$access) {
            $locked = Order::where('shop_id',$shop->id)->whereKey($order->id)->lockForUpdate()->firstOrFail();
            $ball = CustomerBall::where('shop_id',$shop->id)->findOrFail($data['customer_ball_id']);
            $access->assertMatches($ball,$locked);
            $locked->forceFill(['customer_ball_id'=>$ball->id])->save();
        });
        return back()->with('status','Bowling ball linked to the order.');
    }

    public function unlink(Request $request, Order $order, CustomerBallAccess $access): RedirectResponse
    {
        $shop = $access->shop($request);
        $access->order($shop,$order);
        $order->forceFill(['customer_ball_id'=>null])->save();
        return back()->with('status','Ball unlinked; historical revisions preserved.');
    }
}
