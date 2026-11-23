@extends('layouts.shop')
@section('title', 'Order #'.$order->id)
@section('heading', 'Work Order #'.$order->id)
@section('shop-content')
<div class="mb-3"><a href="{{ route('shop.board') }}" class="text-decoration-none"><i class="fa-solid fa-arrow-left me-1"></i> Back to board</a></div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card"><div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h5 mb-0">Order details</h2>
                <span class="badge text-bg-primary">{{ $order->column->name }}</span>
            </div>
            <form action="{{ route('shop.orders.update', $order) }}" method="POST" class="row g-3">
                @csrf @method('PUT')
                <div class="col-md-6"><label for="customer_name" class="form-label">Customer name</label><input class="form-control" id="customer_name" name="customer_name" value="{{ old('customer_name',$order->customer_name) }}" maxlength="160" required></div>
                <div class="col-md-6"><label for="title" class="form-label">Service</label><input class="form-control" id="title" name="title" value="{{ old('title',$order->title) }}" maxlength="255" required></div>
                <div class="col-md-4"><label for="priority" class="form-label">Priority</label><select class="form-select" name="priority" id="priority"><option value="normal" @selected(old('priority',$order->priority)==='normal')>Normal</option><option value="rush" @selected(old('priority',$order->priority)==='rush')>Rush</option></select></div>
                <div class="col-md-4"><label for="due_at" class="form-label">Due date</label><input class="form-control" type="datetime-local" name="due_at" id="due_at" value="{{ old('due_at',$order->due_at?->format('Y-m-d\TH:i')) }}"></div>
                <div class="col-md-4"><label for="assigned_user_id" class="form-label">Assigned employee</label><select class="form-select" name="assigned_user_id" id="assigned_user_id"><option value="">Unassigned</option>
                    @foreach($employees as $employee)<option value="{{ $employee->id }}" @selected((string)old('assigned_user_id',$order->assigned_user_id)===(string)$employee->id)>{{ $employee->name }}</option>@endforeach
                </select></div>
                <div class="col-12"><label for="description" class="form-label">Work instructions</label><textarea class="form-control" name="description" id="description" maxlength="4000" rows="5">{{ old('description',$order->description) }}</textarea></div>
                <div class="col-12"><button class="btn btn-primary">Save changes</button></div>
            </form>
            <div class="mt-4 border-top pt-3">
                <h3 class="h6">Change workflow stage</h3>
                <label class="form-label" for="order-status">Current stage</label>
                <select class="form-select" id="order-status" data-move-select data-current="{{ $order->workflow_column_id }}" data-move-url="{{ route('shop.orders.move', $order) }}">
                    @foreach($columns as $column)<option value="{{ $column->id }}" @selected($order->workflow_column_id===$column->id)>{{ $column->name }}</option>@endforeach
                </select>
            </div>
        </div></div>
    </div>
    <div class="col-lg-4">
<div class="card mb-4"><div class="card-body p-4">
    <h2 class="h5">Customer profile & shared fitting</h2>
    @if($order->customerProfile)
        <a class="fw-semibold d-block mb-2" href="{{ route('shop.customers.show', $order->customerProfile) }}">{{ $order->customerProfile->name }}</a>
        @if($fitting)
            <p class="small text-secondary mb-1">Latest baseline fitting: revision {{ $fitting->revision }}</p>
            <div class="small mb-2">Spans {{ $fitting->middle_span ?? '—' }} / {{ $fitting->ring_span ?? '—' }} · Thumb {{ $fitting->thumb_size ?? '—' }}</div>
            <a href="{{ route('shop.customers.fittings.show',[$order->customerProfile,$fitting]) }}" class="btn btn-outline-primary btn-sm">Review baseline fitting</a>
        @else
            <p class="small text-secondary">No fitting recorded for this customer yet.</p>
        @endif
    @else
        <p class="small text-secondary">Link this legacy order to the appropriate customer profile after confirming identity.</p>
        <form method="POST" action="{{ route('shop.orders.customer.update',$order) }}">
            @csrf @method('PUT')
            <label class="form-label" for="order-customer-id">Customer profile</label>
            <select name="customer_id" id="order-customer-id" required class="form-select mb-2"><option value="">Choose customer...</option>
            @foreach($customers as $profile)<option value="{{ $profile->id }}">{{ $profile->name }}</option>@endforeach
            </select>
            <button class="btn btn-primary btn-sm">Link profile</button>
        </form>
    @endif
