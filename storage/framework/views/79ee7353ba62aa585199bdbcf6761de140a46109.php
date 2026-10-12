<footer>
    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="footer-menu">
                        <ul>
                            <li>
                                <a href="<?php echo e(route('about_us')); ?>">আমাদের সম্পর্কে</a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('delivery_policy')); ?>">ডেলিভারি পলিসি</a>
                            </li>
                            <li>
                                <a href="<?php echo e(route('return_policy')); ?>">রিটার্ন পলিসি</a>
                            </li>
                        </ul>
                    </div>
                    <div class="copyright_text">
                        <p><?php echo $web_settings->website_copyright_text; ?> Developed By <a href="https://prodevsltd.com" target="_blank">Pro Devs</a> </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>


<style>
    .footer {
        height: 90px;
    }
    .main-products-section {
        min-height: calc(100vh - 90px);
        box-sizing: border-box;
    }

    @media (max-width: 767px) {
        .footer {
            height: 140px;
        }
        .main-products-section {
            min-height: calc(100vh - 250px);
        }
    }
</style>
<?php /**PATH D:\xampp\htdocs\cache-system\resources\views/inc/footer.blade.php ENDPATH**/ ?>