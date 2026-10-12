@extends('layouts.master')

@section('title')
    Searched Products
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
                            @if(!empty($category_id))
                                @php $activeCat = $categories->firstWhere('id', $category_id); @endphp
                                @if($activeCat)
                                    <a href="{{ route('category.show', $activeCat->id) }}">{{ $activeCat->category_name }}</a> /
                                @endif
                            @endif
                            @if($query)
                                Searched For "{{$query}}"
                            @else
                                সকল পণ্য
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="main-products-section">
            <div class="container">
                <div class="row m-0">
                    @if($data->count() > 0)
                        @foreach($data as $item)
                            <div class="col-md-2 col-6 main-product">
                                <div class="main-product-inner-wrapper text-center">
                                    <a href="{{route('product.details',[$item->slug,$item->id])}}">
                                        <img src="{{$item->get_thumb ? asset($item->get_thumb->file_url) : asset('frontEnd/images/no_image.png')}}" alt="{{$item->name}}" loading="lazy">
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
                    @else
                        <div class="col-12 text-center py-md-5 my-md-5">
                            <h2 class="mb-md-4" style="color: red;font-weight: bold">দুঃখিত কোন পণ্য পাওয়া যায়নি</h2>
                            <a href="{{route('home')}}" class="btn btn-success px-5" style="background-color: green">হোম</a>
                        </div>
                    @endif
                </div>

                <div class="row">
                    <div class="col-12 mt-3">
                        {{$data->appends(request()->only(['query','category']))->links()}}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection



@section('script')

@endsection
