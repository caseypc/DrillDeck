<div class="dd-sidebar-inner">
    <div class="dd-sidebar-brand d-flex justify-content-between align-items-center">
        @include('layouts.partials.brand')
        <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#shop-sidebar" aria-label="Close navigation"></button>
    </div>
    <div class="dd-workspace"><span class="dd-workspace-icon"><i class="fa-solid fa-store"></i></span>
        <div class="min-w-0 flex-grow-1"><div class="small fw-semibold text-truncate">{{ $shop->name }}</div><div class="small text-body-secondary">Shop workspace</div></div>
        <i class="fa-solid fa-chevron-down small text-body-secondary" aria-hidden="true"></i>
    </div>
    <nav class="dd-sidebar-nav nav flex-column" aria-label="Shop navigation">
        <span class="dd-nav-label">MAIN MENU</span>
        <a class="nav-link @if(request()->routeIs('shop.board')) active @endif" href="{{ route('shop.board') }}" @if(request()->routeIs('shop.board')) aria-current="page" @endif><i class="fa-solid fa-table-columns"></i> Dashboard</a>
        <a class="nav-link @if(request()->routeIs('shop.orders.*')) active @endif" href="{{ route('shop.orders.index') }}"><i class="fa-solid fa-list-check"></i> Orders</a>
        <a class="nav-link @if(request()->routeIs('shop.customers.*')) active @endif" href="{{ route('shop.customers.index') }}"><i class="fa-solid fa-users"></i> Customers</a>
        <a class="nav-link @if(request()->routeIs('shop.balls.*')) active @endif" href="{{ route('shop.balls.index') }}"><i class="fa-solid fa-bowling-ball"></i> Customer balls</a>
        <a class="nav-link @if(request()->routeIs('shop.drilling*')) active @endif" href="{{ route('shop.drilling') }}"><i class="fa-solid fa-ruler-combined"></i> Drilling station</a>
        @if(auth()->user()->shops()->wherePivotIn('role', ['owner','manager'])->exists())
            <span class="dd-nav-label">TOOLS</span>
            <a class="nav-link @if(request()->routeIs('shop.workflow*')) active @endif" href="{{ route('shop.workflow') }}"><i class="fa-solid fa-diagram-project"></i> Workflow</a>
            <a class="nav-link @if(request()->routeIs('shop.catalog.*')) active @endif" href="{{ route('shop.catalog.index') }}"><i class="fa-solid fa-layer-group"></i> Catalog</a>
            <a class="nav-link @if(request()->routeIs('shop.staff.*')) active @endif" href="{{ route('shop.staff.index') }}"><i class="fa-solid fa-user-gear"></i> Staff</a>
        @endif
        <span class="dd-nav-label">RESOURCES</span>
        <a class="nav-link" href="{{ route('portal.index') }}"><i class="fa-solid fa-arrow-up-right-from-square"></i> Customer portal</a>
    </nav>
    <div class="mt-auto p-3 border-top">
        <div class="d-flex gap-2 align-items-center"><span class="dd-avatar">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <div class="min-w-0 flex-grow-1"><div class="small fw-semibold text-truncate">{{ auth()->user()->name }}</div><div class="small text-body-secondary text-truncate">{{ auth()->user()->email }}</div></div>
        </div>
    </div>
</div>
