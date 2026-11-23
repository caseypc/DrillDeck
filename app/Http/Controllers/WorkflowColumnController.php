<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\WorkflowColumn;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WorkflowColumnController extends Controller
{
    private function shop(Request $request): Shop
    {
        $shop=$request->user()->shops()->wherePivotIn('role',['owner','manager'])->first();
        abort_unless($shop,403);
        return $shop;
    }

    public function index(Request $request)
    {
        $shop=$this->shop($request);
        $columns=$shop->columns()->orderBy('sort_order')->get();
        $rules=$shop->rules()->with('target')->orderBy('event')->get();
        return view('shop.workflow',compact('shop','columns','rules'));
    }

    public function store(Request $request): RedirectResponse
    {
        $shop=$this->shop($request);
        $data=$request->validate([
            'name'=>['required','string','max:120'],
            'customer_status'=>['required',Rule::in(['received','in_progress','ready','completed'])],
        ]);
        $shop->columns()->create([...$data,
            'sort_order'=>((int)$shop->columns()->max('sort_order'))+1,
            'is_terminal'=>$data['customer_status']==='completed']);
        return back()->with('status','Column created.');
    }

    public function update(Request $request, WorkflowColumn $column): RedirectResponse
    {
        $shop=$this->shop($request);
        abort_unless($column->shop_id===$shop->id,404);
        $data=$request->validate([
            'name'=>['required','string','max:120'],
            'customer_status'=>['required',Rule::in(['received','in_progress','ready','completed'])],
        ]);
        $column->update([...$data,'is_terminal'=>$data['customer_status']==='completed']);
        return back()->with('status','Column updated.');
    }

    public function reorder(Request $request): JsonResponse|RedirectResponse
    {
        $shop=$this->shop($request);
        $data=$request->validate([
            'column_ids'=>['required','array','min:1'],
            'column_ids.*'=>['required','integer','distinct',
                Rule::exists('workflow_columns','id')->where('shop_id',$shop->id)],
        ]);
        DB::transaction(function () use ($data,$shop) {
            $existing=$shop->columns()->lockForUpdate()->pluck('id')->sort()->values()->all();
            $selected=collect($data['column_ids'])->sort()->values()->all();
            if ($existing!==$selected) {
                throw ValidationException::withMessages(['column_ids'=>'Every column must be included exactly once.']);
            }
            foreach ($data['column_ids'] as $position=>$id) {
                $shop->columns()->whereKey($id)->update(['sort_order'=>$position]);
            }
        });
        return $request->expectsJson()
            ? response()->json(['ok'=>true])
            : back()->with('status','Workflow reordered.');
    }

    public function destroy(Request $request, WorkflowColumn $column): RedirectResponse
    {
        $shop=$this->shop($request);
        abort_unless($column->shop_id===$shop->id,404);
        DB::transaction(function () use ($shop,$column) {
            $column=WorkflowColumn::where('shop_id',$shop->id)->whereKey($column->id)
                ->lockForUpdate()->firstOrFail();
            if ($shop->columns()->count()<=1) {
                throw ValidationException::withMessages(['column'=>'Keep at least one workflow column.']);
            }
            if ($column->orders()->exists()
                || DB::table('workflow_rules')->where('target_column_id',$column->id)->exists()
                || DB::table('order_status_history')->where('to_column_id',$column->id)
                    ->orWhere('from_column_id',$column->id)->exists()) {
                throw ValidationException::withMessages([
                    'column'=>'This stage has orders, history or automation rules. Move/remove references first.',
                ]);
            }
            $column->delete();
        });
        return back()->with('status','Workflow column deleted.');
    }
}
