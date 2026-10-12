<!doctype html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{config('app.name')}} - @yield('title')</title>

    {{--Google fonts--}}
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,300i,400,400i,600,600i,700,700i,800,800i&display=swap" rel="stylesheet">
    {{--Font Awesome--}}
    <link href="{{asset('frontEnd/plugins/font-awesome/font-awesome.css')}}" rel="stylesheet">
    {{--Bootstrap CSS--}}
    <link rel="stylesheet" href="{{asset('frontEnd/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('frontEnd/css/style.css')}}">
    {{--Owl-Carousel--}}
    <link rel="stylesheet" href="{{asset('frontEnd/plugins/owl-carousel/owl.carousel.min.css')}}">
    <link rel="stylesheet" href="{{asset('frontEnd/plugins/owl-carousel/owl.theme.default.min.css')}}">
    {{--toastr--}}
    <link rel="stylesheet" type="text/css" href="{{asset('/')}}backEnd/assets/vendor/toastr/toastr.min.css">

    <style>
        /* Global Lazy Loading Styles */
        img[loading="lazy"] {
            content-visibility: auto;
        }
    </style>

    @yield('style')

    <script>
        window.dataLayer = window.dataLayer || [];
    </script>

    <!-- Google Tag Manager (GTM) -->
    @if (isset($globalSetting['google_trackings']) && is_array($globalSetting['google_trackings']))
        @foreach ($globalSetting['google_trackings'] as $gtm)
            @if (!empty($gtm['tracking_id']) && isset($gtm['status']) && $gtm['status'] == 1)
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
                    (window, document, 'script', 'dataLayer', '{{ $gtm['tracking_id'] }}');
                </script>
            @endif
        @endforeach
    @endif

    <!-- Facebook Pixel -->
    @if (isset($globalSetting['fb_pixels']) && is_array($globalSetting['fb_pixels']))
        @foreach ($globalSetting['fb_pixels'] as $pixel)
            @if (!empty($pixel['pixel_id']) && isset($pixel['status']) && $pixel['status'] == 1)
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
                    fbq('init', '{{ $pixel['pixel_id'] }}');
                    fbq('track', 'PageView');
                </script>
                <noscript>
                    <img height="1" width="1" style="display:none"
                        src="https://www.facebook.com/tr?id={{ $pixel['pixel_id'] }}&ev=PageView&noscript=1" />
                </noscript>
            @endif
        @endforeach
    @endif

    @yield('tracking')
</head>
<body>

    <!-- Google Tag Manager Data Layer (noscript) -->
    @if (isset($globalSetting['google_trackings']) && is_array($globalSetting['google_trackings']))
        @foreach ($globalSetting['google_trackings'] as $gtm)
            @if (!empty($gtm['tracking_id']) && isset($gtm['status']) && $gtm['status'] == 1)
                <noscript>
                    <iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtm['tracking_id'] }}" height="0" width="0" style="display:none;visibility:hidden"></iframe>
                </noscript>
            @endif
        @endforeach
    @endif
    <!-- End Google Tag Manager (noscript) -->

    <div class="main-wrapper">
        @include('inc.header')

        @yield('body')

        @include('inc.footer')
        @include('inc.bottom-menu')
    </div>

    <script src="{{asset('frontEnd/js/jquery-3.3.1.min.js')}}"></script>
    <script src="{{asset('frontEnd/js/bootstrap.bundle.min.js')}}"></script>

    {{--Owl-Carousel--}}
    <script src="{{asset('frontEnd/plugins/owl-carousel/owl.carousel.min.js')}}"></script>

    {{--toastr--}}
    <script src="{{asset('/')}}backEnd/assets/vendor/toastr/toastr.min.js"></script>
    <script>
        @if(session()->has('success'))
            toastr.options = {
            "positionClass": "toast-bottom-left"
        };
        toastr.success("{{ session('success') }}");
        @endif
            @if(Session::has('error'))
            toastr.options = {
            "positionClass": "toast-bottom-left"
        };
        toastr.error("{{ session('error') }}");
        @endif
            @if(Session::has('info'))
            toastr.options = {
            "positionClass": "toast-bottom-left"
        };
        toastr.info("{{ session('info') }}");
        @endif
            @if(Session::has('warning'))
            toastr.options = {
            "positionClass": "toast-bottom-left"
        };
        toastr.warning("{{ session('warning') }}");
        @endif
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

    @yield('script')
</body>
</html>
