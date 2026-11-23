<!doctype html><html lang="en"><head>@include('layouts.partials.head')</head>
<body class="dd-staff">
    <div class="dd-shell">
        <aside id="shop-sidebar" class="dd-sidebar offcanvas-lg offcanvas-start" tabindex="-1" aria-label="Shop sidebar">@include('layouts.partials.sidebar')</aside>
        <div class="dd-main-column">
            @include('layouts.partials.topbar')
            <main id="main-content" class="dd-content">
                @include('layouts.partials.alerts')
                <div class="dd-content-header">
                    <div><div class="dd-section-eyebrow mb-2">Shop workspace / {{ $shop->name }}</div><h1 class="h3 fw-bold mb-0">@yield('heading', 'Overview')</h1></div>
                    @hasSection('page-actions')<div>@yield('page-actions')</div>@endif
                </div>
                @yield('shop-content')
            </main>
            <footer class="dd-footer d-flex flex-wrap gap-2 justify-content-between"><span>© {{ date('Y') }} DrillDeck</span><span>Built for bowling professionals</span></footer>
        </div>
    </div>
    @stack('scripts')
</body></html>
