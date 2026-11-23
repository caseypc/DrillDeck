@extends('layouts.public')
@section('title', $shop->name)
@section('content')
<div class="row justify-content-center"><div class="col-lg-8">
    <p class="text-primary fw-semibold">Pro Shop</p>
    <h1 class="display-6 fw-bold">{{ $shop->name }}</h1>
    <p class="text-secondary">Request service and track your work order from your DrillDeck customer account.</p>
    @auth
    <div class="card mt-4"><div class="card-body p-4 p-lg-5">
        <h2 class="h5">Submit an order request</h2>
        <form method="POST" action="{{ route('portal.orders.store', $shop) }}">
            @csrf
            <div class="mb-3"><label for="title" class="form-label">Service requested</label><input id="title" name="title" maxlength="255" required class="form-control" placeholder="e.g. New ball drilling" value="{{ old('title') }}"></div>
            <div class="mb-3"><label for="description" class="form-label">Details</label><textarea id="description" name="description" rows="4" maxlength="4000" class="form-control">{{ old('description') }}</textarea></div>
            <button class="btn btn-primary w-100">Send request</button>
        </form>
    </div></div>
    @else
    <a class="btn btn-primary mt-3" href="{{ route('login') }}">Log in to request service</a>
    <a class="btn btn-outline-primary mt-3" href="{{ route('register') }}">Create customer account</a>
    @endauth
</div></div>
@endsection