</div></div>
<div class="card mb-4"><div class="card-body p-4">
    <h2 class="h5"><i class="fa-solid fa-bowling-ball text-primary me-2"></i>Customer bowling ball</h2>
    @if($order->ball)
        <div class="border rounded p-3 mb-3">
            <div class="d-flex justify-content-between flex-wrap gap-2 align-items-center">
                <div><strong>{{ $order->ball->ball_name }}</strong>
                    <div class="small text-secondary">{{ $order->ball->customer_name }} @if($order->ball->weight_lbs) · {{ $order->ball->weight_lbs }} lb @endif</div>
                </div>
                <a href="{{ route('shop.balls.show', $order->ball) }}" class="btn btn-outline-primary btn-sm">Full ball history</a>
            </div>
            <form method="POST" action="{{ route('shop.orders.ball.unlink', $order) }}" class="mt-3">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm">Unlink ball from ticket</button>
            </form>
        </div>
        <h3 class="h6">Recorded drilling measurements</h3>
        @forelse($order->ball->specs as $spec)
            <div class="border-start border-primary border-3 ps-3 mb-3">
                <a href="{{ route('shop.balls.specs.show', [$order->ball, $spec]) }}" class="fw-semibold">Revision {{ $spec->revision }}</a>
                <div class="small text-secondary">{{ $spec->created_at->format('M j, Y') }} · {{ $spec->author?->name ?? 'Unknown' }} @if($spec->order_id === $order->id) · This order @endif</div>
                <div class="small">Spans: {{ $spec->middle_span ?? '—' }} / {{ $spec->ring_span ?? '—' }} · PAP: {{ $spec->pap ?? '—' }}</div>
            </div>
        @empty
            <p class="text-secondary small">No measurements recorded yet.</p>
        @endforelse
        <hr>
        <h3 class="h6">Create a drilling revision for this order</h3>
        <p class="text-secondary small">The latest measurements are copied into a new draft. Prior worksheets are never overwritten.</p>
        @php($ball = $order->ball)
        @php($source = $ball->specs->first())
        @include('shop.balls._revision_form')
    @else
        <p class="text-secondary small">Link a customer-owned ball to this ticket to access its previous drilling measurements.</p>
        @if($candidateBalls->isNotEmpty())
        <form method="POST" action="{{ route('shop.orders.ball.link', $order) }}" class="mb-4">
            @csrf
            <label class="form-label" for="customer_ball_id">Existing ball for this customer</label>
            <select id="customer_ball_id" name="customer_ball_id" class="form-select mb-2" required>
                @foreach($candidateBalls as $candidate)
                    <option value="{{ $candidate->id }}">{{ $candidate->ball_name }} @if($candidate->weight_lbs) · {{ $candidate->weight_lbs }} lb @endif</option>
                @endforeach
            </select>
            <button class="btn btn-primary">Link bowling ball</button>
        </form>
        @endif
        <h3 class="h6">Add a new ball for this customer</h3>
        <form method="POST" action="{{ route('shop.balls.store') }}" class="row g-2">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">
            <div class="col-12">@include('shop.balls._catalog_fields', ['formId'=>'order'])</div>
            <div class="col-sm-4"><label class="form-label" for="new-ball-weight">Weight (lb)</label>
                <input class="form-control" type="number" id="new-ball-weight" name="weight_lbs" min="6" max="16"></div>
            <div class="col-12 mt-3"><button class="btn btn-primary">Add and link ball</button></div>
        </form>
    @endif
</div></div>
<div class="card"><div class="card-body p-4">
        <h2 class="h5">Workflow history</h2>
        @forelse($history as $step)
            <div class="border-start border-primary border-3 ps-3 pb-3 mb-2">
                <div class="fw-semibold">{{ $step->destination_name ?? 'Previous stage' }}</div>
                <div class="small text-secondary">{{ $step->actor_name ?? 'System' }}</div>
                <div class="small text-secondary">{{ \Illuminate\Support\Carbon::parse($step->created_at)->format('M j, Y g:i A') }}</div>
            </div>
        @empty<p class="text-secondary">No recorded transitions.</p>
        @endforelse
    </div></div></div>
</div>
@endsection
