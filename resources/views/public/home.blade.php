@extends('layouts.public')
@section('title', 'Bowling pro shop workflow')
@section('content')
<section class="row align-items-center g-5 py-4 py-lg-5">
    <div class="col-lg-7">
        <span class="badge text-bg-primary mb-3">Built for the pro shop floor</span>
        <h1 class="display-4 fw-bold lh-sm">Every bowling order. <span class="text-primary">One clear workflow.</span></h1>
        <p class="lead text-secondary mt-3">DrillDeck makes it easy to track orders from drop-off to pickup, manage your drilling records, and keep customers in the loop.</p>
        <div class="d-flex gap-2 flex-wrap mt-4">
            <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Start a pro shop <i class="fa-solid fa-arrow-right ms-1"></i></a>
            <a href="{{ route('portal.index') }}" class="btn btn-outline-primary btn-lg">Track my order</a>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0"><div class="card-body p-4">
            <div class="d-flex align-items-center gap-3 mb-4"><span class="display-icon"><i class="fa-solid fa-table-columns"></i></span><div><strong>Work orders at a glance</strong><div class="small text-secondary">Received → Drilling → Ready</div></div></div>
            @foreach(['Received' => 'New ball fitting','Drilling' => 'Ball drilling','Ready' => 'Pickup waiting'] as $stage => $job)
                <div class="bg-light border rounded-3 p-3 mb-2"><div class="d-flex justify-content-between"><strong>{{ $job }}</strong><span class="badge text-bg-light">{{ $stage }}</span></div><div class="small text-secondary">Stay organized, even on your busiest days.</div></div>
            @endforeach
        </div></div>
    </div>
</section>
<section class="row g-3 mt-4">
    @foreach([['fa-list-check','Simple Kanban','Move work orders between stages on a tablet.'],['fa-bowling-ball','Drilling specs','Keep customer measurements with their equipment.'],['fa-mobile-screen-button','Customer tracking','Customers can request work and view progress.']] as [$icon,$title,$description])
        <div class="col-md-4"><div class="card h-100"><div class="card-body p-4"><i class="fa-solid {{ $icon }} text-primary fs-3 mb-3"></i><h2 class="h5">{{ $title }}</h2><p class="text-secondary mb-0">{{ $description }}</p></div></div></div>
    @endforeach
</section>
@endsection
