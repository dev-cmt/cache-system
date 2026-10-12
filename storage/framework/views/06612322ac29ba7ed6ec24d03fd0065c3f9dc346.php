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

<?php
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
?>

<div class="mobile-bottom-menu">

    <!-- CATEGORY -->
    <?php if($firstCat): ?>
    <a href="<?php echo e(route('category.show', $firstCat->id)); ?>" class="menu-btn text-decoration-none">
        
        <span><?php echo e($firstCat->category_name); ?></span>
    </a>
    <?php endif; ?>

    <!-- CATEGORY -->
    <?php if($secondCat): ?>
    <a href="<?php echo e(route('category.show', $secondCat->id)); ?>" class="menu-btn text-decoration-none">
        
        <span><?php echo e($secondCat->category_name); ?></span>
    </a>
    <?php endif; ?>

    <!-- HOME -->
    <a href="<?php echo e(route('home')); ?>" class="menu-btn home-btn <?php echo e(request()->routeIs('home') ? 'active' : ''); ?> text-decoration-none">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-home-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0" /><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" /><path d="M10 12h4v4h-4l0 -4" /></svg>
    </a>

    <!-- CATEGORY -->
    <?php if($thirdCat): ?>
    <a href="<?php echo e(route('category.show', $thirdCat->id)); ?>" class="menu-btn text-decoration-none">
        
        <span><?php echo e($thirdCat->category_name); ?></span>
    </a>
    <?php endif; ?>

    <!-- CATEGORY -->
    <?php if($fourthCat): ?>
    <a href="<?php echo e(route('category.show', $fourthCat->id)); ?>" class="menu-btn text-decoration-none">
        
        <span><?php echo e($fourthCat->category_name); ?></span>

        
    </a>
    <?php endif; ?>

</div>
<?php /**PATH D:\xampp\htdocs\website2\resources\views/inc/bottom-menu.blade.php ENDPATH**/ ?>