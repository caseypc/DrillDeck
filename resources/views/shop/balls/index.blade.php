@extends('layouts.shop')
@section('title', 'Customer balls')
@section('heading', $shop->name.' · Ball Inventory')
@section('shop-content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card"><div class="card-body p-3 p-md-4">
            <h2 class="h5">Customer-owned bowling balls</h2>
            <div class="table-responsive"><table class="table table-hover align-middle">
                <thead><tr><th>Customer</th><th>Ball</th><th>Weight</th><th>Revisions</th><th></th></tr></thead>
                <tbody>
                @forelse($balls as $ball)
                    <tr>
                        <td>{{ $ball->customerProfile?->name ?? $ball->customer_name }}</td>
                        <td>{{ $ball->ball_name }}</td>
                        <td>{{ $ball->weight_lbs ? $ball->weight_lbs.' lb' : '—' }}</td>
                        <td>{{ $ball->specs_count }}</td>
                        <td><a href="{{ route('shop.balls.show', $ball) }}" class="btn btn-sm btn-outline-primary">View history</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-secondary">No bowling balls yet.</td></tr>
                @endforelse
                </tbody>
            </table></div>
            {{ $balls->links() }}
        </div></div>
    </div>
    <div class="col-lg-4">
        <div class="card"><div class="card-body p-4">
            <h2 class="h5">Add a customer ball</h2>
            <p class="small text-secondary">Choose a customer profile to share its hand fitting across multiple balls. Legacy walk-ins can still use a name.</p>
            <label class="form-label" for="customer_id">Customer profile</label>
            <select class="form-select mb-3" id="customer_id" name="customer_id"><option value="">Legacy walk-in</option>@foreach($customers as $profile)<option value="{{ $profile->id }}">{{ $profile->name }}</option>@endforeach</select>
            <form action="{{ route('shop.balls.store') }}" method="POST">@csrf
                <label class="form-label" for="customer_name">Walk-in customer name</label>
                <input class="form-control mb-3" name="customer_name" id="customer_name" maxlength="160">
                @include('shop.balls._catalog_fields', ['formId'=>'inventory'])
                <label class="form-label" for="weight_lbs">Weight (lb)</label>
                <input class="form-control mb-3" type="number" id="weight_lbs" name="weight_lbs" min="6" max="16">
                <button class="btn btn-primary w-100">Add to inventory</button>
            </form>
        </div></div>
    </div>
</div>
@endsection
