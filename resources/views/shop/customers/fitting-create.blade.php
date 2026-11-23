@extends('layouts.shop')
@section('title','Create fitting')
@section('heading',$customer->name.' · New fitting spec sheet')
@section('shop-content')
<a href="{{ route('shop.customers.show',$customer) }}" class="d-inline-block mb-3"><i class="fa-solid fa-arrow-left me-1"></i>Customer profile</a>
<div class="row g-4 fitting-workspace">
    <div class="col-xl-6">
        <div class="card"><div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <h2 class="h5 mb-0">Grip measurements & pitches</h2>
                <span class="badge text-bg-light">Revision {{ ($source?->revision ?? 0)+1 }}</span>
            </div>
            <p class="small text-secondary">Values are prefilled from the latest revision. Saving creates a new immutable snapshot.</p>
            @include('shop.customers._fitting_form')
        </div></div>
    </div>
    <div class="col-xl-6"><div class="sticky-xl-top" style="top:6rem;">
        @include('shop.customers._fitting_diagram')
    </div></div>
</div>
@endsection
