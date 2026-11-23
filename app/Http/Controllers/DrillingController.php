<?php

namespace App\Http\Controllers;

use App\Models\CustomerBall;
use App\Services\CustomerBallAccess;
use App\Services\DrillingRevisionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DrillingController extends Controller
{
    public function index(Request $request, CustomerBallAccess $access)
    {
        $shop = $access->shop($request);
        $balls = CustomerBall::where('shop_id', $shop->id)
            ->withCount('specs')->latest()->limit(100)->get();

        return view('shop.drilling', compact('shop', 'balls'));
    }

    public function store(
        Request $request,
        CustomerBallAccess $access,
        DrillingRevisionService $revisions
    ) {
        $shop = $access->shop($request);
        $data = $request->validate([
            'customer_name' => ['required','string','max:160'],
            'ball_name' => ['required','string','max:160'],
            'weight_lbs' => ['nullable','integer','between:6,16'],
            'handedness' => ['nullable','in:right,left'],
            'grip_style' => ['nullable','in:fingertip,conventional,no-thumb'],
            'middle_span' => ['nullable','string','max:50'],
            'ring_span' => ['nullable','string','max:50'],
            'thumb_size' => ['nullable','string','max:50'],
            'bridge' => ['nullable','string','max:50'],
            'pap' => ['nullable','string','max:100'],
            'layout' => ['nullable','string','max:100'],
            'notes' => ['nullable','string','max:4000'],
        ]);

        $ball = DB::transaction(function () use ($shop, $request, $data, $revisions) {
            $ball = $shop->balls()->create([
                'customer_name' => $data['customer_name'],
                'ball_name' => $data['ball_name'],
                'weight_lbs' => $data['weight_lbs'] ?? null,
            ]);

            $revisions->create(
                $shop,
                $ball,
                collect($data)->except(['customer_name', 'ball_name', 'weight_lbs'])->all(),
                $request->user()->id
            );
            return $ball;
        });

        return redirect()->route('shop.balls.show', $ball)
            ->with('status','Draft drilling worksheet saved. Verify all measurements before drilling.');
    }
}
