@extends('layouts.public')
@section('title', 'Reset password')
@section('content')
<div class="row justify-content-center"><div class="col-md-7 col-lg-5"><div class="card"><div class="card-body p-4">
    <h1 class="h4">Forgot your password?</h1>
    <p class="text-secondary">We will email you a reset link.</p>
    @if(session('status'))<p class="text-success">{{ session('status') }}</p>@endif
    <form action="{{ route('password.email') }}" method="POST">@csrf
        <label class="form-label" for="email">Email</label><input type="email" id="email" name="email" class="form-control mb-3" required value="{{ old('email') }}">
        <button class="btn btn-primary w-100">Email reset link</button>
    </form>
</div></div></div></div>
@endsection
