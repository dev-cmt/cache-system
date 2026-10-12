@extends('layouts.master')

@section('title')
    Home
@endsection

@section('body')
    @php
        $hotDealAll = collect($hot_deal ?? []);
        $half       = (int) ceil($hotDealAll->count() / 2);
        $hot_deal_1 = $hotDealAll->take($half);
        $hot_deal_2 = $hotDealAll->skip($half);
    @endphp

    <section>
        <div class="slider">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div id="home_slider" class="carousel slide" data-ride="carousel">
                            <ol class="carousel-indicators">
                                @foreach($sliders as $key => $item)
                                    <li data-target="#home_slider" data-slide-to="{{$key}}" @if($key==0) class="active" @endif></li>
                                @endforeach
                            </ol>
                            <div class="carousel-inner">
                                @foreach($sliders as $key => $item)
                                    <div class="carousel-item {{$key==0 ? 'active':''}}">
                                        <img src="{{$item->get_img ? asset($item->get_img->file_url) : asset('frontEnd/images/no_image.png')}}" class="d-block w-100" alt="">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if($hot_deal_1->count() > 0 || $hot_deal_2->count() > 0)
    <section>
        <div class="hot-deals">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="hot-deals-inner-wrapper">
                            <div class="row mb-3">
                                <div class="col-md-6 col-6">
                                    <div class="hot-deals-gif">
                                        <img src="{{asset('frontEnd/images/hot-deal-logo.gif')}}" alt="">
                                    </div>
                                </div>
                                <div class="col-md-6 col-6">
                                    <div class="all-hot-deals-btn text-right mt-2">
                                        <a href="{{route('all.hot.deals')}}">সকল হট ডিল <i class="fa fa-angle-right"></i></a>
                                    </div>
                                </div>
                            </div>

                            @if($hot_deal_1->count() > 0)
                            <div class="owl-carousel mb-3">
                                @foreach($hot_deal_1 as $item)
                                    @php
                                        $percentage = ($item->price > 0 && $item->sale_price > 0)
                                            ? round(100 - (($item->sale_price / $item->price) * 100))
                                            : 0;
                                    @endphp
                                    <div class="hot-deals-product">
                                        <a href="{{route('product.details',[$item->slug,$item->id])}}">
                                            <div class="discount">
                                                <div class="discount-wrapper">
                                                    <img src="{{asset('frontEnd/images/flash-deal-percentage.png')}}" alt="">
                                                    <span>{{$percentage}}%</span> <br>
                                                    <span>ছাড়</span>
                                                </div>
                                            </div>
                                            <p class="float_price">{{$web_settings->currency_sign}} {{$item->sale_price}}</p>
                                            <img src="{{$item->get_thumb ? asset($item->get_thumb->file_url) : asset('frontEnd/images/no_image.png')}}" alt="{{$item->name}}">
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                            @endif

                            @if($hot_deal_2->count() > 0)
                            <div class="owl-carousel">
                                @foreach($hot_deal_2 as $item)
                                    @php
                                        $percentage = ($item->price > 0 && $item->sale_price > 0)
                                            ? round(100 - (($item->sale_price / $item->price) * 100))
                                            : 0;
                                    @endphp
                                    <div class="hot-deals-product">
                                        <a href="{{route('product.details',[$item->slug,$item->id])}}">
                                            <div class="discount">
                                                <div class="discount-wrapper">
                                                    <img src="{{asset('frontEnd/images/flash-deal-percentage.png')}}" alt="">
                                                    <span>{{$percentage}}%</span> <br>
                                                    <span>ছাড়</span>
                                                </div>
                                            </div>
                                            <p class="float_price">{{$web_settings->currency_sign}} {{$item->sale_price}}</p>
                                            <img src="{{$item->get_thumb ? asset($item->get_thumb->file_url) : asset('frontEnd/images/no_image.png')}}" alt="{{$item->name}}">
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    <section>
        <div class="main-products-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="mb-3">প্রয়োজনীয় প্রোডাক্ট</h4>
                    </div>
                </div>
                <div class="row m-0">
                    @foreach($products as $item)
                        <div class="col-md-2 col-6 main-product">
                            <div class="main-product-inner-wrapper text-center">
                                <a href="{{route('product.details',[$item->slug,$item->id])}}">
                                    <img src="{{$item->get_thumb ? asset($item->get_thumb->file_url) : asset('frontEnd/images/no_image.png')}}" alt="{{$item->name}}">
                                </a>
                                @if($item->sale_price != 0)
                                    <p class="mb-0" style="text-decoration: line-through;color: #b8b8b8">{{$web_settings->currency_sign}} {{$item->price}}</p>
                                    <p class="font-weight-bold mb-0" style="color: #fca204">{{$web_settings->currency_sign}} {{$item->sale_price}}</p>
                                @else
                                    <p class="font-weight-bold mb-0" style="margin-top: 24px;color: #fca204">{{$web_settings->currency_sign}} {{$item->price}}</p>
                                @endif
                                <p class="mb-0 prod_name"><a href="{{route('product.details',[$item->slug,$item->id])}}">{{$item->name}}</a></p>
                                <form action="{{route('add.cart',$item->id)}}" method="post">
                                    @csrf
                                    <input type="hidden" name="qty" value="1">
                                    <input type="submit" class="btn btn-sm w-100 mb-2 order_now_btn" name="order_now" value="অর্ডার করুন">
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="row mt-md-4 mt-2">
                    <div class="col-12">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            $(".owl-carousel").owlCarousel({
                margin: 15, loop: true, dots: false, autoplay: true,
                autoplayTimeout: 6000, autoplayHoverPause: true, responsiveClass: true,
                responsive: {
                    0: { items: 3, nav: true },
                    600: { items: 3, nav: false },
                    1000: { items: 6, nav: true, loop: false }
                }
            });
            $('.owl-nav').remove();
        });
    </script>
@endsection
