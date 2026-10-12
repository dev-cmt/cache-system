<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group.
|
*/

// Cache clearing utility
Route::get('/cc', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    (new \App\Services\AppApiService())->clearCache();
    return 'All caches and API local storage cleared successfully!';
});

// FB product catalog feed
Route::get('/product-catalog-feed', 'HomeController@productsCatalogFeed')->name('products.catalog.feed');

// Storefront Frontend Routes
Route::get('/', 'HomeController@index')->name('home');
Route::get('/category/{id}', 'HomeController@getCategory')->name('category.show');
Route::get('/product/{slug}/{id}', 'HomeController@productDetails')->name('product.details');
Route::get('/all-hot-deals', 'HomeController@allHotDeals')->name('all.hot.deals');
Route::get('/search', 'HomeController@search')->name('search');
Route::get('/ajax-search', 'HomeController@ajaxSearch')->name('ajax.search');

// Product Reviews
Route::post('/review/store', 'HomeController@reviewStore')->name('review.store');
Route::get('/show-reviews/{id}', 'HomeController@showReviews')->name('show.reviews');

// Shopping Cart & AJAX
Route::post('/ajax-get-variant', 'HomeController@ajaxGetVariant')->name('ajax.get.variant');
Route::post('/add-cart/{id}', 'HomeController@addCart')->name('add.cart');
Route::get('/cart-item-delete/{id}', 'HomeController@cartItemDelete')->name('cart.item.delete');
Route::get('/cart-item-plus/{id}', 'HomeController@cartItemPlus')->name('cart.item.plus');
Route::get('/cart-item-minus/{id}', 'HomeController@cartItemMinus')->name('cart.item.minus');
Route::get('/cart-clear', 'HomeController@cartClear')->name('cart.clear');
Route::post('/ajax-get-shipp-meth', 'HomeController@getShippMeth')->name('ajax.get.shipp.meth');

// Orders & Checkout
Route::get('/checkout', 'HomeController@checkout')->name('checkout');
Route::post('/place-order', 'HomeController@placeOrder')->name('place.order');
Route::get('/confirm-order', 'HomeController@confirmOrder')->name('confirm.order');
Route::get('/confiirm-order', 'HomeController@confiirmOrder')->name('confiirm.order');
Route::post('/abandoned-cart', 'HomeController@abandonedCart')->name('abandoned.cart');

// CMS Pages
Route::get('/about-us', 'HomeController@aboutUs')->name('about_us');
Route::get('/contact-us', 'HomeController@contactUs')->name('contact_us');
Route::get('/delivery-policy', 'HomeController@deliveryPolicy')->name('delivery_policy');
Route::get('/return-policy', 'HomeController@returnPolicy')->name('return_policy');
