<?php $__env->startSection('title'); ?>
    Home
<?php $__env->stopSection(); ?>

<?php $__env->startSection('body'); ?>
    <?php
        $hotDealAll = collect($hot_deal ?? []);
        $half       = (int) ceil($hotDealAll->count() / 2);
        $hot_deal_1 = $hotDealAll->take($half);
        $hot_deal_2 = $hotDealAll->skip($half);
    ?>

    <section>
        <div class="slider">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <div id="home_slider" class="carousel slide" data-ride="carousel">
                            <ol class="carousel-indicators">
                                <?php $__currentLoopData = $sliders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li data-target="#home_slider" data-slide-to="<?php echo e($key); ?>" <?php if($key==0): ?> class="active" <?php endif; ?>></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ol>
                            <div class="carousel-inner">
                                <?php $__currentLoopData = $sliders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="carousel-item <?php echo e($key==0 ? 'active':''); ?>">
                                        <img src="<?php echo e($item->get_img ? asset($item->get_img->file_url) : asset('frontEnd/images/no_image.png')); ?>" class="d-block w-100" alt="">
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if($hot_deal_1->count() > 0 || $hot_deal_2->count() > 0): ?>
    <section>
        <div class="hot-deals">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="hot-deals-inner-wrapper">
                            <div class="row mb-3">
                                <div class="col-md-6 col-6">
                                    <div class="hot-deals-gif">
                                        <img src="<?php echo e(asset('frontEnd/images/hot-deal-logo.gif')); ?>" alt="">
                                    </div>
                                </div>
                                <div class="col-md-6 col-6">
                                    <div class="all-hot-deals-btn text-right mt-2">
                                        <a href="<?php echo e(route('all.hot.deals')); ?>">সকল হট ডিল <i class="fa fa-angle-right"></i></a>
                                    </div>
                                </div>
                            </div>

                            <?php if($hot_deal_1->count() > 0): ?>
                            <div class="owl-carousel mb-3">
                                <?php $__currentLoopData = $hot_deal_1; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $percentage = ($item->price > 0 && $item->sale_price > 0)
                                            ? round(100 - (($item->sale_price / $item->price) * 100))
                                            : 0;
                                    ?>
                                    <div class="hot-deals-product">
                                        <a href="<?php echo e(route('product.details',[$item->slug,$item->id])); ?>">
                                            <div class="discount">
                                                <div class="discount-wrapper">
                                                    <img src="<?php echo e(asset('frontEnd/images/flash-deal-percentage.png')); ?>" alt="">
                                                    <span><?php echo e($percentage); ?>%</span> <br>
                                                    <span>ছাড়</span>
                                                </div>
                                            </div>
                                            <p class="float_price"><?php echo e($web_settings->currency_sign); ?> <?php echo e($item->sale_price); ?></p>
                                            <img src="<?php echo e($item->get_thumb ? asset($item->get_thumb->file_url) : asset('frontEnd/images/no_image.png')); ?>" alt="<?php echo e($item->name); ?>" loading="lazy">
                                        </a>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <?php endif; ?>

                            <?php if($hot_deal_2->count() > 0): ?>
                            <div class="owl-carousel">
                                <?php $__currentLoopData = $hot_deal_2; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $percentage = ($item->price > 0 && $item->sale_price > 0)
                                            ? round(100 - (($item->sale_price / $item->price) * 100))
                                            : 0;
                                    ?>
                                    <div class="hot-deals-product">
                                        <a href="<?php echo e(route('product.details',[$item->slug,$item->id])); ?>">
                                            <div class="discount">
                                                <div class="discount-wrapper">
                                                    <img src="<?php echo e(asset('frontEnd/images/flash-deal-percentage.png')); ?>" alt="">
                                                    <span><?php echo e($percentage); ?>%</span> <br>
                                                    <span>ছাড়</span>
                                                </div>
                                            </div>
                                            <p class="float_price"><?php echo e($web_settings->currency_sign); ?> <?php echo e($item->sale_price); ?></p>
                                            <img src="<?php echo e($item->get_thumb ? asset($item->get_thumb->file_url) : asset('frontEnd/images/no_image.png')); ?>" alt="<?php echo e($item->name); ?>" loading="lazy">
                                        </a>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section>
        <div class="main-products-section">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="mb-3">প্রয়োজনীয় প্রোডাক্ট</h4>
                    </div>
                </div>
                <div class="row m-0">
                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="col-md-2 col-6 main-product">
                            <div class="main-product-inner-wrapper text-center">
                                <a href="<?php echo e(route('product.details',[$item->slug,$item->id])); ?>">
                                    <img src="<?php echo e($item->get_thumb ? asset($item->get_thumb->file_url) : asset('frontEnd/images/no_image.png')); ?>" alt="<?php echo e($item->name); ?>" loading="lazy">
                                </a>
                                <?php if($item->sale_price != 0): ?>
                                    <p class="mb-0" style="text-decoration: line-through;color: #b8b8b8"><?php echo e($web_settings->currency_sign); ?> <?php echo e($item->price); ?></p>
                                    <p class="font-weight-bold mb-0" style="color: #fca204"><?php echo e($web_settings->currency_sign); ?> <?php echo e($item->sale_price); ?></p>
                                <?php else: ?>
                                    <p class="font-weight-bold mb-0" style="margin-top: 24px;color: #fca204"><?php echo e($web_settings->currency_sign); ?> <?php echo e($item->price); ?></p>
                                <?php endif; ?>
                                <p class="mb-0 prod_name"><a href="<?php echo e(route('product.details',[$item->slug,$item->id])); ?>"><?php echo e($item->name); ?></a></p>
                                <form action="<?php echo e(route('add.cart',$item->id)); ?>" method="post">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="qty" value="1">
                                    <input type="submit" class="btn btn-sm w-100 mb-2 order_now_btn" name="order_now" value="অর্ডার করুন">
                                </form>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="row mt-md-4 mt-2">
                    <div class="col-12">
                        <?php echo e($products->links()); ?>

                    </div>
                </div>
            </div>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\xampp\htdocs\cache-system\resources\views/index.blade.php ENDPATH**/ ?>