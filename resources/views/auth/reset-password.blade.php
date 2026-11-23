@extends('layouts.public')
@section('title', 'Set new password')
@section('content')
<div class="row justify-content-center"><div class="col-md-7 col-lg-5"><div class="card"><div class="card-body p-4">
    <h1 class="h4">Set a new password</h1>
    <form action="{{ route('password.update') }}" method="POST">@csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div class="mb-3"><label class="form-label" for="email">Email</label><input type="email" class="form-control" id="email" name="email" required value="{{ old('email', $request->email) }}"></div>
        <div class="mb-3"><label class="form-label" for="password">New password</label><input type="password" id="password" name="password" class="form-control" minlength="12" required></div>
        <div class="mb-3"><label class="form-label" for="password_confirmation">Confirm password</label><input type="password" id="password_confirmation" name="password_confirmation" class="form-control" minlength="12" required></div>
        <button class="btn btn-primary w-100">Reset password</button>
    </form>
</div></div></div></div>
@endsection
