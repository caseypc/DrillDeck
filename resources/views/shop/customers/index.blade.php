@extends('layouts.shop')
@section('title', 'Customers')
@section('heading', $shop->name.' · Customers')
@section('shop-content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card dd-table-card"><div class="card-body p-3 p-md-4">
            <form method="GET" action="{{ route('shop.customers.index') }}" class="d-flex gap-2 mb-3">
                <label class="visually-hidden" for="customer-search">Find customers</label>
                <input class="form-control" id="customer-search" name="q" value="{{ $search }}" placeholder="Search name, email or phone">
                <button class="btn btn-outline-primary">Search</button>
            </form>
            <div class="table-responsive"><table class="table align-middle">
                <thead><tr><th>Name</th><th>Contact</th><th>Balls</th><th>Fittings</th><th></th></tr></thead>
                <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td><div class="d-flex gap-2 align-items-center"><span class="dd-person-icon" aria-hidden="true">{{ strtoupper(mb_substr($customer->name, 0, 1)) }}</span><strong>{{ $customer->name }}</strong></div></td>
                        <td class="small">{{ $customer->email ?? (\App\Services\PhoneNumber::display($customer->phone) ?: '—') }}</td>
                        <td>{{ $customer->balls_count }}</td>
                        <td>{{ $customer->fittings_count }}</td>
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ route('shop.customers.show',$customer) }}">Open</a></td>
                    </tr>
                @empty<tr><td colspan="5" class="text-secondary">No customers found.</td></tr>@endforelse
                </tbody>
            </table></div>
            {{ $customers->links() }}
        </div></div>
    </div>
    <div class="col-lg-4"><div class="card"><div class="card-body p-4">
        <h2 class="h5">New customer</h2>
        <form method="POST" action="{{ route('shop.customers.store') }}">@csrf
            <label class="form-label" for="name">Full name</label>
            <input class="form-control mb-3" list="existing-customers" id="name" name="name" maxlength="160" required value="{{ old('name') }}">
            <label class="form-label" for="email">Email (optional)</label>
            <input class="form-control mb-3" type="email" id="email" name="email" maxlength="255" value="{{ old('email') }}">
            <label class="form-label" for="phone">Phone (optional)</label>
            <input class="form-control mb-3" id="phone" name="phone" data-phone maxlength="40" autocomplete="tel-national" value="{{ old('phone') }}">
            <button class="btn btn-primary w-100">Create customer</button>
            <datalist id="existing-customers">@foreach($customers as $known)<option value="{{ $known->name }}"></option>@endforeach</datalist>
        </form>
    </div></div></div>
</div>
@endsection
