@extends('layouts.public')
@section('title', 'My orders')
@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div><p class="text-primary small fw-semibold mb-1">CUSTOMER PORTAL</p><h1 class="h2 mb-0">My orders</h1></div>
    @if(auth()->user()->shops()->exists())<a href="{{ route('shop.board') }}" class="btn btn-outline-primary">Staff dashboard</a>@endif
</div>
<div class="row g-4">
    <div class="col-lg-8">
        @forelse($orders as $order)
        <div class="card mb-3"><div class="card-body p-4">
            <div class="d-flex justify-content-between gap-2 align-items-center mb-2"><strong>Order #{{ $order->id }} · {{ $order->title }}</strong><span class="badge text-bg-primary text-capitalize">{{ str_replace('_',' ', $order->column->customer_status) }}</span></div>
            <div class="text-secondary small mb-2">{{ $order->shop->name }} · Updated {{ $order->updated_at->diffForHumans() }}</div>
            <p class="mb-0">{{ $order->description }}</p>
        </div></div>
        @empty <div class="alert alert-info">You have no orders yet. Choose a pro shop to request service.</div>
        @endforelse
        {{ $orders->links() }}
    </div>
    <div class="col-lg-4"><div class="card"><div class="card-body p-4">
        <h2 class="h5">Find your pro shop</h2>
        <p class="text-secondary small">Select a shop to request a service.</p>
        @forelse($shops as $shop)<a href="{{ route('public.shop',$shop) }}" class="d-flex justify-content-between align-items-center py-3 border-bottom text-decoration-none">{{ $shop->name }} <i class="fa-solid fa-arrow-right ms-1"></i></a>
        @empty <p>No shops have registered yet.</p>
        @endforelse
    </div></div></div>
</div>
@endsection
