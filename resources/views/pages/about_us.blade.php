@extends('layouts.master')

@section('title')
    About Us
@endsection

@section('body')
    <section>
        <div class="cart-section">
            <div class="container">
                <div class="row">
                    <div class="col-12 mb-md-0 mb-4">
                        <h5 class="font-weight-bold">About Us</h5>
                        <div class="p-2">
                            {!! $content !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
