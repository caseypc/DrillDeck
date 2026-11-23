<?php

namespace App\Http\Controllers;

use App\Models\CustomerBall;
use App\Models\DrillingSpec;
use App\Models\Order;
use App\Services\CustomerBallAccess;
use App\Services\DrillingRevisionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DrillingSpecController extends Controller
{
    private function fields(Request $request): array
    {
        return $request->validate([
            'handedness'=>['nullable',Rule::in(['right','left'])],
            'grip_style'=>['nullable',Rule::in(['fingertip','conventional','no-thumb'])],
            'middle_span'=>['nullable','string','max:50'],
            'ring_span'=>['nullable','string','max:50'],
            'thumb_size'=>['nullable','string','max:50'],
            'bridge'=>['nullable','string','max:50'],
            'pap'=>['nullable','string','max:100'],
            'layout'=>['nullable','string','max:100'],
            'notes'=>['nullable','string','max:4000'],
            'drilling_angle'=>['nullable','string','max:30'],
            'pin_to_pap'=>['nullable','string','max:30'],
            'val_angle'=>['nullable','string','max:30'],
            'surface_finish'=>['nullable','string','max:120'],
            'weight_hole_position'=>['nullable','string','max:50'],
            'weight_hole_size'=>['nullable','string','max:50'],
            'weight_hole_depth'=>['nullable','string','max:50'],
            'order_id'=>['nullable','integer'],
            'fitting_spec_id'=>['nullable','integer'],
        ]);
    }

    public function store(Request $request, CustomerBall $ball, CustomerBallAccess $access,
        DrillingRevisionService $revisions): RedirectResponse
    {
        $shop=$access->shop($request);
        $access->ball($shop,$ball);
        $data=$this->fields($request);
        $order=null;
        if(!empty($data['order_id'])){
            $order=Order::where('shop_id',$shop->id)->findOrFail($data['order_id']);
            $access->assertMatches($ball,$order);
        }
        unset($data['order_id']);
        $revision=$revisions->create($shop,$ball,$data,$request->user()->id,$order);
        return redirect()->route('shop.balls.specs.show',[$ball,$revision])
            ->with('status',"Drilling worksheet revision {$revision->revision} saved.");
    }

    public function show(Request $request, CustomerBall $ball, DrillingSpec $spec, CustomerBallAccess $access)
    {
        $shop=$access->shop($request);
        $access->ball($shop,$ball);
        abort_unless($spec->shop_id===$shop->id && $spec->customer_ball_id===$ball->id,404);
        $spec->load(['author','order','fitting']);
        return view('shop.balls.spec',compact('shop','ball','spec'));
    }
}
