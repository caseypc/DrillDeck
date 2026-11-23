<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\WorkflowRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkflowRuleController extends Controller
{
    private function shop(Request $request): Shop
    {
        $shop = $request->user()->shops()->wherePivotIn('role', ['owner','manager'])->first();
        abort_unless($shop, 403);
        return $shop;
    }

    public function store(Request $request): RedirectResponse
    {
        $shop = $this->shop($request);
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'event' => ['required',Rule::in(['created','assigned','rush'])],
            'target_column_id' => ['required','integer',Rule::exists('workflow_columns','id')->where('shop_id',$shop->id)],
        ]);

        $shop->rules()->updateOrCreate(
            ['event' => $data['event']],
            ['name' => $data['name'], 'target_column_id' => $data['target_column_id'], 'is_active' => true],
        );

        return back()->with('status', 'Automation rule saved.');
    }

    public function toggle(Request $request, WorkflowRule $rule): RedirectResponse
    {
        $shop = $this->shop($request);
        abort_unless($rule->shop_id === $shop->id, 404);
        $rule->update(['is_active' => ! $rule->is_active]);
        return back()->with('status', 'Automation rule updated.');
    }

    public function destroy(Request $request, WorkflowRule $rule): RedirectResponse
    {
        $shop = $this->shop($request);
        abort_unless($rule->shop_id === $shop->id, 404);
        $rule->delete();
        return back()->with('status','Automation rule removed.');
    }
}
