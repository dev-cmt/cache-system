@extends('layouts.master')
@section('title') Customer Reviews for {{ $product->name }} @endsection

@php
    $total_reviews = count($reviews);
    $avg_rating = $total_reviews > 0 ? number_format($reviews->avg('rating'), 1) : 0;
@endphp

@section('body')
<section class="category_breadcrumb">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <p>
                    <a href="{{ route('home') }}">Home</a> / 
                    <a href="{{ route('product.details', [$product->slug, $product->id]) }}">{{ $product->name }}</a> / 
                    <a href="javascript:void(0);">Customer Reviews</a>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="py-4 py-md-5 bg-light">
    <div class="container">
        <!-- Product Header Summary Card -->
        <div class="row mb-4">
            <div class="col-lg-10 mx-auto">
                <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                    <div class="card-body p-3 p-md-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center">
                                <a href="{{ route('product.details', [$product->slug, $product->id]) }}" class="mr-3">
                                    <img src="{{ $product->get_thumb ? asset($product->get_thumb->file_url) : asset('frontEnd/images/no_image.png') }}" 
                                         alt="{{ $product->name }}" 
                                         class="img-thumbnail rounded" 
                                         style="width: 80px; height: 80px; object-fit: cover;">
                                </a>
                                <div>
                                    <h4 class="font-weight-bold text-dark mb-1">
                                        <a href="{{ route('product.details', [$product->slug, $product->id]) }}" class="text-dark">
                                            {{ $product->name }}
                                        </a>
                                    </h4>
                                    <div class="d-flex align-items-center flex-wrap">
                                        <div class="text-warning mr-2" style="font-size: 15px;">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($avg_rating >= $i)
                                                    <i class="fa fa-star"></i>
                                                @elseif($avg_rating > ($i - 1) && $avg_rating < $i)
                                                    <i class="fa fa-star-half-o"></i>
                                                @else
                                                    <i class="fa fa-star-o text-muted" style="opacity: 0.4;"></i>
                                                @endif
                                            @endfor
                                        </div>
                                        <span class="font-weight-bold text-dark mr-2">{{ $avg_rating }} / 5</span>
                                        <span class="badge badge-secondary px-2 py-1 font-weight-bold">{{ $total_reviews }} টি রিভিউ</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <a href="{{ route('product.details', [$product->slug, $product->id]) }}" class="btn btn-outline-danger font-weight-bold shadow-sm px-4 py-2" style="border-radius: 20px;">
                                    <i class="fa fa-arrow-left mr-1"></i> পন্যের পেজে ফিরে যান
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- All Reviews Container -->
        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="reviews-wrapper shadow-sm bg-white p-4 rounded-3 border">
                    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                        <h4 class="font-weight-bold text-dark mb-0">
                            <i class="fa fa-comments text-primary mr-2"></i>সকল কাস্টমার রিভিউ ({{ $total_reviews }})
                        </h4>
                    </div>

                    @if($reviews->count() > 0)
                        <div class="row">
                            @foreach ($reviews as $review)
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 border shadow-sm rounded">
                                        <div class="card-body p-3 p-md-4">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle bg-primary text-white font-weight-bold rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 42px; height: 42px; font-size: 16px;">
                                                        {{ strtoupper(substr($review->name ?? 'C', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 font-weight-bold text-dark">
                                                            {{ $review->name }}
                                                            <span class="badge badge-success-light text-success font-weight-normal ml-1 py-1 px-2" style="font-size: 11px; background-color: #e6f4ea;">
                                                                <i class="fa fa-check-circle mr-1"></i> Verified Buyer
                                                            </span>
                                                        </h6>
                                                        <div class="text-warning small mt-1">
                                                            @for($s = 1; $s <= 5; $s++)
                                                                @if($review->rating >= $s)
                                                                    <i class="fa fa-star"></i>
                                                                @elseif($review->rating > ($s - 1) && $review->rating < $s)
                                                                    <i class="fa fa-star-half-o"></i>
                                                                @else
                                                                    <i class="fa fa-star-o text-muted" style="opacity: 0.4;"></i>
                                                                @endif
                                                            @endfor
                                                        </div>
                                                    </div>
                                                </div>
                                                <span class="badge badge-light text-muted font-weight-normal px-2 py-1 border">
                                                    {{ date('d M, Y', strtotime($review->created_at)) }}
                                                </span>
                                            </div>

                                            <p class="comment_description text-secondary my-3" style="font-size: 14.5px; line-height: 1.6;">
                                                {{ $review->summary ?? $review->review ?? '' }}
                                            </p>

                                            @if(!empty($review->image) && is_array($review->image))
                                                <div class="review-images-wrapper mt-3 d-flex flex-wrap gap-2">
                                                    @foreach($review->image as $img)
                                                        <img src="{{ asset($img) }}"
                                                             alt="User Review Image"
                                                             class="review-img img-thumbnail mr-2 mb-2"
                                                             style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px; cursor: pointer; transition: transform 0.2s;"
                                                             loading="lazy">
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Empty State Notice -->
                        <div class="text-center py-5">
                            <i class="fa fa-comments-o fa-3x text-muted mb-3 opacity-50"></i>
                            <h5 class="text-muted font-weight-bold mb-2">এই পন্যে এখনও কোন কাস্টমার রিভিউ নেই</h5>
                            <p class="text-muted small mb-4">আপনি পন্যটির পেজে গিয়ে প্রথম রিভিউ প্রদান করতে পারেন।</p>
                            <a href="{{ route('product.details', [$product->slug, $product->id]) }}" class="btn btn-danger font-weight-bold px-4 py-2 shadow-sm">
                                <i class="fa fa-pencil mr-1"></i> রিভিউ লিখুন
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).on('click', '.review-img', function () {
        Swal.fire({
            imageUrl: $(this).attr('src'),
            imageAlt: 'Review Image',
            showCloseButton: true,
            showConfirmButton: false,
            background: '#ffffff',
            backdrop: 'rgba(0,0,0,0.75)',
            width: 'auto',
            maxWidth: '90%'
        });
    });
</script>
<style>
    .review-img:hover {
        transform: scale(1.08);
        border-color: #ff5722 !important;
    }
</style>
@endsection
