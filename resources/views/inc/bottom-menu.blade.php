<style>
    .mobile-bottom-menu {
        position: fixed !important;
        bottom: 0;
        left: 0;
        width: 100%;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.08);
        z-index: 9999;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        position: relative;
        /* IMPORTANT */
        height: 60px;
    }

    /* Each normal button gets equal width */
    .mobile-bottom-menu .menu-btn {
        width: 20%;
        text-align: center;
        background: none;
        border: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 500;
        color: #777;
        position: relative;
        transition: 0.3s;
    }

    /* ICON */
    .mobile-bottom-menu .menu-btn i {
        font-size: 16px;
        margin-bottom: 3px;
    }

    /* HOME CENTER */
    .mobile-bottom-menu .home-btn {
        width: 55px;
        height: 55px;
        background: #40b1b2;
        border-radius: 50%;
        color: #fff !important;
        box-shadow: 0 8px 25px rgba(35, 37, 111, 0.08);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: -40px;
    }

    /* Hide home text */
    .mobile-bottom-menu .home-btn span {
        display: none;
    }

    .mobile-bottom-menu .cart-shop {
        position: relative;
    }

    .mobile-bottom-menu .cart-shop .cart-badge {
        position: absolute;
        top: -15px;
        right: 18px;
        width: 20px;
        height: 20px;
        background: #40b1b2;
        border-radius: 50%;
        color: #fff;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* MOBILE ONLY */
    @media (min-width: 768px) {
        .mobile-bottom-menu {
            display: none;
        }
    }

    /* Default */
    .menu-btn {
        color: #777;
        transition: all 0.3s ease;
    }

    /* Active / Soft Active */
    .menu-btn.active {
        color: #40b1b2;
    }

    .menu-btn.active i {
        color: #40b1b2;
    }

    .menu-btn.active span {
        color: #40b1b2;
    }

    .mobile-bottom-menu .home-btn.active i {
        color: #fff;
    }
</style>

@php
    // Use the API-provided $categories (injected via AppServiceProvider) instead of a DB query.
    // The API data doesn't have is_mobile_menu, so we just take the first 4 active categories.
    $mobileCategories = ($categories ?? collect([]))
        ->filter(fn($c) => (bool)($c->status ?? true))
        ->values()
        ->take(4);

    $firstCat  = $mobileCategories->get(0);
    $secondCat = $mobileCategories->get(1);
    $thirdCat  = $mobileCategories->get(2);
    $fourthCat = $mobileCategories->get(3);
@endphp

<div class="mobile-bottom-menu">

    <!-- CATEGORY -->
    @if($firstCat)
    <a href="{{ route('category.show', $firstCat->id) }}" class="menu-btn text-decoration-none">
        {{-- <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-adjustments-horizontal"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 6a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M4 6l8 0" /><path d="M16 6l4 0" /><path d="M6 12a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M4 12l2 0" /><path d="M10 12l10 0" /><path d="M15 18a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /><path d="M4 18l11 0" /><path d="M19 18l1 0" /></svg> --}}
        <span>{{ $firstCat->category_name }}</span>
    </a>
    @endif

    <!-- CATEGORY -->
    @if($secondCat)
    <a href="{{ route('category.show', $secondCat->id) }}" class="menu-btn text-decoration-none">
        {{-- <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-map-pin"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18.364 4.636a9 9 0 0 1 .203 12.519l-.203 .21l-4.243 4.242a3 3 0 0 1 -4.097 .135l-.144 -.135l-4.244 -4.243a9 9 0 0 1 12.728 -12.728zm-6.364 3.364a3 3 0 1 0 0 6a3 3 0 0 0 0 -6" /></svg> --}}
        <span>{{ $secondCat->category_name }}</span>
    </a>
    @endif

    <!-- HOME -->
    <a href="{{route('home')}}" class="menu-btn home-btn {{ request()->routeIs('home') ? 'active' : '' }} text-decoration-none">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-home-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M10 12h4v4h-4l0 -4" /></svg>
    </a>

    <!-- CATEGORY -->
    @if($thirdCat)
    <a href="{{ route('category.show', $thirdCat->id) }}" class="menu-btn text-decoration-none">
        {{-- <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-phone-ringing"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 4l-2 2" /><path d="M22 10.5l-2.5 -.5" /><path d="M13.5 2l.5 2.5" /><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2c-8.072 -.49 -14.51 -6.928 -15 -15a2 2 0 0 1 2 -2" /></svg> --}}
        <span>{{ $thirdCat->category_name }}</span>
    </a>
    @endif

    <!-- CATEGORY -->
    @if($fourthCat)
    <a href="{{ route('category.show', $fourthCat->id) }}" class="menu-btn text-decoration-none">
        {{-- <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-shopping-cart-share"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 19a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M12.5 17h-6.5v-14h-2" /><path d="M6 5l14 1l-1 7h-13" /><path d="M16 22l5 -5" /><path d="M21 21.5v-4.5h-4.5" /></svg> --}}
        <span>{{ $fourthCat->category_name }}</span>

        {{-- <span class="cart-badge">
            @if (Cart::getContent()->count() > 0)
                {{ Cart::getContent()->count() }}
            @else
                0
            @endif
        </span> --}}
    </a>
    @endif

</div>
