<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\FittingSpec;
use App\Services\CustomerBallAccess;
use App\Services\FittingRevisionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FittingSpecController extends Controller
{
    public function create(Request $request, Customer $customer, CustomerBallAccess $access)
    {
        $shop=$access->shop($request);
        abort_unless($customer->shop_id===$shop->id,404);
        $source=$customer->latestFitting();
        return view('shop.customers.fitting-create',compact('shop','customer','source'));
    }

    public function store(Request $request, Customer $customer, CustomerBallAccess $access,
        FittingRevisionService $revisions): RedirectResponse
    {
        $shop=$access->shop($request);
        abort_unless($customer->shop_id===$shop->id,404);
        $fields=$request->validate([
            'name'=>['nullable','string','max:120'],
            'handedness'=>['nullable',Rule::in(['right','left'])],
            'grip_style'=>['nullable',Rule::in(['fingertip','conventional','no-thumb'])],
            'middle_span'=>['nullable','string','max:50'],
            'middle_span_cut'=>['nullable','string','max:120'],
            'middle_span_outer_cut'=>['nullable','string','max:120'],
            'ring_span'=>['nullable','string','max:50'],
            'ring_span_cut'=>['nullable','string','max:120'],
            'ring_span_outer_cut'=>['nullable','string','max:120'],
            'thumb_size'=>['nullable','string','max:50'],
            'middle_finger_size'=>['nullable','string','max:50'],
            'ring_finger_size'=>['nullable','string','max:50'],
            'bridge'=>['nullable','string','max:50'],
            'middle_pitch'=>['nullable','string','max:100'],
            'ring_pitch'=>['nullable','string','max:100'],
            'thumb_pitch'=>['nullable','string','max:100'],
            'pap'=>['nullable','string','max:100'],
            'middle_insert_type'=>['nullable','string','max:120'],
            'ring_insert_type'=>['nullable','string','max:120'],
            'thumb_insert_type'=>['nullable','string','max:120'],
            'middle_pitch_lateral'=>['nullable','string','max:120'],
            'middle_pitch_forward'=>['nullable','string','max:120'],
            'middle_pitch_reverse'=>['nullable','string','max:120'],
            'ring_pitch_lateral'=>['nullable','string','max:120'],
            'ring_pitch_forward'=>['nullable','string','max:120'],
            'ring_pitch_reverse'=>['nullable','string','max:120'],
            'thumb_pitch_lateral'=>['nullable','string','max:120'],
            'thumb_pitch_forward'=>['nullable','string','max:120'],
            'thumb_pitch_reverse'=>['nullable','string','max:120'],
            'thumb_flexibility'=>['nullable','string','max:120'],
            'thumb_oval_type'=>['nullable','string','max:120'],
            'thumb_start_bit'=>['nullable','string','max:120'],
            'thumb_oval_width'=>['nullable','string','max:120'],
            'thumb_oval_degree'=>['nullable','string','max:120'],
            'thumb_insert_od'=>['nullable','string','max:120'],
            'thumb_taper_size'=>['nullable','string','max:120'],
            'notes'=>['nullable','string','max:4000'],
        ]);
        $fitting=$revisions->create($shop,$customer,$fields,$request->user()->id);
        return redirect()->route('shop.customers.fittings.show',[$customer,$fitting])
            ->with('status','New fitting revision saved; prior measurements remain intact.');
    }

    public function show(Request $request, Customer $customer, FittingSpec $fitting, CustomerBallAccess $access)
    {
        $shop=$access->shop($request);
        abort_unless($customer->shop_id===$shop->id && $fitting->shop_id===$shop->id
            && $fitting->customer_id===$customer->id,404);
        $fitting->load('author');
        return view('shop.customers.fitting',compact('shop','customer','fitting'));
    }
}
