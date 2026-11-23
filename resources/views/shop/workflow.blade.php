@extends('layouts.shop')
@section('title','Workflow settings')
@section('heading',$shop->name.' · Workflow')
@section('shop-content')
<p class="text-secondary">Drag the grip handle to reorder columns. Changes save automatically. Delete is only available for empty stages with no historical or automation references.</p>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="h5 mb-0">Columns</h2>
            <span id="column-save-status" role="status" aria-live="polite" class="small text-secondary"></span>
        </div>
        <div id="workflow-sort" data-reorder-url="{{ route('shop.workflow.reorder') }}">
        @foreach($columns as $column)
            <div class="card mb-2" data-column-id="{{ $column->id }}"><div class="card-body">
                <div class="d-flex gap-2 align-items-center">
                    <span class="column-drag-handle fs-4 text-secondary" role="img" aria-label="Drag to reorder" title="Drag to reorder" style="cursor:grab;"><i class="fa-solid fa-grip-vertical"></i></span>
                    <form method="POST" action="{{ route('shop.workflow.update',$column) }}" class="row g-2 align-items-end flex-grow-1">
                        @csrf @method('PATCH')
                        <div class="col-md-5"><label class="form-label" for="col-{{ $column->id }}">Column name</label><input class="form-control" id="col-{{ $column->id }}" name="name" maxlength="120" required value="{{ $column->name }}"></div>
                        <div class="col-md-4"><label class="form-label" for="status-{{ $column->id }}">Customer status</label><select class="form-select" id="status-{{ $column->id }}" name="customer_status">
                        @foreach(['received'=>'Received','in_progress'=>'In progress','ready'=>'Ready','completed'=>'Completed'] as $value=>$label)<option value="{{ $value }}" @selected($column->customer_status===$value)>{{ $label }}</option>@endforeach
                        </select></div>
                        <div class="col-md-3">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Save</button>
                                <button type="submit" form="delete-column-{{ $column->id }}" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash me-1"></i> Delete</button>
                            </div>
                        </div>
                    </form>
                </div>
                <form id="delete-column-{{ $column->id }}" method="POST" action="{{ route('shop.workflow.destroy',$column) }}" onsubmit="return confirm('Delete this empty workflow column?')">
                    @csrf @method('DELETE')
                </form>
            </div></div>
        @endforeach
        </div>
        <div class="alert alert-light small">Touch devices: press and hold the grip handle to rearrange columns. The saving indicator reports progress or errors.</div>
    </div>
    <div class="col-lg-5">
        <div class="card mb-4"><div class="card-body">
            <h2 class="h5">Add workflow column</h2>
            <form method="POST" action="{{ route('shop.workflow.store') }}">@csrf
                <div class="mb-3"><label class="form-label" for="new-name">Name</label><input class="form-control" id="new-name" name="name" maxlength="120" required></div>
                <div class="mb-3"><label class="form-label" for="new-status">Customer status</label><select class="form-select" id="new-status" name="customer_status">
                    <option value="in_progress">In progress</option><option value="received">Received</option><option value="ready">Ready</option><option value="completed">Completed</option>
                </select></div>
                <button class="btn btn-primary w-100">Add column</button>
            </form>
        </div></div>
        <div class="card"><div class="card-body">
            <h2 class="h5">Automatic transitions</h2>
            <p class="small text-secondary">Optional destination for new, assigned, or newly marked Rush orders. One rule per trigger.</p>
            @foreach($rules as $rule)
                <div class="border-bottom py-3">
                    <strong>{{ $rule->name }}</strong>
                    <div class="small text-secondary">{{ ucfirst($rule->event) }} → {{ $rule->target?->name ?? 'Missing stage' }} · {{ $rule->is_active ? 'Active' : 'Paused' }}</div>
                    <div class="d-flex gap-2 mt-2">
                        <form method="POST" action="{{ route('shop.rules.toggle',$rule) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary">{{ $rule->is_active ? 'Pause' : 'Enable' }}</button></form>
                        <form method="POST" action="{{ route('shop.rules.destroy',$rule) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Remove</button></form>
                    </div>
                </div>
            @endforeach
            <form action="{{ route('shop.rules.store') }}" method="POST" class="mt-3">@csrf
                <div class="mb-2"><label for="rule-name" class="form-label">Rule name</label><input id="rule-name" name="name" class="form-control" maxlength="120" required></div>
                <div class="mb-2"><label for="rule-event" class="form-label">When</label><select id="rule-event" name="event" class="form-select"><option value="created">Order created</option><option value="assigned">Employee assigned</option><option value="rush">Marked Rush</option></select></div>
                <div class="mb-3"><label for="rule-target" class="form-label">Move to</label><select id="rule-target" name="target_column_id" class="form-select">@foreach($columns as $column)<option value="{{ $column->id }}">{{ $column->name }}</option>@endforeach</select></div>
                <button class="btn btn-primary w-100">Save automation</button>
            </form>
        </div></div>
    </div>
</div>
@endsection
