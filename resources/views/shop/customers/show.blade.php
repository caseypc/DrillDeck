@extends('layouts.shop')
@section('title', $customer->name)
@section('heading', $customer->name.' · Customer Profile')
@section('shop-content')
<a href="{{ route('shop.customers.index') }}" class="d-inline-block mb-3"><i class="fa-solid fa-arrow-left me-1"></i>All customers</a>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card mb-4"><div class="card-body p-4">
            <h2 class="h5">Profile and contact information</h2>
            <form action="{{ route('shop.customers.update',$customer) }}" method="POST" class="row g-3">
                @csrf @method('PUT')
                <div class="col-sm-6"><label class="form-label" for="name">Name</label><input class="form-control" id="name" name="name" maxlength="160" required value="{{ old('name',$customer->name) }}"></div>
                <div class="col-sm-6"><label class="form-label" for="email">Email</label><input type="email" class="form-control" id="email" name="email" maxlength="255" value="{{ old('email',$customer->email) }}"></div>
                <div class="col-sm-6"><label class="form-label" for="phone">Phone</label><input class="form-control" id="phone" name="phone" maxlength="40" data-phone autocomplete="tel-national" value="{{ old('phone', \App\Services\PhoneNumber::display($customer->phone)) }}"></div>
                <div class="col-12"><label class="form-label" for="notes">Shop notes</label><textarea class="form-control" id="notes" name="notes" rows="3" maxlength="4000">{{ old('notes',$customer->notes) }}</textarea></div>
                <div class="col-12"><button class="btn btn-outline-primary">Save profile</button></div>
            </form>
        </div></div>
        <div class="card mb-4"><div class="card-body p-4">
            <h2 class="h5">Customer bowling-ball arsenal</h2>
            @forelse($customer->balls as $ball)
                <a class="d-flex justify-content-between gap-2 border-bottom py-3 text-decoration-none" href="{{ route('shop.balls.show',$ball) }}">
                    <span>{{ $ball->ball_name }} <small class="text-secondary">{{ $ball->weight_lbs ? $ball->weight_lbs.' lb' : '' }}</small></span>
                    <span class="badge text-bg-light">{{ $ball->specs_count }} drill revisions</span>
                </a>
            @empty<p class="text-secondary">No balls on this profile yet.</p>@endforelse
            <form method="POST" action="{{ route('shop.balls.store') }}" class="row g-2 mt-3">
                @csrf <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                <div class="col-12"><h3 class="h6">Add a bowling ball to this customer</h3>
                @include('shop.balls._catalog_fields', ['formId'=>'customer'])</div>
                <div class="col-sm-4"><label for="new-weight" class="form-label">Weight (lb)</label><input class="form-control" type="number" id="new-weight" name="weight_lbs" min="6" max="16"></div>
                <div class="col-12"><button class="btn btn-outline-primary">Add ball to arsenal</button></div>
            </form>
        </div></div>
        <h2 class="h5">Fitting measurement history</h2>
        @forelse($customer->fittings as $fitting)
            <div class="card mb-2"><div class="card-body p-3">
                <div class="d-flex justify-content-between">
                    <strong>Fitting revision {{ $fitting->revision }}</strong>
                    <small class="text-secondary">{{ $fitting->created_at->format('M j, Y') }}</small>
                </div>
                <div class="small text-secondary">{{ $fitting->author?->name ?? 'Unknown employee' }}</div>
                <div class="small my-2">Spans {{ $fitting->middle_span ?? '—' }} / {{ $fitting->ring_span ?? '—' }} · Thumb {{ $fitting->thumb_size ?? '—' }}</div>
                <a class="btn btn-sm btn-outline-primary" href="{{ route('shop.customers.fittings.show',[$customer,$fitting]) }}">Read fitting</a>
            </div></div>
        @empty<p class="text-secondary">No fitting history yet.</p>@endforelse
        <h2 class="h5 mt-4">Work orders</h2>
        @forelse($customer->orders as $order)
            <a href="{{ route('shop.orders.show',$order) }}" class="d-block border rounded p-3 mb-2 text-decoration-none">#{{ $order->id }} · {{ $order->title }}</a>
        @empty<p class="text-secondary">No linked orders.</p>@endforelse
    </div>
    <div class="col-lg-5"><div class="card"><div class="card-body p-4">
        <h2 class="h5">Hand-fitting workspace</h2>
        <p class="text-secondary small">Open the full-size fitting editor with span, pitch, insert and thumb oval sections and a live grip schematic.</p>
        <a class="btn btn-primary w-100" href="{{ route('shop.customers.fittings.create',$customer) }}">Create / clone fitting sheet</a>
    </div></div></div>
</div>
@endsection
