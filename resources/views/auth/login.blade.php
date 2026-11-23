@extends('layouts.public')
@section('title', 'Log in')
@section('content')
<div class="row justify-content-center"><div class="col-md-7 col-lg-5"><div class="card"><div class="card-body p-4 p-md-5">
    <h1 class="h3 mb-3">Welcome back</h1>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" name="email" required autofocus autocomplete="username" value="{{ old('email') }}"></div>
        <div class="mb-3"><label class="form-label" for="password">Password</label><input class="form-control" type="password" id="password" name="password" required autocomplete="current-password"></div>
        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="remember" id="remember"><label class="form-check-label" for="remember">Remember me</label></div>
        <button class="btn btn-primary w-100">Log in</button>
    </form>
    <div class="d-flex justify-content-between mt-3 small"><a href="{{ route('password.request') }}">Forgot password?</a><a href="{{ route('register') }}">Create account</a></div>
</div></div></div></div>
@endsection
