@extends('layouts.shop')
@section('title', 'Layout revision '.$spec->revision)
@section('heading', $ball->ball_name.' · Layout revision '.$spec->revision)
@section('shop-content')
<div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
    <a href="{{ route('shop.balls.show', $ball) }}">
        <i class="fa-solid fa-arrow-left me-1"></i>Drilling workspace
    </a>
    <button type="button" onclick="window.print()" class="btn btn-outline-secondary">
        <i class="fa-solid fa-print me-1"></i>Print worksheet
    </button>
</div>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card"><div class="card-body p-3 p-md-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h2 class="h5 mb-0">Drilling worksheet · Revision {{ $spec->revision }}</h2>
                <span class="badge text-bg-warning">Not approved for drilling</span>
            </div>
            <p class="small text-secondary mt-3">
                {{ $ball->customerProfile?->name ?? $ball->customer_name }}
                · {{ $ball->manufacturer }} {{ $ball->ball_name }}
                · {{ $spec->created_at->format('M j, Y') }}
                · {{ $spec->author?->name ?? 'Unknown' }}
            </p>
            @if($spec->fitting)
                <p><a href="{{ route('shop.customers.fittings.show', [$spec->fitting->customer_id, $spec->fitting]) }}">
                    Baseline customer fitting revision {{ $spec->fitting->revision }}
                </a></p>
            @endif
            <div class="row g-2">
                @foreach([
                    'drilling_angle'=>'Drilling angle',
                    'pin_to_pap'=>'Pin to PAP',
                    'val_angle'=>'VAL angle',
                    'layout'=>'Layout notation',
                    'surface_finish'=>'Surface finish',
                    'handedness'=>'Handedness',
                    'grip_style'=>'Grip style',
                    'middle_span'=>'Middle span',
                    'ring_span'=>'Ring span',
                    'thumb_size'=>'Thumb size',
                    'bridge'=>'Bridge',
                    'pap'=>'PAP',
                    'weight_hole_position'=>'Historical weight-hole position',
                    'weight_hole_size'=>'Historical weight-hole size',
                    'weight_hole_depth'=>'Historical weight-hole depth',
                ] as $field => $label)
                    <div class="col-sm-6">
                        <div class="border rounded p-3 h-100">
                            <span class="d-block text-secondary small">{{ $label }}</span>
                            <strong>{{ $spec->$field ?? '—' }}</strong>
                        </div>
                    </div>
                @endforeach
                <div class="col-12">
                    <div class="border rounded p-3">
                        <span class="d-block text-secondary small">Notes</span>
                        <p class="mb-0" style="white-space:pre-wrap">{{ $spec->notes ?? 'None' }}</p>
                    </div>
                </div>
            </div>
        </div></div>
    </div>
    <div class="col-lg-5">
        {{-- The original photo component remains independent of any speculative 3D renderer. --}}
        @include('shop.balls._photo')
    </div>
</div>
@endsection
