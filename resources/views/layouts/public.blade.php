<!doctype html><html lang="en"><head>@include('layouts.partials.head')</head>
<body class="d-flex flex-column min-vh-100 dd-public">
    <header class="dd-public-nav sticky-top"><nav class="navbar navbar-expand-lg"><div class="container py-2">
        @include('layouts.partials.brand')
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#dd-public-nav" aria-controls="dd-public-nav" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="dd-public-nav"><div class="navbar-nav ms-lg-auto align-items-lg-center gap-lg-2">
            <a class="nav-link" href="{{ route('home') }}">Home</a>
            @auth
                <a class="nav-link" href="{{ route('portal.index') }}">My orders</a>
                @if(auth()->user()->shops()->exists())<a class="btn btn-outline-primary btn-sm" href="{{ route('shop.board') }}">Shop workspace</a>@endif
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-primary btn-sm">Log out</button></form>
            @else
                <a class="nav-link" href="{{ route('login') }}">Log in</a><a class="btn btn-primary btn-sm" href="{{ route('register') }}">Get started</a>
            @endauth
        </div></div>
    </div></nav></header>
    <main id="main-content" class="container dd-public-main flex-grow-1 py-4 py-lg-5">@include('layouts.partials.alerts')@yield('content')</main>
    <footer class="dd-footer"><div class="container d-flex flex-wrap justify-content-between gap-2"><span>© {{ date('Y') }} DrillDeck</span><span>Better shop work, every day.</span></div></footer>
    @stack('scripts')
</body></html>
