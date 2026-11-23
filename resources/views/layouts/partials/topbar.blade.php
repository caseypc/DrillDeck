<header class="dd-header sticky-top">
    <div class="d-flex align-items-center gap-3 px-3 px-xl-4 py-3">
        <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#shop-sidebar" aria-controls="shop-sidebar" aria-label="Open menu"><i class="fa-solid fa-bars"></i></button>
        <form method="GET" action="{{ route('shop.orders.index') }}" role="search" class="dd-top-search">
            <label for="top-search" class="visually-hidden">Search work orders</label>
            <div class="input-group"><span class="input-group-text"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></span>
                <input id="top-search" type="search" class="form-control" name="q" value="{{ request()->routeIs('shop.orders.index') ? request('q') : '' }}" placeholder="Search orders, customers, work..." autocomplete="off">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </div>
        </form>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="small text-body-secondary d-none d-xl-inline">{{ $shop->name }}</span>
            <div class="dropdown"><button class="btn btn-sm btn-outline-secondary dropdown-toggle d-inline-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false" type="button"><span class="dd-avatar">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><span class="d-none d-md-inline">{{ auth()->user()->name }}</span></button>
                <div class="dropdown-menu dropdown-menu-end p-2">
                    <div class="dropdown-header">{{ auth()->user()->email }}</div>
                    <a class="dropdown-item" href="{{ route('portal.index') }}">Customer portal</a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item" type="submit">Log out</button></form>
                </div>
            </div>
        </div>
    </div>
</header>
