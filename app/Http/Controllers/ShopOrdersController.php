<?php

namespace App\Http\Controllers;

use App\Services\CustomerBallAccess;
use Illuminate\Http\Request;

class ShopOrdersController extends Controller
{
    public function index(Request $request, CustomerBallAccess $access)
    {
        $shop = $access->shop($request);
        $search = trim((string)$request->query('q',''));

        $orders = $shop->orders()->with(['column','assignee','customerProfile','ball'])
            ->when($search !== '', fn ($query) => $query->where(function ($builder) use ($search) {
                $builder->where('title','like','%'.$search.'%')
                    ->orWhere('customer_name','like','%'.$search.'%')
                    ->orWhere('description','like','%'.$search.'%');
            }))
            ->latest()->paginate(20)->withQueryString();

        return view('shop.orders.index',compact('shop','orders','search'));
    }
}
