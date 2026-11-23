@extends('layouts.shop')
@section('title','All orders')
@section('heading',$shop->name.' · Orders')
@section('shop-content')
<div class="card dd-table-card"><div class="card-body p-3 p-lg-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <form method="GET" action="{{ route('shop.orders.index') }}" class="d-flex gap-2 flex-grow-1">
            <label for="orders-q" class="visually-hidden">Search orders</label>
            <input type="search" class="form-control" id="orders-q" name="q" value="{{ $search }}" placeholder="Search orders, customers or instructions">
            <button class="btn btn-outline-primary">Search</button>
        </form>
        <a href="{{ route('shop.board') }}" class="btn btn-primary"><i class="fa-solid fa-table-columns me-1"></i>Dashboard board</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr><th>Order</th><th>Customer</th><th>Ball</th><th>Status</th><th>Assigned</th><th>Due</th><th></th></tr></thead>
            <tbody>
            @forelse($orders as $order)
                <tr>
                    <td><div class="fw-semibold">#{{ $order->id }} · {{ $order->title }}</div>
                    @if($order->priority==='rush')<span class="badge text-bg-warning">Rush</span>@endif</td>
                    <td>@if($order->customerProfile)<a href="{{ route('shop.customers.show',$order->customerProfile) }}">{{ $order->customerProfile->name }}</a>@else{{ $order->customer_name }}@endif</td>
                    <td>{{ $order->ball?->ball_name ?? '—' }}</td>
                    <td><span class="badge text-bg-light">{{ $order->column?->name }}</span></td>
                    <td>{{ $order->assignee?->name ?? 'Unassigned' }}</td>
                    <td>{{ $order->due_at?->format('M j, Y') ?? '—' }}</td>
                    <td><a href="{{ route('shop.orders.show',$order) }}" class="btn btn-outline-primary btn-sm">Open</a></td>
                </tr>
            @empty<tr><td colspan="7" class="text-secondary">No matching orders.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
    {{ $orders->links() }}
</div></div>
@endsection
