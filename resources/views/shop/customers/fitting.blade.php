@extends('layouts.shop')
@section('title', 'Fitting Revision '.$fitting->revision)
@section('heading', $customer->name.' · Fitting Revision '.$fitting->revision)
@section('shop-content')
<div class="d-flex justify-content-between mb-3">
    <a href="{{ route('shop.customers.show',$customer) }}"><i class="fa-solid fa-arrow-left me-1"></i>Customer profile</a>
    <button class="btn btn-outline-secondary" type="button" onclick="window.print()"><i class="fa-solid fa-print me-2"></i>Print</button>
</div>
<div class="card"><div class="card-body p-4">
    <h2 class="h5">Baseline hand fitting · Revision {{ $fitting->revision }}</h2>
    <p class="text-secondary">Customer: {{ $customer->name }} · Recorded {{ $fitting->created_at->format('M j, Y g:i A') }} by {{ $fitting->author?->name ?? 'Unknown' }}</p>
    <div class="alert alert-warning small">Recorded dimensions only. A qualified driller must confirm the measurements before using them.</div>
    <div class="row g-3">
        @foreach(['handedness'=>'Handedness','grip_style'=>'Grip style','middle_span'=>'Middle span','ring_span'=>'Ring span','middle_finger_size'=>'Middle hole / insert','ring_finger_size'=>'Ring hole / insert','thumb_size'=>'Thumb size','bridge'=>'Bridge','middle_pitch'=>'Middle pitch','ring_pitch'=>'Ring pitch','thumb_pitch'=>'Thumb pitch','pap'=>'PAP','middle_span_cut'=>'Middle cut','ring_span_cut'=>'Ring cut','middle_pitch_lateral'=>'Middle lateral pitch','ring_pitch_lateral'=>'Ring lateral pitch','thumb_pitch_lateral'=>'Thumb lateral pitch','thumb_pitch_reverse'=>'Thumb reverse pitch','thumb_oval_type'=>'Thumb oval type','thumb_start_bit'=>'Thumb start bit','thumb_oval_width'=>'Thumb oval width','thumb_oval_degree'=>'Thumb oval degree','thumb_insert_od'=>'Thumb insert OD'] as $key => $label)
            <div class="col-sm-6"><div class="border rounded p-3 h-100"><span class="small text-secondary d-block">{{ $label }}</span><strong>{{ $fitting->$key ?? 'Not recorded' }}</strong></div></div>
        @endforeach
        <div class="col-12"><div class="border rounded p-3"><span class="small text-secondary">Notes</span><p class="mb-0 text-break" style="white-space:pre-wrap;">{{ $fitting->notes ?? 'No notes.' }}</p></div></div>
    </div>
</div></div>
@endsection
