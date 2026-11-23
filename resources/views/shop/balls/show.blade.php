@extends('layouts.shop')
@section('title', $ball->ball_name.' · Drilling workspace')
@section('heading', $ball->ball_name.' · Drilling workspace')

@section('shop-content')
@php($source = $ball->specs->first())
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <a class="text-decoration-none" href="{{ route('shop.balls.index') }}">
        <i class="fa-solid fa-arrow-left me-1"></i>Ball inventory
    </a>
    <div class="d-flex flex-wrap gap-2">
        @if($ball->customerProfile)
            <a class="btn btn-outline-secondary btn-sm"
                href="{{ route('shop.customers.show', $ball->customerProfile) }}">
                <i class="fa-solid fa-user me-1"></i>Customer profile
            </a>
        @endif
        <a class="btn btn-outline-secondary btn-sm" href="#drill-history">
            <i class="fa-solid fa-clock-rotate-left me-1"></i>Layout history
        </a>
    </div>
</div>

<div class="drilldeck-workspace-heading rounded-3 p-3 mb-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div>
            <div class="text-uppercase small opacity-75">Drilling workstation</div>
            <h2 class="h4 mb-1">{{ $ball->manufacturer ?: 'Manufacturer not recorded' }} · {{ $ball->ball_name }}</h2>
            <p class="mb-0 small opacity-75">
                {{ $ball->customerProfile?->name ?? $ball->customer_name }}
                @if($ball->weight_lbs) · {{ $ball->weight_lbs }} lb @endif
                @if($ball->serial_number) · Serial {{ $ball->serial_number }} @endif
            </p>
        </div>
        <span class="badge text-bg-light">Next layout revision {{ ($source?->revision ?? 0) + 1 }}</span>
    </div>
</div>

<div class="row g-3 drilldeck-workspace">
    {{-- Compact form inspector, inspired by the left pane of the supplied screenshots. --}}
    <div class="col-xl-5 col-xxl-4">
        <div class="drilldeck-workspace-editor">
            <section class="card mb-3">
                <div class="card-body p-3 p-md-4">
                    <h3 class="h5 text-primary mb-3">Ball info</h3>
                    <dl class="row small mb-0">
                        <dt class="col-5 text-secondary">Manufacturer</dt>
                        <dd class="col-7">{{ $ball->manufacturer ?: '—' }}</dd>
                        <dt class="col-5 text-secondary">Ball name</dt>
                        <dd class="col-7">{{ $ball->ball_name }}</dd>
                        <dt class="col-5 text-secondary">Weight</dt>
                        <dd class="col-7">{{ $ball->weight_lbs ? $ball->weight_lbs.' lb' : '—' }}</dd>
                        <dt class="col-5 text-secondary">Core type</dt>
                        <dd class="col-7">{{ $ball->core_type ?: '—' }}</dd>
                        <dt class="col-5 text-secondary">Coverstock</dt>
                        <dd class="col-7">{{ $ball->coverstock_type ?: '—' }}</dd>
                        <dt class="col-5 text-secondary">Surface</dt>
                        <dd class="col-7">{{ $ball->surface ?: '—' }}</dd>
                    </dl>
                    @unless($ball->customerProfile)
                        <hr>
                        <p class="small text-secondary">This ball is a legacy walk-in record. Associate it with a verified customer profile to use shared fittings.</p>
                        <form method="POST" action="{{ route('shop.balls.customer.update', $ball) }}">
                            @csrf @method('PUT')
                            <label class="form-label" for="associate-customer">Customer profile</label>
                            <select class="form-select mb-2" name="customer_id" id="associate-customer" required>
                                <option value="">Choose customer...</option>
                                @foreach($customers as $profile)
                                    <option value="{{ $profile->id }}">{{ $profile->name }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-outline-primary btn-sm">Associate customer</button>
                        </form>
                    @endunless
                </div>
            </section>

            <section class="card">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="h5 text-primary mb-0">Layout &amp; surface</h3>
                        <span class="badge text-bg-light">Draft</span>
                    </div>
                    @php($order = null)
                    @include('shop.balls._revision_form')
                </div>
            </section>
        </div>
    </div>

    {{-- Main media pane keeps the real photo component, with a separate static grip reference. --}}
    <div class="col-xl-7 col-xxl-8">
        <div class="drilldeck-workspace-media">
            @include('shop.balls._photo')
            <div class="mt-3">
                @include('shop.balls._grip_reference')
            </div>
        </div>
    </div>
</div>

<section class="card mt-4" id="drill-history">
    <div class="card-body p-3 p-md-4">
        <h3 class="h5">Drilling revision history</h3>
        <p class="small text-secondary">Each worksheet is retained as a separate, read-only revision.</p>
        <div class="row g-3">
            @forelse($ball->specs as $spec)
                <div class="col-md-6 col-xl-4">
                    <div class="border rounded p-3 h-100">
                        <strong>Revision {{ $spec->revision }}</strong>
                        <div class="small text-secondary">
                            {{ $spec->created_at->format('M j, Y g:i A') }}
                            · {{ $spec->author?->name ?? 'Unknown' }}
                        </div>
                        <div class="small mt-2">
                            Drill {{ $spec->drilling_angle ?? '—' }}°
                            · Pin/PAP {{ $spec->pin_to_pap ?? '—' }}
                            · VAL {{ $spec->val_angle ?? '—' }}°
                        </div>
                        @if($spec->fitting)
                            <div class="small mt-1">Fitting revision {{ $spec->fitting->revision }}</div>
                        @endif
                        <a href="{{ route('shop.balls.specs.show', [$ball, $spec]) }}"
                            class="btn btn-outline-primary btn-sm mt-3">Open worksheet</a>
                    </div>
                </div>
            @empty
                <p class="text-secondary">No drilling layouts recorded yet.</p>
            @endforelse
        </div>
        <h4 class="h6 mt-4">Linked work orders</h4>
        @forelse($ball->orders as $order)
            <a href="{{ route('shop.orders.show', $order) }}" class="d-inline-block me-3 mb-2">
                #{{ $order->id }} · {{ $order->title }}
            </a>
        @empty
            <p class="small text-secondary">No work orders linked to this ball.</p>
        @endforelse
    </div>
</section>
@endsection
