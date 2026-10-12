@extends('layouts.master')

@section('title')
    Checkout
@endsection

@section('style')
    <style>
        .checkout_form ::placeholder {
            font-family: inherit;
            font-size: 14px;
        }

        /* Sticky Mobile Submit Button */
        /* @media (max-width: 767.98px) {
            html, body {
                overflow-anchor: none;
            }

            #conf_order_btn {
                position: fixed;
                left: 15px;
                width: calc(100% - 30px) !important;
                bottom: calc(12px + env(safe-area-inset-bottom, 0px));
                z-index: 9999;
                margin-bottom: 0 !important;
                border-radius: 8px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                transform: translateY(0);
                will-change: transform;
            }
        } */
    </style>
@endsection

@if(\Cart::getContent()->count() > 0)
@section('tracking')
    <script>
        dataLayer.push({ecommerce: null});  // Clear the previous ecommerce object.
        dataLayer.push({
            event: "begin_checkout",
            ecommerce: {
                currency: "BDT",
                value: {{ session('api_begin_checkout_data')['value'] ?? 0 }},
                coupon: "",
                items: {!! session('api_begin_checkout_data')['products'] ?? '[]' !!}
            }
        });
        if (typeof fbq === 'function') {
            var checkoutItems = {!! session('api_begin_checkout_data')['products'] ?? '[]' !!};
            fbq('track', 'InitiateCheckout', {
                content_ids: checkoutItems.map(function (item) { return item.item_id; }),
                content_type: 'product',
                num_items: checkoutItems.reduce(function (total, item) { return total + (Number(item.quantity) || 0); }, 0),
                value: {{ session('api_begin_checkout_data')['value'] ?? 0 }},
                currency: 'BDT'
            });
        }
    </script>
@endsection
@endif

