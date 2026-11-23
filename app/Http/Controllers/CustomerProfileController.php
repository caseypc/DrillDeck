<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CustomerBallAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerProfileController extends Controller
{
    public function index(Request $request, CustomerBallAccess $access)
    {
        $shop = $access->shop($request);
        $search = trim((string) $request->query('q', ''));
        $customers = $shop->customers()
            ->when($search !== '', fn ($query) => $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%');
            }))
            ->withCount(['balls', 'orders', 'fittings'])
            ->orderBy('name')->paginate(20)->withQueryString();

        return view('shop.customers.index', compact('shop', 'customers', 'search'));
    }

    public function store(Request $request, CustomerBallAccess $access): RedirectResponse
    {
        $shop = $access->shop($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:4000'],
        ]);
        $data['phone'] = \App\Services\PhoneNumber::normalize($data['phone'] ?? null);
        $profile = $shop->customers()->create($data);

        return redirect()->route('shop.customers.show', $profile)->with('status', 'Customer profile created.');
    }

    public function show(Request $request, Customer $customer, CustomerBallAccess $access)
    {
        $shop = $access->shop($request);
        abort_unless($customer->shop_id === $shop->id, 404);

        $customer->load([
            'balls' => fn ($query) => $query->withCount('specs')->orderBy('ball_name'),
            'orders' => fn ($query) => $query->latest()->limit(30),
            'fittings' => fn ($query) => $query->with('author')->orderByDesc('revision'),
        ]);

        $ballOptions = $shop->ballOptions()->where('active',true)->orderBy('kind')->orderBy('sort_order')->orderBy('id')->get();
        return view('shop.customers.show', compact('shop', 'customer','ballOptions'));
    }

    public function update(Request $request, Customer $customer, CustomerBallAccess $access): RedirectResponse
    {
        $shop = $access->shop($request);
        abort_unless($customer->shop_id === $shop->id, 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:4000'],
        ]);
        $data['phone'] = \App\Services\PhoneNumber::normalize($data['phone'] ?? null);
        $customer->update($data);

        return back()->with('status', 'Customer profile updated.');
    }

}
