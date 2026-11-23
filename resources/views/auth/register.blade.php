@extends('layouts.public')
@section('title', 'Register')
@section('content')
<div class="row justify-content-center"><div class="col-md-8 col-lg-6"><div class="card"><div class="card-body p-4 p-md-5">
    <h1 class="h3 mb-2">Create your DrillDeck account</h1>
    <p class="text-secondary">Customers can leave the shop name blank. Pro shop owners enter their business name to start their workspace.</p>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-3"><label for="name" class="form-label">Your name</label><input class="form-control" id="name" name="name" maxlength="255" value="{{ old('name') }}" required autocomplete="name"></div>
        <div class="mb-3"><label for="email" class="form-label">Email</label><input type="email" class="form-control" id="email" name="email" maxlength="255" value="{{ old('email') }}" required autocomplete="username"></div>
        <div class="mb-3"><label for="shop_name" class="form-label">Pro shop name <span class="text-secondary">(owners only)</span></label><input class="form-control" id="shop_name" name="shop_name" maxlength="150" value="{{ old('shop_name') }}" placeholder="Leave blank for customer account"></div>
        <div class="mb-3"><label for="password" class="form-label">Password (12+ characters)</label><input type="password" class="form-control" id="password" name="password" minlength="12" required autocomplete="new-password"></div>
        <div class="mb-3"><label for="password_confirmation" class="form-label">Confirm password</label><input type="password" class="form-control" id="password_confirmation" name="password_confirmation" minlength="12" required autocomplete="new-password"></div>
        <button class="btn btn-primary w-100">Create account</button>
    </form>
    <p class="mt-3 mb-0 small">Already registered? <a href="{{ route('login') }}">Log in</a></p>
</div></div></div></div>
@endsection