@section('body')
    @if(\Cart::getContent()->count() > 0)
        <section>
            <div class="cart-section">
                <div class="container">
                    <div class="row">
                        <div class="col-md-5 col-12 mb-md-0 mb-4">
                            <div class="card">
                                <h5 class="font-weight-bold card-header">কাস্টমার ইনফরমেশন</h5>
                                <div class="card-body p-2">
                                    <p class="text-center">অর্ডারটি কনফার্ম করতে আপনার নাম, ঠিকানা, মোবাইল নাম্বার, লিখে <span
                                            class="text-danger">অর্ডার কনফার্ম করুন</span> বাটনে ক্লিক করুন</p>
                                    <form action="{{route('place.order')}}" method="post" id="checkout_form" class="checkout_form">
                                        @csrf
                                        <input type="hidden" name="shipping_cost" id="shipping_cost">
                                        <div class="form-group">
                                            <label for="customer_name">নাম <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('customer_name')is-invalid @enderror" id="customer_name" name="customer_name" placeholder="আপনার নাম লিখুন" required value="{{old('customer_name')}}" {{--onkeydown="return /[a-zA-Z ]/i.test(event.key)" onblur="if (this.value == '') {this.value = '';}" onfocus="if (this.value == '') {this.value = '';}"--}}>
                                            @error('customer_name')
                                            <span class="text-danger font-weight-bold">{{$message}}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="customer_phone">মোবাইল <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control @error('customer_phone')is-invalid @enderror" id="customer_phone" name="customer_phone"
                                                   placeholder="আপনার ১১ সংখ্যার মোবাইল নাম্বার লিখুন" minlength="11" maxlength="11" required value="{{old('customer_phone')}}">
                                            @error('customer_phone')
                                            <span class="text-danger font-weight-bold">{{$message}}</span>
                                            @enderror
                                        </div>


                                        {{--<div class="form-group">
                                            <label for="district">আপনার জেলা সিলেক্ট করুন <span class="text-danger">*</span></label>
                                            <select name="district" id="district" class="form-control" required>
                                                <option value="">জেলা সিলেক্ট করুন</option>
                                                @foreach($districts as $district)
                                                    <option value="{{$district->name}}">{{$district->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>--}}
                                        <div class="form-group">
                                            <label for="customer_address">ঠিকানা <span class="text-danger">*</span></label>
                                            <textarea type="text" class="form-control" id="customer_address" name="customer_address" placeholder="গ্রাম/বাসা, রোড, এলাকা/থানা, জেলা সহ সম্পূর্ণ ঠিকানা লিখুন।"
                                                      required rows="3">{{old('customer_address')}}</textarea>
                                        </div>

                                        {{--<div class="form-group">
                                            <label for="shipping_method">আপনার এরিয়া সিলেক্ট করুন <span class="text-danger">*</span></label>
                                            <select name="shipping_method" id="shipping_method" class="form-control" required>
                                                @foreach($shipping_methods as $item)
                                                    <option value="{{$item->id}}">{{$item->text}}</option>
                                                @endforeach
                                            </select>
                                        </div>--}}

                                        <div class="form-group">
                                            @foreach($shipping_methods as $key => $item)
                                                <div class="form-check">
                                                    <input type="radio" name="shipping_method" id="shipping_method{{$key}}" class="form-check-input"
                                                           value="{{$item->id}}" {{$key==0?"checked":""}}>
                                                    <label class="form-check-label font-weight-normal" for="shipping_method{{$key}}">{{$item->text}}</label>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="form-group">
                                            <textarea class="form-control" id="order_note" name="order_note" placeholder="অর্ডার বা ডেলিভারি সম্পর্কে কোনো নির্দেশনা থাকলে এখানে লিখুন।"></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-success w-100 mb-2" style="height: 50px" id="conf_order_btn">অর্ডার কনফার্ম করুন</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7 col-12">
                            <div class="card">
                                <h5 class="font-weight-bold card-header">অর্ডার ইনফরমেশন</h5>
                                <div class="card-body p-2 table-responsive">
                                    <table class="cart_table table table-bordered table-striped text-center mb-0">
                                        <thead>
                                        <tr>
                                            <th></th>
                                            <th>Product Name & Image</th>
                                            <th>Price</th>
                                            <th>Qty</th>
                                            <th>Sub Total</th>
                                        </tr>
                                        </thead>

                                        <tbody>
                                        @foreach(\Cart::getContent() as $item)
                                            <tr>
                                                <td>
                                                    <a href="{{route('cart.item.delete',$item->id)}}"><i class="fa fa-trash-o text-danger"></i></a>
                                                </td>
                                                <td class="text-left">
                                                    @php
                                                        $itemImg = !empty($item->attributes['image']) ? $item->attributes['image'] : (!empty($item->attributes->image) ? $item->attributes->image : ($item->associatedModel->get_thumb ? $item->associatedModel->get_thumb->file_url : ''));
                                                        $variantName = $item->attributes['variant'] ?? ($item->attributes->variant ?? null);
                                                    @endphp
                                                    <div class="d-flex align-items-center">
                                                        <img width="45" height="45" style="object-fit: cover; border-radius: 4px; margin-right: 8px; border: 1px solid #e2e8f0;" src="{{ $itemImg ? asset($itemImg) : asset('frontEnd/images/no_image.png') }}" alt="{{ $item->name }}">
                                                        <div>
                                                            <a style="font-size: 14px; font-weight: 600;"
                                                               href="{{route('product.details',[$item->associatedModel->slug,$item->associatedModel->id])}}">{{$item->name}}</a>
                                                            @if($variantName)
                                                                <div class="mt-1">
                                                                    <span class="badge badge-info" style="font-size: 11px; font-weight: normal; background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;">{{ $variantName }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{$item->price}}</td>
                                                <td width="15%" class="cart_qty">
                                                    <a href="{{route('cart.item.minus',$item->id)}}"><i class="fa fa-minus" id="qty_minus"></i></a>
                                                    <input type="text" name="qty" id="qty" min="1" value="{{$item->quantity}}" readonly>
                                                    <a href="{{route('cart.item.plus',$item->id)}}"><i class="fa fa-plus" id="qty_plus"></i></a>
                                                </td>
                                                <td>{{$item->getPriceSum()}}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-right pr-2">Net Total</th>
                                            <td><span id="net_total">{{\Cart::getTotal()}}</span></td>
                                        </tr>
                                        <tr>
                                            <th colspan="4" class="text-right pr-2">Shipping Cost</th>
                                            <td>
                                                <span id="cart_shipping_cost">0</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th colspan="4" class="text-right pr-2">Grand Total</th>
                                            <td>
                                                <span id="grand_total"></span>
                                            </td>
                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="card-footer">
                                    <a href="{{route('home')}}" class="btn btn-info btn-sm ">
                                        <i class="fa fa-angle-left"></i> Back To Shopping
                                    </a>
                                    <a href="{{route('cart.clear')}}" class="btn btn-danger btn-sm float-right">Cart Clear</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @else
        <section class="py-md-5">
            <div class="cart-section">
                <div class="container">
                    <div class="row py-md-5">
                        <div class="col-12 text-center">
                            <h1 class="mb-md-4">কোন প্রোডাক্ট নেই</h1>
                            <a href="{{route('home')}}" class="btn btn-info px-5">প্রোডাক্ট বাছাই করুন</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endsection


@section('script')
    <script>
        $(document).ready(function () {
            /* ---------------------------------------------------------------
             * Keep #conf_order_btn glued just above the on-screen keyboard.
             *
             * On mobile, `position: fixed` is anchored to the *layout*
             * viewport, which does NOT shrink when the keyboard opens (on
             * iOS especially). The *visual* viewport does shrink. That
             * mismatch is what causes the button to jump, disappear behind
             * the keyboard, or float in the middle of the screen.
             *
             * window.visualViewport tells us exactly how tall the visible
             * area currently is, so we translate the button up by the
             * keyboard's height instead of leaving it at a fixed CSS bottom.
             * ------------------------------------------------------------- */
            var $btn = $('#conf_order_btn');
            var btnEl = $btn.get(0);
            var isMobile = function () {
                return window.matchMedia('(max-width: 767.98px)').matches;
            };

            var rafPending = false;
            var lastOffset = 0;

            function applyOffset() {
                rafPending = false;
                if (!isMobile() || !window.visualViewport) {
                    if (lastOffset !== 0) {
                        lastOffset = 0;
                        btnEl.style.transform = 'translateY(0)';
                    }
                    return;
                }

                var vv = window.visualViewport;
                var covered = window.innerHeight - vv.height - vv.offsetTop;
                var offset = covered > 0 ? Math.round(covered) : 0;
                if (offset !== lastOffset) {
                    lastOffset = offset;
                    btnEl.style.transform = 'translateY(-' + offset + 'px)';
                }
            }

            function pinButtonAboveKeyboard() {
                if (rafPending) return;
                rafPending = true;
                requestAnimationFrame(applyOffset);
            }

            if (window.visualViewport) {
                window.visualViewport.addEventListener('resize', pinButtonAboveKeyboard, {passive: true});
                window.visualViewport.addEventListener('scroll', pinButtonAboveKeyboard, {passive: true});
            }
            pinButtonAboveKeyboard();

            $('#checkout_form input, #checkout_form textarea, #checkout_form select').on('focus', function () {
                if (!isMobile()) return;
                var el = this;
                setTimeout(function () {
                    el.scrollIntoView({block: 'center', behavior: 'smooth'});
                    pinButtonAboveKeyboard();
                }, 300);
            });

            $('#customer_phone').on('change, blur', function() {
                var phone = $(this).val();
                var name = $('#customer_name').val();
                var address = $('#customer_address').val();
                var shipping_method = $('#shipping_method').val();
                var shipping_cost = $('#shipping_cost').val();
                var data = {
                    phone: phone,
                    name: name,
                    address: address,
                    shipping_method: shipping_method,
                    shipping_cost: shipping_cost
                };
                if (phone.length == 11) {
                    $.ajax({
                        url: '{{ route('abandoned.cart') }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            data: data
                        },
                        success: function(data) {
                            // console.log(data)
                        }
                    });
                }
            });


            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '{{route('ajax.get.shipp.meth')}}',
                type: 'POST',
                data: {_token: CSRF_TOKEN, id: $("input[name='shipping_method']:checked").val()},
                success: function (data) {
                    $("#cart_shipping_cost").text(data);
                    $("#shipping_cost").val(data);
                    calculate();
                }
            });


            $("input[name='shipping_method']").on('click', function () {
                if ($("input[name='shipping_method']:checked").val()) {
                    $.ajax({
                        url: '{{route('ajax.get.shipp.meth')}}',
                        type: 'POST',
                        data: {_token: CSRF_TOKEN, id: $(this).val()},
                        success: function (data) {
                            $("#cart_shipping_cost").text(data);
                            $("#shipping_cost").val(data);
                            calculate();
                        }
                    });
                } else {
                    $("#cart_shipping_cost").text(0);
                    $("#shipping_cost").val(0);
                    calculate();
                }

            });


            function calculate() {
                var net_total = parseFloat($('#net_total').text());
                var cart_shipping_cost = parseFloat($('#cart_shipping_cost').text());
                $('#grand_total').text(net_total + cart_shipping_cost);
            }


            $("#checkout_form").submit(function () {
                $("#conf_order_btn").attr("disabled", true).text('সাবমিট হচ্ছে...');
            });
        });
    </script>
@endsection

