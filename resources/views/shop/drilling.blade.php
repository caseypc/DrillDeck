@extends('layouts.shop')
@section('title','Drilling workspace')
@section('heading',$shop->name.' · Drilling Station')
@section('shop-content')
<div class="card mb-4"><div class="card-body p-4">
    <h2 class="h5">Choose a customer bowling ball</h2>
    <p class="text-secondary">Drilling and fitting sheets now live with each physical ball and its customer profile. Select a ball below, or add one from inventory.</p>
    <a class="btn btn-primary" href="{{ route('shop.balls.index') }}">Open ball inventory</a>
    <a class="btn btn-outline-primary" href="{{ route('shop.customers.index') }}">Customer profiles & fittings</a>
</div></div>
<div class="row g-3">
    @forelse($balls as $ball)
        <div class="col-sm-6 col-xl-4"><div class="card h-100"><div class="card-body">
            <h3 class="h6">{{ $ball->manufacturer ? $ball->manufacturer.' · ' : '' }}{{ $ball->ball_name }}</h3>
            <div class="small text-secondary mb-2">{{ $ball->customer_name }}
                @if($ball->weight_lbs) · {{ $ball->weight_lbs }} lb @endif
                · {{ $ball->specs_count }} revisions
            </div>
            <a href="{{ route('shop.balls.show',$ball) }}" class="btn btn-sm btn-outline-primary">Open drilling workspace</a>
        </div></div></div>
    @empty<p class="text-secondary">No bowling balls recorded yet.</p>@endforelse
</div>
@endsection
