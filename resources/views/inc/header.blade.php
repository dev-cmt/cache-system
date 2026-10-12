@php
    // $categories is injected globally from AppServiceProvider via AppApiService.
    // Do NOT re-query here — it would override API data with a DB call.
    $categories = $categories ?? collect([]);
@endphp
@if ($web_settings->marquee_text ?? null)
    <div class="bg-warning">
        <marquee behavior="scroll" direction="left">
            {{ $web_settings->marquee_text ?? "" }}
        </marquee>
    </div>
@endif
<header>
    @if(!request()->is('product*'))
        <div class="header-top">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 col-7">
                        <div class="header-left">
                            <p>Welcome to {{config('app.name')}}</p>
                        </div>
                    </div>

                    <div class="col-md-8 col-5">
                        <div class="header-right">
                            <ul>
                                <li>
                                    <i class="fa fa-home"></i>
                                    <a href="{{route('home')}}">
                                        <span>Home</span>
                                    </a>
                                </li>
                                <li>
                                    <i class="fa fa-envelope"></i>
                                    <a href="">
                                        <span>Contact</span>
                                    </a>
                                </li>
                                <li>
                                    <i class="fa fa-search"></i>
                                    <a href="">
                                        <span>Track Order</span>
                                    </a>
                                </li>
                                <li>
                                    <i class="fa fa-shopping-cart"></i>
                                    <a href="">
                                        <span>Checkout</span>
                                    </a>
                                </li>
                                <li>
                                    <i class="fa fa-user"></i>
                                    <a href="">
                                        <span>Account</span>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        {{--for mobile--}}
                        <div class="header-right-m">
                            <ul>
                                <li class="position-relative">
                                    <i class="fa fa-user" id="account-btn"></i>
                                    <div class="login-float">
                                        <div class="card">
                                            <div class="card-header">
                                                <p>Login</p>
                                            </div>
                                            <div class="card-body">
                                                <form action="" method="post">
                                                    @csrf
                                                    <div class="form-group mb-2">
                                                        <input type="text" class="form-control" placeholder="Mobile Number">
                                                    </div>
                                                    <div class="form-group">
                                                        <input type="password" class="form-control" placeholder="Password">
                                                    </div>
                                                    <button type="submit" class="btn btn-info w-100 mb-3">Login</button>
                                                    <p>New Customer? <a href="">Signup</a></p>
                                                </form>
                                            </div>
                                        </div>

                                    </div>
                                </li>
                                <li class="position-relative">
                                    <i class="fa fa-bars" id="header-top-menu-btn"></i>
                                    <div class="header-top-menu-m">
                                        <ul>
                                            <li>
                                                <span class="fa fa-home"></span>
                                                <a href="{{route('home')}}">
                                                    <span>Home</span>
                                                </a>
                                            </li>
                                            <li>
                                                <span class="fa fa-envelope"></span>
                                                <a href="">
                                                    <span>Contact</span>
                                                </a>
                                            </li>
                                            <li>
                                                <span class="fa fa-search"></span>
                                                <a href="">
                                                    <span>Track Order</span>
                                                </a>
                                            </li>
                                            <li>
                                                <span class="fa fa-shopping-cart"></span>
                                                <a href="">
                                                    <span>Checkout</span>
                                                </a>
                                            </li>
                                            <li>
                                                <span class="fa fa-user"></span>
                                                <a href="">
                                                    <span>Account</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="header">
        <div class="container">
            <div class="row">
                <div class="col-4 d-md-none cat_menu_btn_m">
                    <ul>
                        <li>
                            <i class="fa fa-bars" id="cat_menu_mobile_btn"></i>
                        </li>
                        <li>
                            <i class="fa fa-search" id="search_mobile_btn"></i>
                        </li>
                    </ul>
                </div>
                <div class="col-md-3 col-7 logo-m">
                    <div class="logo">
                        <a href="{{route('home')}}"><img src="{{$web_settings->get_logo ? asset($web_settings->get_logo->file_url) : asset('frontEnd/images/no_image.png')}}" alt=""></a>
                    </div>
                </div>

                <div class="col-md-6 py-md-3 d-none d-md-block">
                    <div class="search">
                        <form action="{{route('search')}}" method="get">
                            <select name="category" id="category" class="search-select">
                                <option value="">ক্যাটেগরীজ</option>
                                @foreach($categories as $cat)
                                    <option value="{{$cat->id}}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{$cat->category_name}}</option>
                                @endforeach
                            </select>
                            <input type="text" name="query" class="search-input" placeholder="সার্চ করুন" value="{{ request('query', '') }}">
                            <button type="submit" class="search-btn"></button>
                        </form>
                    </div>
                </div>

                <div class="col-md-3 text-md-right text-center py-3 cart-m">
                    <span class="cart-number d-none d-md-inline-block"><i class="fa fa-phone"></i> {{$web_settings->website_phone ?? null}}</span>
                    <div class="cart d-inline-block position-relative">
                        @if(Cart::getContent()->count() > 0)
                            <span class="badge badge-danger rounded-circle">{{Cart::getContent()->count()}}</span>
                        @endif
                        <a href="{{route('checkout')}}"><i style="color: #fdc525" class="fa fa-2x fa-shopping-cart"></i></a>
                    </div>
                </div>
            </div>
        </div>


        <!--Mobile Menu-->
        <div class="bs-canvas bs-canvas-left position-fixed bg-light h-100 ml-0">
            <header class="bs-canvas-header d-flex justify-content-between align-items-center p-3 bg-dark">
                <h4 class="mb-0 text-white">Categories</h4>
                <button type="button" class="bs-canvas-close close text-white" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </header>

            <div class="bs-canvas-content p-3">
                <div class="cat_menu_m">
                    <ul class="list-unstyled mb-0">

                        {{-- Home link --}}
                        <li class="border-bottom">
                            <a href="{{ route('home') }}" class="text-dark font-weight-bold">Home</a>
                        </li>

                        {{-- Dynamic categories --}}
                        @foreach($categories->whereNull('parent_id') as $cat)
                            <li class="border-bottom">
                                <a href="{{ route('category.show', $cat->id) }}" class="text-dark font-weight-bold d-block">
                                    {{ $cat->category_name }}
                                </a>

                                {{-- Child categories: filter flat $categories by parent_id --}}
                                @php $subItems = $categories->where('parent_id', $cat->id); @endphp
                                @if($subItems->isNotEmpty())
                                    <ul class="list-unstyled pl-3">
                                        @foreach($subItems as $sub)
                                            <li class="d-flex align-items-center mb-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                    fill="none" stroke-linecap="round" stroke-linejoin="round"
                                                    class="mr-1 text-secondary">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M9 6l6 6l-6 6"/>
                                                </svg>
                                                <a href="{{ route('category.show', $sub->id) }}" class="text-muted">
                                                    {{ $sub->category_name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach

                    </ul>

                    {{-- <a href="{{ url('track-order') }}" class="btn btn-dark btn-sm w-100 mt-4">
                        Track Order
                    </a> --}}
                </div>
            </div>
        </div>


        <div class="search-form-m">
            <form action="{{route('search')}}" method="get">
                <select name="category" class="form-control search-cat-m mb-1">
                    <option value="">সব ক্যাটেগরি</option>
                    @foreach($categories->whereNull('parent_id') as $cat)
                        <option value="{{$cat->id}}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{$cat->category_name}}</option>
                    @endforeach
                </select>
                <input type="text" name="query" class="form-control" placeholder="সার্চ করুন" autocomplete="off" value="{{ request('query', '') }}">
                <button type="submit">
                    <i class="fa fa-search"></i>
                </button>
            </form>
            {{--<input type="text" name="q" id="searchMf" class="form-control" value="" placeholder="সার্চ করুন" autocomplete="off"><span
                role="status" aria-live="polite" class="ui-helper-hidden-accessible"></span>
            <button type="submit">
                <i class="fa fa-search"></i>
            </button>--}}
            <button class="search_btnclose">
                <i class="fa fa-times-circle"></i>
            </button>
        </div>
    </div>

    {{-- <div class="header-bottom d-md-block d-none">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="cat_menu">
                        <ul>
                            <li>
                                <a href="{{route('home')}}">Home</a>
                            </li>
                            @foreach($categories as $cat)
                                <li>
                                    <a href="{{route('category.show',$cat->id)}}">{{$cat->category_name}}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}


    <div class="header-bottom d-md-block d-none">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="cat_menu">
                        <ul class="menu">
                            <li>
                                <a href="{{ route('home') }}">Home</a>
                            </li>
                            @foreach($categories->where('parent_id', null) as $cat)
                                <li class="has-submenu">
                                    <a href="{{ route('category.show', $cat->id) }}">{{ $cat->category_name }}
                                        @if($categories->where('parent_id', $cat->id)->count() > 0)
                                            <i class="fa fa-caret-down submenu-icon"></i>
                                        @endif
                                    </a>
                                    {{-- Subcategories --}}
                                    @php
                                        $subcats = $categories->where('parent_id', $cat->id);
                                    @endphp
                                    @if($subcats->count() > 0)
                                        <ul class="submenu">
                                            @foreach($subcats as $sub)
                                                <li>
                                                    <a href="{{ route('category.show', $sub->id) }}">{{ $sub->category_name }}</a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .header-bottom{
            padding: 0;
        }
        .cat_menu ul.menu {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-flow: wrap;
        }

        .cat_menu ul.menu li {
            position: relative;
        }

        .cat_menu ul.menu li a {
            text-decoration: none;
            color: #333;
            display: block;
        }

        .cat_menu ul.menu li .submenu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            list-style: none;
            background: #fff;
            min-width: 180px;
            z-index: 99;
        }

        .cat_menu ul.menu li .submenu li a {
            padding: 0px 8px;
        }
        .cat_menu ul.menu li.has-submenu:hover {
            background: #1b1b1b;
        }
        .cat_menu ul.menu li.has-submenu:hover > a{
            color: #e6e6e6;
        }

        .cat_menu ul.menu li:hover .submenu {
            display: flex;
            flex-direction: column;
        }
        .cat_menu ul.menu li .submenu li:hover {
            background: #1b1b1b;
        }
        .cat_menu ul.menu li .submenu li:hover a {
            color: #e6e6e6;
        }

        .cat_menu ul.menu li.has-submenu .submenu-icon {
            margin-left: 4px;
            font-size: 14px;
            transition: transform 0.3s ease;
        }
        /* Rotate icon when hovering */
        .cat_menu ul.menu li.has-submenu:hover .submenu-icon {
            transform: rotate(180deg);
        }


    </style>
</header>
