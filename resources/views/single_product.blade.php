@extends('layouts.master')

@section('title')
    {{$data->name}}
@endsection

@section('style')
    <style>
        .variant-options-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .custom-variant-item {
            display: inline-block;
            margin-right: 6px;
            margin-bottom: 6px;
        }
        .custom-variant-item .d-none {
            display: none;
        }
        .variant-option-btn {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border: 1.5px solid #d1d5db;
            border-radius: 6px;
            background: #ffffff;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            transition: all 0.2s ease-in-out;
            user-select: none;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .variant-option-btn:hover {
            border-color: #0088cc;
            color: #0088cc;
            transform: translateY(-1px);
        }
        .attr_checkbox:checked + .variant-option-btn {
            border-color: #0088cc;
            background-color: #f0f9ff;
            color: #0088cc;
            font-weight: 600;
            box-shadow: 0 0 0 1.5px #0088cc;
        }
        .variant-swatch-img {
            width: 24px;
            height: 24px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 6px;
            border: 1px solid rgba(0,0,0,0.1);
        }
    </style>
@endsection

@section('tracking')
    @if(session()->has('api_add_to_cart'))
        <script>
            dataLayer.push({ecommerce: null});
            dataLayer.push({
                event: "add_to_cart",
                ecommerce: {
                    currency: "BDT",
                    value: {{ session('api_add_to_cart_data')['value'] ?? 0 }},
                    items: {!! session('api_add_to_cart_data')['products'] ?? '[]' !!}
                }
            });
            if (typeof fbq === 'function') {
                var addToCartItems = {!! session('api_add_to_cart_data')['products'] ?? '[]' !!};
                fbq('track', 'AddToCart', {
                    content_ids: addToCartItems.map(function (item) { return item.item_id; }),
                    content_name: addToCartItems.length ? addToCartItems[0].item_name : '',
                    content_type: 'product',
                    value: {{ session('api_add_to_cart_data')['value'] ?? 0 }},
                    currency: 'BDT'
                });
            }
        </script>
    @endif
    <script>
        var viewItemItems = {!! session('api_view_item_data')['products'] ?? '[]' !!};
        dataLayer.push({ecommerce: null});
        dataLayer.push({
            event: "view_item",
            ecommerce: {
                currency: "BDT",
                value: {{ session('api_view_item_data')['value'] ?? 0 }},
                items: {!! session('api_view_item_data')['products'] ?? '[]' !!}
            }
        });
        if (typeof fbq === 'function') {
            fbq('track', 'ViewContent', {
                content_ids: viewItemItems.map(function (item) { return item.item_id; }),
                content_name: viewItemItems.length ? viewItemItems[0].item_name : '',
                content_type: 'product',
                value: {{ session('api_view_item_data')['value'] ?? 0 }},
                currency: 'BDT'
            });
        }
    </script>
@endsection

@section('body')
    <section>
        <div class="category_breadcrumb">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <p>
                            <a href="{{route('home')}}">Home</a>
                            /
                            <a href="javascript:void(0);">{{$data->name}}</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="products-details-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-4 col-12 mb-md-3 mb-2">
                        {{--@php
                            if ($data->gallery_images){
                                $photos = explode(',', $data->gallery_images);
                            }else{
                                $photos = [];
                            }
                        @endphp--}}
                        <div id="sing_prod_img_slider" class="carousel slide" data-ride="carousel">
                            @if(!empty($data->gallery_images) && is_array($data->gallery_images))
                            <ol class="carousel-indicators">
                                <li data-target="#sing_prod_img_slider" data-slide-to="0" class="active"></li>
                                @foreach($data->gallery_images as $key => $photo)
                                    <li data-target="#sing_prod_img_slider" data-slide-to="{{$key+1}}"></li>
                                @endforeach
                            </ol>
                        @endif
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="{{$data->get_image ? asset($data->get_image->file_url) : asset('frontEnd/images/no_image.png')}}" class="d-block w-100" alt="">
                                </div>
                                @if(!empty($data->gallery_images) && is_array($data->gallery_images))
                                    @foreach($data->gallery_images as $photo)
                                        <div class="carousel-item">
                                            <img src="{{ $photo ? asset($photo) : asset('frontEnd/images/no_image.png') }}" class="d-block w-100" alt="" loading="lazy">
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            @if(!empty($data->gallery_images) && is_array($data->gallery_images))
                                <button class="carousel-control-prev" type="button" data-target="#sing_prod_img_slider" data-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="sr-only">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-target="#sing_prod_img_slider" data-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="sr-only">Next</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-5 mb-2">
                        <h2 class="text-capitalize single_prod_title">{{$data->name}}</h2>
                        @php
                            $title_reviews = (isset($product_reviews) && count($product_reviews) > 0) ? $product_reviews : ($data->get_reviews ?? collect());
                            $title_review_count = $title_reviews->count();
                            $title_avg_rating = $title_review_count > 0 ? (float) $title_reviews->avg('rating') : 0;
                        @endphp
                        @if($title_review_count > 0)
                            <a href="#rev-pane" class="d-inline-flex align-items-center mb-2 text-decoration-none" id="top_review_link">
                                <span class="text-warning mr-2" style="font-size: 17px; letter-spacing: 1px;">
                                    @for($star = 1; $star <= 5; $star++)
                                        @if($title_avg_rating >= $star)
                                            <i class="fa fa-star"></i>
                                        @elseif($title_avg_rating > ($star - 1))
                                            <i class="fa fa-star-half-o"></i>
                                        @else
                                            <i class="fa fa-star-o text-muted" style="opacity: 0.4;"></i>
                                        @endif
                                    @endfor
                                </span>
                                <span class="text-dark font-weight-bold">({{ $title_review_count }} reviews)</span>
                            </a>
                        @endif
                        <h3 class="font-weight-bold single_prod_prices">
                            @if($data->sale_price > 0)
                                <span class="regular_price" style="text-decoration: line-through; color: #555;opacity: .5">{{$web_settings->currency_sign}} {{$data->price}}</span>
                                <span class="sale_price" style="color: #0088cc">{{$web_settings->currency_sign}} {{$data->sale_price}}</span>
                            @else
                                <span class="sale_price" style="color: #0088cc">{{$web_settings->currency_sign}} {{$data->price}}</span>
                            @endif

                            @if($data->sale_price>0)
                                <span class="save_taka badge badge-warning p-1" style="font-size: 11px">Save {{$data->price - $data->sale_price}}TK</span>
                            @endif
                        </h3>
                        <p class="sku_text"><span>প্রোডাক্ট কোড: </span> <span class="p-0 pr-1 single_prod_sku">{{$data->sku}}</span></p>
                        <h4 class="single_prod_in_stock">স্টক : <span class="text-success">ইন স্টক</span></h4>

                        {{--<div class="qty_div">
                            <a style="color: black" href="{{route('cart.item.minus',$data->id)}}"><i class="fa fa-minus" id="qty_minus"></i></a>
                            <input type="text" name="qty" id="qty" min="1" value="{{$qty ?? 1}}" readonly>
                            <a style="color: black" href="{{route('cart.item.plus',$data->id)}}"><i class="fa fa-plus" id="qty_plus"></i></a>
                        </div>--}}

                        <form action="{{route('add.cart',$data->id)}}" method="post">
                            @csrf
                            <input type="hidden" name="id" id="id" value="{{$data->id}}">
                            <div class="d-flex">
                                <div class="qty-text-div">
                                    <span>পরিমান : </span>
                                </div>
                                <div class="qty_div">
                                    <div class="minus-qty-div">
                                        <i class="fa fa-minus" id="qty_minus"></i>
                                    </div>
                                    <div class="qty-div">
                                        <input type="text" name="qty" id="qty" min="1" value="{{$qty ?? 1}}" readonly>
                                    </div>
                                    <div class="plus-qty-div">
                                        <i class="fa fa-plus" id="qty_plus"></i>
                                    </div>
                                </div>
                            </div>

                            @if($data->has_variant == 1 && !empty($data->attributes))
                                <input type="hidden" name="choice_attributes" id="choice_attributes" class="choice_attributes">
                                <input type="hidden" name="variant_value" id="variant_value" class="variant_value">
                                <div class="attributes mt-2 mb-2">
                                    @foreach($data->attributes as $key1 => $att)
                                        @php $att = (object) $att; @endphp
                                        <div class="row mb-3">
                                            <div class="col-md-12 col-12">
                                                <label class="mb-1 font-weight-bold" style="font-size: 14px;">
                                                    <span>{{ $att->attribute_name ?? '' }}</span>:
                                                    <span class="attribute-name-value text-primary font-weight-bold" style="color: #0088cc !important;"></span>
                                                </label>
                                                <div class="variant-options-group d-flex flex-wrap align-items-center" style="gap: 8px;">
                                                    <input type="hidden" name="attribute_id[]" value="{{ $att->attribute_id ?? '' }}">
                                                    @php $k = 0; @endphp
                                                    @if(!empty($att->values))
                                                        @foreach($att->values as $key2 => $attr_item)
                                                            @php
                                                                $attrVal = is_object($attr_item) ? ($attr_item->name ?? '') : (is_array($attr_item) ? ($attr_item['name'] ?? '') : $attr_item);
                                                                $attrId  = is_object($attr_item) ? ($attr_item->id ?? '') : (is_array($attr_item) ? ($attr_item['id'] ?? '') : '');
                                                                $attrSku = is_object($attr_item) ? ($attr_item->sku ?? '') : (is_array($attr_item) ? ($attr_item['sku'] ?? '') : '');
                                                                $attrImg = is_object($attr_item) ? ($attr_item->image ?? $attr_item->variant_image ?? '') : (is_array($attr_item) ? ($attr_item['image'] ?? $attr_item['variant_image'] ?? '') : '');
                                                            @endphp
                                                            <div class="custom-variant-item">
                                                                <input type="radio" id="val_{{$key1}}_{{$key2}}" name="attribute_item_id[{{$att->attribute_id ?? $key1}}][]"
                                                                       value="{{$attrVal}}"
                                                                       data-id="{{$attrId}}"
                                                                       data-sku="{{$attrSku}}"
                                                                       data-image="{{$attrImg ? asset($attrImg) : ''}}"
                                                                       class="attr_checkbox d-none" {{$k==0?'checked':""}}>
                                                                <label class="variant-option-btn mb-0 d-inline-flex align-items-center justify-content-center cursor-pointer" for="val_{{$key1}}_{{$key2}}">
                                                                    @if(!empty($attrImg))
                                                                        <img src="{{ asset($attrImg) }}" class="variant-swatch-img mr-1" alt="{{$attrVal}}" loading="lazy">
                                                                    @endif
                                                                    <span class="variant-text">{{$attrVal}}</span>
                                                                </label>
                                                            </div>
                                                            @php $k++; @endphp
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if ($data->delivery_text)
                                <span class="delivery_text">{{ $data->delivery_text ?? '' }}</span>
                            @endif

                            {{--<div class="button-wrapper">
                                <div class="mobile-call-button">
                                    <a href="tel:{{ $web_settings->website_phone ?? "" }}">
                                        <div class="button_call">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-phone-ringing"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 4l-2 2" /><path d="M22 10.5l-2.5 -.5" /><path d="M13.5 2l.5 2.5" /><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2c-8.072 -.49 -14.51 -6.928 -15 -15a2 2 0 0 1 2 -2" /></svg>
                                        </div>
                                    </a>
                                    <a href="https://wa.me/{{ $web_settings->website_phone ?? "" }}">
                                        <div class="button_whatsapp">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-brand-whatsapp"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" /><path d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" /></svg>
                                        </div>
                                    </a>
                                </div>

                                <div class="mt-md-2">
                                    <input type="submit" class="btn px-4 order_now_btn order_now_btn_m" name="order_now" value="অর্ডার করুন">
                                </div>

                                <div class="mt-md-2">
                                    <input type="submit" class="btn px-4 add_cart_btn" name="add_cart" value="কার্ট-এ যোগ করুন">
                                </div>
                            </div>--}}
                            <div class="mt-md-3 mt-2">
                                <input type="submit" class="btn px-4 order_now_btn new-order-btn" name="order_now" value="অর্ডার করুন">
                            </div>

                            <div class="mt-md-3 mt-2">
                                <input type="submit" class="btn px-4 add_cart_btn new-cart-btn" name="add_cart" value="কার্ট-এ যোগ করুন">
                            </div>
                        </form>

                        <div class="mt-md-3 mt-2">
                            <div class="row no-gutters">
                                <div class="col-6 pr-2">
                                    <a href="tel:{{ $web_settings->website_phone }}" class="btn btn-lg btn-block" style="background-color: #008eff; color: #fff">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-phone-call">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5 -2.5l5 2v4a2 2 0 0 1 -2 2a16 16 0 0 1 -15 -15a2 2 0 0 1 2 -2" />
                                            <path d="M15 7a2 2 0 0 1 2 2" /><path d="M15 3a6 6 0 0 1 6 6" />
                                        </svg>
                                        <span class="font-weight-bold ml-2" style="font-size: 15px">Phone Call</span>
                                    </a>
                                </div>

                                <div class="col-6 pl-2">
                                    <a href="https://wa.me/{{ $web_settings->website_phone3 }}" class="btn btn-lg btn-block" style="background: #1ad17A; color: #fff" target="_blank">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            class="icon icon-tabler icons-tabler-outline icon-tabler-brand-whatsapp"><path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                                            <path d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" />
                                        </svg>
                                        <span class="font-weight-bold ml-2" style="font-size: 15px;">WhatsApp</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{--<div class="mt-md-3 mt-2">
                            @if($web_settings->website_phone)
                                <h4 class="font-weight-bold">
                                    <a class="btn btn-success call-btn" href="tel:{{$web_settings->website_phone}}">
                                        <span>কল করতে ক্লিক করুন</span><br>
                                        <i class="fa fa-phone text-warning"></i>
                                        {{$web_settings->website_phone}}
                                    </a>
                                </h4>
                            @endif

                            @if($web_settings->website_phone2)
                                <h4 class="font-weight-bold">
                                    <a class="btn btn-success call-btn" href="tel:{{$web_settings->website_phone2}}">
                                        <span>কল করতে ক্লিক করুন</span><br>
                                        <i class="fa fa-phone text-warning"></i>
                                        {{$web_settings->website_phone2}}
                                    </a>
                                </h4>
                            @endif

                            @if($web_settings->website_phone3)
                                <h4 class="font-weight-bold">
                                    <a class="btn btn-success call-btn" href="tel:{{$web_settings->website_phone3}}">
                                        <span>কল করতে ক্লিক করুন</span><br>
                                        <i class="fa fa-phone text-warning"></i>
                                        {{$web_settings->website_phone3}}
                                    </a>
                                </h4>
                            @endif
                        </div>

                        <div class="fb-share-button mt-3" data-href="https://developers.facebook.com/docs/plugins/" data-layout="button" data-size="large">
                            <a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=&amp;src=sdkpreparse" class="fb-xfbml-parse-ignore"></a>
                        </div>

                        @if($shipping_methods->count() > 0)
                            <div class="col-12 mt-3 delivery_details" style="padding: 0">
                                <table class="table" style="color:#08c !important">
                                    <tbody>
                                    @foreach($shipping_methods as $item)
                                        <tr>
                                            <td style="padding-left: 0;border-bottom: 1px solid #ddd;">
                                                {{$item->text}}
                                            </td>
                                            <td style="border-bottom: 1px solid #ddd;">
                                                <b>{{$web_settings->currency_sign}} {{$item->amount}}</b>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                        <h6 class="font-weight-bold text-danger mt-md-3 mt-2">বিকাশ নাম্বার : {{$web_settings->bkash_merchant_numb}}</h6>--}}
                    </div>

                    <div class="col-md-3 mb-3">

                        <div class="row g-3">
                            <div class="col-md-12 col-6 px-2 mt-2">
                                <div class="feature-card">
                                    <div class="feature-icon lock-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                        </svg>
                                    </div>
                                    <div class="feature-text">
                                        <span class="feature-title">Secure Checkout</span>
                                        <span class="feature-subtitle">SSL Encrypted</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 col-6 px-2 mt-2">
                                <div class="feature-card">
                                    <div class="feature-icon cart-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="9" cy="21" r="1"></circle>
                                            <circle cx="20" cy="21" r="1"></circle>
                                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                        </svg>
                                    </div>
                                    <div class="feature-text">
                                        <span class="feature-title">Fast Shipping</span>
                                        <span class="feature-subtitle">Tracked Delivery</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 col-6 px-2 mt-2">
                                <div class="feature-card">
                                    <div class="feature-icon leather-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#007bff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 3h12l4 6-10 12L2 9z"></path>
                                            <path d="M11 3 8 9l4 12 4-12-3-6"></path>
                                            <path d="M2 9h20"></path>
                                        </svg>
                                    </div>
                                    <div class="feature-text">
                                        <span class="feature-title">Premium Quality</span>
                                        <span class="feature-subtitle">100% Genuine</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 col-6 px-2 mt-2">
                                <div class="feature-card">
                                    <div class="feature-icon craft-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#ffc107" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                        </svg>
                                    </div>
                                    <div class="feature-text">
                                        <span class="feature-title">Expert Craft</span>
                                        <span class="feature-subtitle">Handmade Care</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <style>
                            /* Card Styling */
                            .feature-card {
                                display: flex;
                                align-items: center;
                                background-color: #F8FAFC;
                                border: 1px solid #E2E8F0;
                                border-radius: 16px; /* Softly rounded corners as seen in your image */
                                padding: 10px;
                                transition: background-color 0.2s ease, border-color 0.2s ease;
                            }

                            .feature-card:hover {
                                background-color: #F1F5F9;
                                border-color: #CBD5E1;
                            }

                            /* Icon Containers */
                            .feature-icon {
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                width: 35px;
                                height: 35px;
                                flex-shrink: 0;
                                margin-right: 7px;
                            }

                            .feature-icon svg {
                                width: 26px;
                                height: 26px;
                            }

                            /* Distinct Icon Color Mappings matching your image */
                            .lock-icon svg {
                                stroke: #F59E0B; /* Orange / Yellow */
                            }

                            .cod-icon svg {
                                stroke: #EF4444; /* Light Red / Pinkish */
                            }

                            .premium-icon svg {
                                stroke: #334155; /* Slate/Dark Blue Star */
                            }

                            .returns-icon svg {
                                stroke: #0F172A; /* Dark Grey / Black Arrow */
                            }

                            /* Typography Details */
                            .feature-text {
                                display: flex;
                                flex-direction: column;
                            }

                            .feature-title {
                                color: #0F172A;
                                font-weight: 700;
                                font-size: 0.95rem;
                                line-height: 1.3;
                            }

                            .feature-subtitle {
                                color: #64748B;
                                font-size: 0.8rem;
                                margin-top: 3px;
                            }

                            /* --- Responsive Media Query --- */
                            @media (max-width: 640px) {
                                .feature-card {
                                    padding: 10px 0px;
                                }
                                .feature-title {
                                    font-size: 0.75rem;
                                }

                            }
                        </style>

                        <div class="feature-products d-md-block d-none">
                            <p>প্রয়োজনীয় প্রোডাক্ট</p>
                            <div class="feature-products-wrapper">
                                <table>
                                    @foreach($feature_prod as $item)
                                        <tr>
                                            <td class="img">
                                                <a href="{{route('product.details',[$item->slug,$item->id])}}">
                                                    <img width="50" src="{{$item->get_thumb?asset($item->get_thumb->file_url):asset('frontEnd/images/no_image.png')}}"
                                                         alt="">
                                                </a>
                                            </td>
                                            <td class="title">
                                                <a href="{{route('product.details',[$item->slug,$item->id])}}" class="text-dark">
                                                    {{$item->name}}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        @php
                            $product = $data;
                            $reviews = (isset($product_reviews) && count($product_reviews) > 0) ? $product_reviews : ($data->get_reviews ?? collect());
                            $review_count = count($reviews);
                            $avg_score = $review_count > 0 ? number_format($reviews->avg('rating'), 1) : '0.0';
                        @endphp
                        <ul class="nav nav-tabs nav-tabs-mod" id="prodDetailsTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active font-weight-bold" id="desc-tab" data-toggle="tab" href="#desc-pane" role="tab" aria-controls="desc-pane" aria-selected="true">
                                    <i class="fa fa-align-left mr-1"></i> পন্যের বিবরণ
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link font-weight-bold" id="rev-tab" data-toggle="tab" href="#rev-pane" role="tab" aria-controls="rev-pane" aria-selected="false">
                                    <i class="fa fa-star text-warning mr-1"></i> কাস্টমার রিভিউ ({{ $review_count }})
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content tab-content-mod p-3 p-md-4 bg-white border border-top-0 rounded-bottom">
                            <!-- Description Tab -->
                            <div class="tab-pane fade show active" id="desc-pane" role="tabpanel" aria-labelledby="desc-tab">
                                <div class="product-description-content" style="line-height: 1.8; color: #333;" aria-live="polite">
                                    <div class="product-description-loading text-center text-muted py-3">
                                        <i class="fa fa-spinner fa-spin mr-1" aria-hidden="true"></i>
                                        বিবরণ লোড হচ্ছে...
                                    </div>
                                </div>
                            </div>
                            <!-- Review Tab -->
                            <div class="tab-pane fade" id="rev-pane" role="tabpanel" aria-labelledby="rev-tab">
                                <div class="review-rating-wrapper">
                                    <!-- Summary Bar -->
                                    <div class="row align-items-center mb-4 p-3 bg-light rounded border">
                                        <div class="col-md-4 text-center border-right-md mb-3 mb-md-0">
                                            <h1 class="display-4 font-weight-bold text-dark mb-0">{{ $avg_score }}</h1>
                                            <div class="text-warning mb-1" style="font-size: 18px;">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($avg_score >= $i)
                                                        <i class="fa fa-star"></i>
                                                    @elseif($avg_score > ($i - 1))
                                                        <i class="fa fa-star-half-o"></i>
                                                    @else
                                                        <i class="fa fa-star-o text-muted" style="opacity: 0.4;"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                            <span class="text-muted small font-weight-bold">ভিত্তিক {{ $review_count }} টি কাস্টমার রিভিউ</span>
                                        </div>
                                        <div class="col-md-5 mb-3 mb-md-0">
                                            @for($star = 5; $star >= 1; $star--)
                                                @php
                                                    $pct = $review_count > 0 ? round(($reviews->where('rating', $star)->count() / $review_count) * 100) : 0;
                                                @endphp
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="small font-weight-bold text-muted mr-2" style="width: 45px;">{{ $star }} Star</span>
                                                    <div class="progress flex-grow-1" style="height: 8px; border-radius: 4px; background-color: #e9ecef;">
                                                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pct }}%;"></div>
                                                    </div>
                                                    <span class="small font-weight-bold text-muted ml-2" style="width: 35px;">{{ $pct }}%</span>
                                                </div>
                                            @endfor
                                        </div>
                                        <div class="col-md-3 text-center">
                                            @if($review_count > 3)
                                                <a href="{{ route('show.reviews', $data->id) }}" class="btn btn-outline-danger btn-block font-weight-bold py-2 shadow-sm" style="border-radius: 20px;">সকল রিভিউ দেখুন ({{ $review_count }})</a>
                                            @else
                                                <a href="#review-form-section" class="btn btn-danger btn-block font-weight-bold py-2 shadow-sm" style="border-radius: 20px;"><i class="fa fa-pencil mr-1"></i> রিভিউ লিখুন</a>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Customer Reviews List -->
                                    <div id="review-section" class="mb-4">
                                        <h5 class="font-weight-bold text-uppercase text-dark mb-3"><i class="fa fa-comments text-primary mr-1"></i> কাস্টমার অভিজ্ঞতা ({{ min(3, $review_count) }}/{{ $review_count }})</h5>
                                        @if($review_count > 0)
                                            @foreach ($reviews->sortByDesc('rating')->take(3) as $review)
                                                <div class="card mb-3 border shadow-sm rounded">
                                                    <div class="card-body p-3">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <div class="avatar-circle bg-primary text-white font-weight-bold rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 40px; height: 40px; font-size: 16px;">
                                                                {{ strtoupper(substr($review->name ?? 'C', 0, 1)) }}
                                                            </div>
                                                            <div>
                                                                <h6 class="mb-0 font-weight-bold text-dark">
                                                                    {{ $review->name }}
                                                                    <span class="badge text-success ml-1 py-1 px-2" style="font-size: 11px; background-color: #e6f4ea;"><i class="fa fa-check-circle mr-1"></i> Verified Buyer</span>
                                                                </h6>
                                                                <div class="text-warning small mt-1">
                                                                    @for($s = 1; $s <= 5; $s++)
                                                                        @if($review->rating >= $s)
                                                                            <i class="fa fa-star"></i>
                                                                        @elseif($review->rating > ($s - 1))
                                                                            <i class="fa fa-star-half-o"></i>
                                                                        @else
                                                                            <i class="fa fa-star-o text-muted" style="opacity: 0.4;"></i>
                                                                        @endif
                                                                    @endfor
                                                                    <span class="text-muted ml-2" style="font-size: 11px;">{{ $review->created_at ? date('d M, Y', strtotime($review->created_at)) : '' }}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <p class="comment_description text-secondary mb-2" style="font-size: 14px; line-height: 1.6;">{{ $review->summary ?? $review->review ?? '' }}</p>
                                                        @if(!empty($review->image) && is_array($review->image))
                                                            <div class="d-flex flex-wrap gap-2 mt-2">
                                                                @foreach($review->image as $img)
                                                                    <img src="{{ asset($img) }}" class="review-img img-thumbnail mr-2 mb-2" style="width: 75px; height: 75px; object-fit: cover; border-radius: 6px; cursor: pointer;" loading="lazy">
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="alert alert-light text-center py-4 border">
                                                <i class="fa fa-comment-o fa-2x text-muted mb-2 d-block"></i>
                                                <p class="text-muted mb-0 font-weight-bold">এখনও কোন রিভিউ পাওয়া যায়নি। আপনিই প্রথম আপনার মূল্যবান রিভিউটি দিন!</p>
                                            </div>
                                        @endif
                                    </div>

                                    <hr class="my-4">

                                    <!-- Submit Review Form Section -->
                                    <div id="review-form-section" class="bg-light p-3 p-md-4 rounded border">
                                        <h5 class="font-weight-bold text-uppercase text-dark mb-1"><i class="fa fa-pencil-square-o text-danger mr-1"></i> আপনার মূল্যায়ন / রিভিউ দিন</h5>
                                        <div id="review-message" style="display: none" class="alert alert-success mb-3"><strong>ধন্যবাদ!</strong> আপনার রিভিউটি সফলভাবে জমা নেওয়া হয়েছে।</div>

                                        <form id="reviewForm" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $data->id }}">
                                            <input type="hidden" id="start_rating_put" name="rating" value="5">

                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold small text-dark d-block mb-1">আপনার রেটিং সিলেক্ট করুন <span class="text-danger">*</span></label>
                                                <div id="star_rating_picker" class="d-inline-flex align-items-center p-2 bg-white rounded border">
                                                    @for($r = 1; $r <= 5; $r++)
                                                        <i class="fa fa-star star-item text-warning mr-1" data-value="{{ $r }}" style="font-size: 22px; cursor: pointer;"></i>
                                                    @endfor
                                                    <span id="star_rating_label" class="badge badge-warning font-weight-bold text-dark px-2 py-1 ml-1" style="font-size: 13px;">5 / 5 - চমৎকার!</span>
                                                </div>
                                            </div>

                                            <div class="form-row">
                                                <div class="form-group col-md-6 mb-3">
                                                    <label for="name" class="font-weight-bold small text-dark">আপনার নাম <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="name" name="name" placeholder="আপনার পুরো নাম লিখুন" required>
                                                </div>
                                                <div class="form-group col-md-6 mb-3">
                                                    <label for="email" class="font-weight-bold small text-dark">ইমেইল ঠিকানা (ঐচ্ছিক)</label>
                                                    <input type="email" class="form-control" id="email" name="email" placeholder="example@email.com">
                                                </div>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="summary" class="font-weight-bold small text-dark">আপনার রিভিউ লিখুন <span class="text-danger">*</span></label>
                                                <textarea class="form-control" name="summary" id="review" rows="3" placeholder="প্রোডাক্টটি আপনার কেমন লেগেছে তা বিস্তারিত লিখুন..." required></textarea>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label for="images" class="font-weight-bold small text-dark d-block">ছবি আপলোড করুন (ঐচ্ছিক)</label>
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" name="images[]" id="images" accept="image/*" multiple>
                                                    <label class="custom-file-label" for="images">ছবি বাছাই করুন (সর্বোচ্চ ৬ টি)</label>
                                                </div>
                                                <small id="image_validation_msg" class="text-muted d-block mt-1">আপনি সর্বোচ্চ ৬ টি ছবি আপলোড করতে পারবেন।</small>
                                            </div>

                                            <button class="btn btn-danger font-weight-bold px-4 py-2 shadow-sm" type="button" id="submitBtn" style="border-radius: 4px;"><i class="fa fa-paper-plane mr-1"></i> রিভিউ জমা দিন</button>
                                        </form>

                                        <script>
                                            document.addEventListener("DOMContentLoaded", function () {
                                                const imgInput = document.getElementById('images'), btn = document.getElementById('submitBtn'),
                                                      starItems = document.querySelectorAll('#star_rating_picker .star-item'),
                                                      ratingInput = document.getElementById('start_rating_put'), ratingLabel = document.getElementById('star_rating_label'),
                                                      reviewForm = document.getElementById('reviewForm'), reviewMsg = document.getElementById('review-message'),
                                                      labels = {1:"1 / 5 - খুব খারাপ", 2:"2 / 5 - চলনসই", 3:"3 / 5 - ভালো", 4:"4 / 5 - খুব ভালো", 5:"5 / 5 - চমৎকার!"};

                                                if (imgInput) {
                                                    imgInput.addEventListener('change', function() {
                                                        const msg = document.getElementById('image_validation_msg'), label = this.nextElementSibling, err = this.files.length > 6;
                                                        if (label) label.textContent = this.files.length > 0 ? this.files.length + " টি ফাইল সিলেক্ট করা হয়েছে" : "ছবি বাছাই করুন (সর্বোচ্চ ৬ টি)";
                                                        msg.textContent = err ? "আপনি ৬ টির বেশি ছবি আপলোড করতে পারবেন না।" : "আপনি সর্বোচ্চ ৬ টি ছবি আপলোড করতে পারবেন।";
                                                        msg.className = err ? "text-danger font-weight-bold d-block mt-1" : "text-muted d-block mt-1";
                                                        this.classList.toggle('is-invalid', err);
                                                        if (btn) btn.disabled = err;
                                                        if (err) this.value = "";
                                                    });
                                                }

                                                starItems.forEach(star => {
                                                    star.addEventListener('click', function() {
                                                        const val = parseInt(this.getAttribute('data-value'));
                                                        ratingInput.value = val;
                                                        if (ratingLabel) ratingLabel.textContent = labels[val] || (val + " / 5");
                                                        starItems.forEach((s, i) => s.className = 'fa fa-star star-item mr-1 ' + (i < val ? 'text-warning' : 'fa-star-o text-muted'));
                                                    });
                                                });

                                                if (btn && reviewForm) {
                                                    btn.addEventListener('click', function(e) {
                                                        e.preventDefault();
                                                        const nameVal = document.getElementById('name').value.trim();
                                                        const reviewVal = document.getElementById('review').value.trim();
                                                        if (!nameVal || !reviewVal) {
                                                            alert('অনুগ্রহ করে আপনার নাম এবং রিভিউ উভয়ই পূরণ করুন।');
                                                            return;
                                                        }
                                                        btn.disabled = true;
                                                        btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> সাবমিট হচ্ছে...';

                                                        fetch('{{ route("review.store") }}', {
                                                            method: 'POST',
                                                            headers: {
                                                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                                'Accept': 'application/json'
                                                            },
                                                            body: new FormData(reviewForm)
                                                        })
                                                        .then(res => res.json().then(data => ({ status: res.status, data: data })))
                                                        .then(result => {
                                                            btn.disabled = false;
                                                            btn.innerHTML = '<i class="fa fa-paper-plane mr-1"></i> রিভিউ জমা দিন';

                                                            if (result.data && result.data.success) {
                                                                if (reviewMsg) {
                                                                    reviewMsg.className = 'alert alert-success mb-3';
                                                                    reviewMsg.innerHTML = '<strong>ধন্যবাদ!</strong> ' + (result.data.message || 'আপনার রিভিউটি সফলভাবে জমা নেওয়া হয়েছে।');
                                                                    reviewMsg.style.display = 'block';
                                                                }
                                                                if (typeof toastr !== 'undefined') {
                                                                    toastr.success(result.data.message || 'আপনার রিভিউটি সফলভাবে জমা নেওয়া হয়েছে!');
                                                                }
                                                                reviewForm.reset();
                                                                ratingInput.value = "5";
                                                                starItems.forEach(s => s.className = 'fa fa-star star-item text-warning mr-1');
                                                                if (ratingLabel) ratingLabel.textContent = "5 / 5 - চমৎকার!";
                                                                const fileLabel = document.querySelector('.custom-file-label');
                                                                if (fileLabel) fileLabel.textContent = "ছবি বাছাই করুন (সর্বোচ্চ ৬ টি)";
                                                            } else {
                                                                const errorMsg = result.data && result.data.message ? result.data.message : 'রিভিউ জমা দিতে সমস্যা হয়েছে।';
                                                                if (reviewMsg) {
                                                                    reviewMsg.className = 'alert alert-danger mb-3';
                                                                    reviewMsg.innerHTML = '<strong>ত্রুটি!</strong> ' + errorMsg;
                                                                    reviewMsg.style.display = 'block';
                                                                }
                                                                if (typeof toastr !== 'undefined') {
                                                                    toastr.error(errorMsg);
                                                                }
                                                            }
                                                        })
                                                        .catch(err => {
                                                            btn.disabled = false;
                                                            btn.innerHTML = '<i class="fa fa-paper-plane mr-1"></i> রিভিউ জমা দিন';
                                                            if (reviewMsg) {
                                                                reviewMsg.className = 'alert alert-danger mb-3';
                                                                reviewMsg.innerHTML = '<strong>ত্রুটি!</strong> সার্ভারের সাথে সংযোগ করা যায়নি। আবার চেষ্টা করুন।';
                                                                reviewMsg.style.display = 'block';
                                                            }
                                                        });
                                                    });
                                                }
                                            });
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-5 related-products">
                    <div class="col-md-12">
                        <h4 class="mb-3">রিলেটেড প্রোডাক্ট</h4>
                    </div>
                </div>

                <div class="row m-0">
                    @foreach($related_prod as $item)
                        <div class="col-md-2 col-6 main-product">
                            <div class="main-product-inner-wrapper text-center">
                                <a href="{{route('product.details',[$item->slug,$item->id])}}">
                                    <img src="{{$item->get_thumb ? asset($item->get_thumb->file_url): asset('frontEnd/images/no_image.png')}}" alt="{{$item->name}}" loading="lazy">
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
                                    <input type="hidden" name="id" value="{{$item->id}}">
                                    <input type="hidden" name="qty" value="1">
                                    <input type="submit" class="btn btn-sm w-100 mb-2 order_now_btn" name="order_now" value="অর্ডার করুন">
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>


            </div>
        </div>
    </section>
@endsection



@section('script')
    <script>
        var productVariants = {!! json_encode($data->get_product_attributes ?? $data->get_attributes ?? []) !!};
        var baseProductSku = @json($data->sku ?? '');
        var productDescriptionUrl = @json(rtrim(env('IMS_APP_BASE_URL'), '/') . '/api/v1/app/product-description/' . $data->id);

        function loadProductDescription() {
            var descriptionContent = $('.product-description-content');

            fetch(productDescriptionUrl, {
                headers: {
                    Accept: 'application/json'
                }
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Product description request failed with status ' + response.status);
                    }
                    return response.json();
                })
                .then(function (data) {
                    var description = data && data.success && data.description
                        ? data.description.description
                        : null;

                    if (typeof description !== 'string') {
                        throw new Error('Product description was not included in the API response');
                    }

                    descriptionContent.html(description);
                })
                .catch(function (error) {
                    console.error('Unable to load product description:', error);
                    descriptionContent.html(
                        '<div class="alert alert-danger mb-0">পন্যের বিবরণ লোড করা যায়নি। অনুগ্রহ করে আবার চেষ্টা করুন।</div>'
                    );
                });
        }

        $('#qty_plus').on('click', function () {
            var qty = $('#qty').val();
            qty++;
            $('#qty').val(qty);
        });

        $('#qty_minus').on('click', function () {
            var qty = $('#qty').val();
            qty--;
            if (qty < 1) {
                $('#qty').val(1);
            } else {
                $('#qty').val(qty);
            }
        });

        function updateSelectedAttributeLabels() {
            $(".attr_checkbox:checked").each(function () {
                $(this).closest('.col-12').find('.attribute-name-value').text($(this).val());
            });
        }

        function findMatchingVariant(selectedIds, selectedNames) {
            if (!productVariants || productVariants.length === 0) return null;

            // 1. Match by attribute_item_ids
            if (selectedIds && selectedIds.length > 0) {
                var matched = productVariants.find(function (v) {
                    var itemIds = [];
                    if (typeof v.attribute_item_ids === 'string') {
                        try { itemIds = JSON.parse(v.attribute_item_ids); } catch(e) { itemIds = []; }
                    } else if (Array.isArray(v.attribute_item_ids)) {
                        itemIds = v.attribute_item_ids;
                    }
                    itemIds = itemIds.map(function (id) { return String(id); });
                    if (itemIds.length !== selectedIds.length) return false;
                    return selectedIds.every(function (id) { return itemIds.includes(String(id)); });
                });
                if (matched) return matched;
            }

            // 2. Match by variant suffix or SKU containing selected names
            if (selectedNames && selectedNames.length > 0) {
                var matched = productVariants.find(function (v) {
                    var vName = (v.variant || '').toLowerCase();
                    var vSku = (v.variant_sku || v.sku || '').toLowerCase();
                    return selectedNames.every(function (name) {
                        var clean = String(name).toLowerCase().replace(/\s+/g, '_');
                        var cleanDash = String(name).toLowerCase().replace(/\s+/g, '-');
                        return vName.includes(clean) || vName.includes(cleanDash) || vSku.includes(clean) || vSku.includes(cleanDash);
                    });
                });
                if (matched) return matched;
            }

            return null;
        }

        function syncVariantState() {
            updateSelectedAttributeLabels();

            var choice_attributes = [];
            var variant_ids = [];
            var singleSelectedSku = '';

            $(".attr_checkbox:checked").each(function () {
                choice_attributes.push($(this).val());
                var id = $(this).data('id');
                if (id) {
                    variant_ids.push(String(id));
                }
                var optSku = $(this).data('sku');
                if (optSku) {
                    singleSelectedSku = optSku;
                }
            });

            $('#variant_value').val(variant_ids.join(','));
            $('#choice_attributes').val(choice_attributes.join(','));

            var matched = findMatchingVariant(variant_ids, choice_attributes);
            if (matched) {
                var currentSku = matched.variant_sku || matched.sku || baseProductSku;
                if (currentSku) {
                    $('.single_prod_sku, .sku_text span:last-child').text(currentSku);
                }
                if (matched.variant) {
                    $('#choice_attributes').val(matched.variant);
                }
                if (matched.price || matched.variant_price) {
                    var vPrice = matched.price || matched.variant_price;
                    $('.sale_price').text('৳ ' + vPrice);
                    var regularPriceText = $('.regular_price').text();
                    var regular_price = parseFloat(regularPriceText.replace(/[^\d.]/g, ''));
                    if ($('.save_taka').length && !isNaN(regular_price)) {
                        $('.save_taka').text('Save ' + (regular_price - parseFloat(vPrice)) + 'TK');
                    }
                }
                if (matched.variant_image) {
                    $('#sing_prod_img_slider .carousel-item.active img').attr('src', matched.variant_image);
                }
            } else if (singleSelectedSku) {
                $('.single_prod_sku, .sku_text span:last-child').text(singleSelectedSku);
            }

            return { choice_attributes: choice_attributes, variant_ids: variant_ids, matched: matched };
        }

        $(document).ready(function () {
            loadProductDescription();

            var state = syncVariantState();
            var hasVariant = {{ $data->has_variant ?? 0 }};
            if (hasVariant == 1) {
                get_variant();
            }
        });

        //variant
        function get_variant() {
            var state = syncVariantState();

            $('.loader-wrapper').addClass('active');
            $('.loader').addClass('active');

            var product_id = $('#id').val();
            var quantity = $('#qty').val();
            var CSRF = `{{csrf_token()}}`;

            $.ajax({
                type: "POST",
                url: `{{route('ajax.get.variant')}}`,
                data: {
                    _token: CSRF,
                    choice_attributes: state.choice_attributes,
                    variant_value: $('#variant_value').val(),
                    id: product_id,
                    quantity: quantity
                },
                success: function (data) {
                    if (data.price) {
                        var regularPriceText = $('.regular_price').text();
                        var regular_price = parseFloat(regularPriceText.replace(/[^\d.]/g, ''));
                        var sale_price = parseFloat(data.price);
                        $('.sale_price').text('৳ ' + data.price);
                        if ($('.save_taka').length && !isNaN(regular_price)) {
                            $('.save_taka').text('Save ' + (regular_price - data.price) + 'TK');
                        }
                    }
                    if (data.choice_attributes) {
                        $('#choice_attributes').val(data.choice_attributes);
                    }
                    if (data.sku) {
                        $('.single_prod_sku, .sku_text span:last-child').text(data.sku);
                    }
                    if (data.variant_image) {
                        $('#sing_prod_img_slider .carousel-item.active img').attr('src', data.variant_image);
                    }
                    $('.loader-wrapper').removeClass('active');
                    $('.loader').removeClass('active');
                },
                error: function () {
                    $('.loader-wrapper').removeClass('active');
                    $('.loader').removeClass('active');
                }
            });
        }

        $(document).on('change click', '.attr_checkbox', function () {
            var optImg = $(this).data('image');
            if (optImg) {
                $('#sing_prod_img_slider .carousel-item.active img').attr('src', optImg);
            }
            get_variant();
        });
    </script>

    <!-- SweetAlert2 for Review Image Modal -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).on('click', '.review-img', function () {
            Swal.fire({
                imageUrl: $(this).attr('src'),
                imageAlt: 'Review Photo',
                showCloseButton: true,
                showConfirmButton: false,
                background: '#ffffff',
                backdrop: 'rgba(0,0,0,0.75)',
                width: 'auto',
                maxWidth: '90%'
            });
        });
    </script>
@endsection
