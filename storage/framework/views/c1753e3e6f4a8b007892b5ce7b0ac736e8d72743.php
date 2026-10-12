<!doctype html>
<html lang="en">
<head>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo e(config('app.name')); ?> - <?php echo $__env->yieldContent('title'); ?></title>

    
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,300i,400,400i,600,600i,700,700i,800,800i&display=swap" rel="stylesheet">
    
    <link href="<?php echo e(asset('frontEnd/plugins/font-awesome/font-awesome.css')); ?>" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo e(asset('frontEnd/css/bootstrap.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('frontEnd/css/style.css')); ?>">
    
    <link rel="stylesheet" href="<?php echo e(asset('frontEnd/plugins/owl-carousel/owl.carousel.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('frontEnd/plugins/owl-carousel/owl.theme.default.min.css')); ?>">
    
    <link rel="stylesheet" type="text/css" href="<?php echo e(asset('frontEnd/plugins/toastr/toastr.min.css')); ?>">

    <style>
        /* Global Lazy Loading Styles */
        img[loading="lazy"] {
            content-visibility: auto;
        }
    </style>

    <?php echo $__env->yieldContent('style'); ?>

    <script>
        window.dataLayer = window.dataLayer || [];
    </script>

    <!-- Google Tag Manager (GTM) -->
    <?php if(isset($globalSetting['google_trackings']) && is_array($globalSetting['google_trackings'])): ?>
        <?php $__currentLoopData = $globalSetting['google_trackings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gtm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(!empty($gtm['tracking_id']) && isset($gtm['status']) && $gtm['status'] == 1): ?>
                <script>
                    (function(w, d, s, l, i) {
                        w[l] = w[l] || [];
                        w[l].push({
                            'gtm.start': new Date().getTime(),
                            event: 'gtm.js'
                        });
                        var f = d.getElementsByTagName(s)[0],
                            j = d.createElement(s),
                            dl = l != 'dataLayer' ? '&l=' + l : '';
                        j.async = true;
                        j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
                        f.parentNode.insertBefore(j, f);
                    })
                    (window, document, 'script', 'dataLayer', '<?php echo e($gtm['tracking_id']); ?>');
                </script>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>

    <!-- Facebook Pixel -->
    <?php if(isset($globalSetting['fb_pixels']) && is_array($globalSetting['fb_pixels'])): ?>
        <?php $__currentLoopData = $globalSetting['fb_pixels']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pixel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(!empty($pixel['pixel_id']) && isset($pixel['status']) && $pixel['status'] == 1): ?>
                <script>
                    ! function(f, b, e, v, n, t, s) {
                        if (f.fbq) return;
                        n = f.fbq = function() {
                            n.callMethod ?
                                n.callMethod.apply(n, arguments) : n.queue.push(arguments)
                        };
                        if (!f._fbq) f._fbq = n;
                        n.push = n;
                        n.loaded = !0;
                        n.version = '2.0';
                        n.queue = [];
                        t = b.createElement(e);
                        t.async = !0;
                        t.src = v;
                        s = b.getElementsByTagName(e)[0];
                        s.parentNode.insertBefore(t, s)
                    }(window, document, 'script',
                        'https://connect.facebook.net/en_US/fbevents.js');
                    fbq('init', '<?php echo e($pixel['pixel_id']); ?>');
                    fbq('track', 'PageView');
                </script>
                <noscript>
                    <img height="1" width="1" style="display:none"
                        src="https://www.facebook.com/tr?id=<?php echo e($pixel['pixel_id']); ?>&ev=PageView&noscript=1" />
                </noscript>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>

    <?php echo $__env->yieldContent('tracking'); ?>
</head>
<body>

    <!-- Google Tag Manager Data Layer (noscript) -->
    <?php if(isset($globalSetting['google_trackings']) && is_array($globalSetting['google_trackings'])): ?>
        <?php $__currentLoopData = $globalSetting['google_trackings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gtm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(!empty($gtm['tracking_id']) && isset($gtm['status']) && $gtm['status'] == 1): ?>
                <noscript>
                    <iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo e($gtm['tracking_id']); ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe>
                </noscript>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php endif; ?>
    <!-- End Google Tag Manager (noscript) -->

    <div class="main-wrapper">
        <?php echo $__env->make('inc.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <?php echo $__env->yieldContent('body'); ?>

        <?php echo $__env->make('inc.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('inc.bottom-menu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>

    <script src="<?php echo e(asset('frontEnd/js/jquery-3.3.1.min.js')); ?>"></script>
    <script src="<?php echo e(asset('frontEnd/js/bootstrap.bundle.min.js')); ?>"></script>

    
    <script src="<?php echo e(asset('frontEnd/plugins/owl-carousel/owl.carousel.min.js')); ?>"></script>
    
    <script src="<?php echo e(asset('frontEnd/plugins/toastr/toastr.min.js')); ?>"></script>
    <script>
        <?php if(session()->has('success')): ?>
            toastr.options = {
            "positionClass": "toast-bottom-left"
        };
        toastr.success("<?php echo e(session('success')); ?>");
        <?php endif; ?>
            <?php if(Session::has('error')): ?>
            toastr.options = {
            "positionClass": "toast-bottom-left"
        };
        toastr.error("<?php echo e(session('error')); ?>");
        <?php endif; ?>
            <?php if(Session::has('info')): ?>
            toastr.options = {
            "positionClass": "toast-bottom-left"
        };
        toastr.info("<?php echo e(session('info')); ?>");
        <?php endif; ?>
            <?php if(Session::has('warning')): ?>
            toastr.options = {
            "positionClass": "toast-bottom-left"
        };
        toastr.warning("<?php echo e(session('warning')); ?>");
        <?php endif; ?>
    </script>

    <script>
        $('#account-btn').on('click', function () {
            $('.login-float').toggle()
        });

        $('#header-top-menu-btn').on('click', function () {
            $('.header-top-menu-m').toggle()
        });

        $('#search_mobile_btn').on('click', function () {
            $('.search-form-m').toggle()
        });

        $('.search_btnclose').on('click', function () {
            $('.search-form-m').toggle()
        });

        $(document).ready(function () {
            // Open sidebar on mobile button click
            $('#cat_menu_mobile_btn').on('click', function (e) {
                e.stopPropagation(); // Prevent triggering document click
                $('.bs-canvas').addClass('show');
            });

            // Close sidebar when close button is clicked
            $('.bs-canvas-close').on('click', function () {
                $('.bs-canvas').removeClass('show');
            });

            // Close sidebar when clicking outside of it
            $(document).on('click', function (e) {
                if (!$(e.target).closest('.bs-canvas, #cat_menu_mobile_btn').length) {
                    $('.bs-canvas').removeClass('show');
                }
            });

            // Optional: prevent sidebar clicks from closing it
            $('.bs-canvas').on('click', function (e) {
                e.stopPropagation();
            });
        });


    </script>

    <?php echo $__env->yieldContent('script'); ?>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\cache-system\resources\views/layouts/master.blade.php ENDPATH**/ ?>