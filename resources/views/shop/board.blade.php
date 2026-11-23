@extends('layouts.shop')
@section('title', 'Orders')
@section('heading', $shop->name.' · Work Orders')
@section('shop-content')
<div class="row g-3 mb-4">
    @foreach([
        ['Total work orders', $columns->sum(fn($column) => $column->orders->count()), 'fa-clipboard-list'],
        ['Rush orders', $columns->sum(fn($column) => $column->orders->where('priority', 'rush')->count()), 'fa-bolt'],
        ['Ready for pickup', optional($columns->firstWhere('name', 'Ready'))->orders?->count() ?? 0, 'fa-circle-check'],
        ['Shop customers', $customers->count(), 'fa-users'],
    ] as [$label, $value, $icon])
        <div class="col-sm-6 col-xl-3"><div class="card dd-stat-card h-100"><div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3"><span class="small text-body-secondary">{{ $label }}</span><span class="dd-stat-icon"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span></div>
            <div class="dd-stat-value">{{ number_format($value) }}</div>
        </div></div></div>
    @endforeach
</div>

<div class="row g-3 mb-4 align-items-end">
    <div class="col-lg"><p class="text-secondary mb-0">Drag tickets to advance work, or use the status menu on a phone or tablet.</p></div>
    <div class="col-lg-auto"><button class="btn btn-primary w-100" data-bs-toggle="collapse" data-bs-target="#new-order"><i class="fa-solid fa-plus me-1"></i> New work order</button></div>
</div>
<div id="new-order" class="collapse mb-4"><div class="card"><div class="card-body">
    <h2 class="h5">New work order</h2>
    <form method="POST" action="{{ route('shop.orders.store') }}" class="row g-3">
        @csrf
        <div class="col-md-6"><label class="form-label" for="customer_id">Customer profile (optional)</label><select class="form-select" name="customer_id" id="customer_id"><option value="">Walk-in / legacy</option>@foreach($customers as $profile)<option value="{{ $profile->id }}" @selected(old('customer_id') == $profile->id)>{{ $profile->name }}</option>@endforeach</select></div>
        <div class="col-md-6"><label for="customer_name" class="form-label">Customer name</label><input class="form-control" name="customer_name" list="known-customers" id="customer_name" maxlength="160" value="{{ old('customer_name') }}"></div>
        <datalist id="known-customers">@foreach($customers as $profile)<option value="{{ $profile->name }}"></option>@endforeach</datalist>
        <div class="col-md-6"><label for="title" class="form-label">Job / service</label><input class="form-control" name="title" id="title" required maxlength="255" placeholder="Ball fitting and drilling" value="{{ old('title') }}"></div>
        <div class="col-md-4"><label for="priority" class="form-label">Priority</label><select name="priority" id="priority" class="form-select"><option value="normal">Normal</option><option value="rush">Rush</option></select></div>
        <div class="col-md-4"><label for="due_at" class="form-label">Due date</label><input type="datetime-local" class="form-control" name="due_at" id="due_at" value="{{ old('due_at') }}"></div>
        <div class="col-md-4"><label for="assigned_user_id" class="form-label">Assign employee</label><select class="form-select" name="assigned_user_id" id="assigned_user_id"><option value="">Unassigned</option>@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->name }}</option>@endforeach</select></div>
        <div class="col-12"><label for="description" class="form-label">Instructions</label><textarea name="description" id="description" rows="2" maxlength="4000" class="form-control">{{ old('description') }}</textarea></div>
        <div class="col-12"><button class="btn btn-primary">Create ticket</button></div>
    </form>
</div></div></div>
<div class="d-flex justify-content-between align-items-center gap-2 mb-3"><h2 class="dd-panel-title">Work order board</h2><span class="small text-body-secondary">Drag tickets to update their status</span></div>
<div class="kanban-scroller" data-kanban-board>
    <div class="kanban-track">
        @foreach($columns as $column)
            <section class="kanban-column" aria-label="{{ $column->name }}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h6 fw-bold mb-0">{{ $column->name }}</h2>
                    <span class="badge text-bg-light">{{ $column->orders->count() }}</span>
                </div>
                <div class="kanban-list d-grid gap-2" data-column-list="{{ $column->id }}">
                    @foreach($column->orders as $order)
                        <article class="card order-ticket" data-order-id="{{ $order->id }}" data-move-url="{{ route('shop.orders.move', $order) }}">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small text-secondary ticket-handle" title="Drag ticket"><i class="fa-solid fa-grip-vertical me-1"></i>#{{ $order->id }}</span>
                                    @if($order->priority === 'rush')<span class="badge text-bg-warning">Rush</span>@endif
                                </div>
                                <a href="{{ route('shop.orders.show', $order) }}" class="fw-semibold text-decoration-none">{{ $order->customerProfile?->name ?? $order->customer_name }}</a>
                                <div class="small text-secondary mb-2">{{ $order->title }}</div>
                                @if($order->assignee)<div class="small mb-2"><i class="fa-regular fa-user me-1"></i>{{ $order->assignee->name }}</div>@endif
                                @if($order->due_at)<div class="small mb-2"><i class="fa-regular fa-clock me-1"></i>{{ $order->due_at->format('M j, g:i A') }}</div>@endif
                                <label class="visually-hidden" for="move-{{ $order->id }}">Change order status</label>
                                <select id="move-{{ $order->id }}" class="form-select form-select-sm" data-move-select data-move-url="{{ route('shop.orders.move', $order) }}" data-current="{{ $column->id }}">
                                    @foreach($columns as $option)
                                        <option value="{{ $option->id }}" @selected($option->id === $column->id)>{{ $option->name }}</option>
                                    @endforeach
                                </select>
                                <a class="btn btn-sm btn-outline-secondary mt-2 w-100" href="{{ route('shop.orders.show', $order) }}">Edit ticket <i class="fa-solid fa-arrow-right ms-1"></i></a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</div>
@endsection
