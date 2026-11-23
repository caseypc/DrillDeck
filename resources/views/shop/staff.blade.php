@extends('layouts.shop')
@section('title', 'Staff')
@section('heading', $shop->name.' · Staff')
@section('shop-content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card"><div class="card-body p-4">
            <h2 class="h5">Team members</h2>
            <div class="list-group list-group-flush dd-list-group">
            @foreach($employees as $employee)
                <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-3 align-items-center"><span class="dd-person-icon" aria-hidden="true">{{ strtoupper(mb_substr($employee->name, 0, 1)) }}</span><div><strong>{{ $employee->name }}</strong><div class="small text-secondary">{{ $employee->email }}</div></div></div>
                    <span class="badge text-bg-light text-capitalize">{{ $employee->pivot->role }}</span>
                </div>
            @endforeach
            </div>
        </div></div>
        <h2 class="h5 mt-4">Pending invitations</h2>
        @forelse($invitations as $invitation)
            <div class="card mb-2"><div class="card-body d-flex justify-content-between"><span>{{ $invitation->email }}</span><span class="small text-secondary">Expires {{ \Illuminate\Support\Carbon::parse($invitation->expires_at)->format('M j') }}</span></div></div>
        @empty<p class="text-secondary">No pending invitations.</p>@endforelse
    </div>
    <div class="col-lg-5"><div class="card"><div class="card-body p-4">
        <h2 class="h5">Invite an employee</h2>
        <p class="text-secondary small">We will email a one-time invitation. Employees must sign in using that email address to join.</p>
        <form method="POST" action="{{ route('shop.staff.invite') }}">@csrf
            <label class="form-label" for="email">Employee email</label>
            <input type="email" id="email" name="email" class="form-control mb-3" maxlength="255" required>
            <button class="btn btn-primary w-100">Send invitation</button>
        </form>
    </div></div></div>
</div>
@endsection
