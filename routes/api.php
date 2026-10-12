<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Services\AppApiService;

Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    (new \App\Services\AppApiService())->clearCache();

    return response()->json(['message' => 'All caches and API local storage cleared successfully!']);
});

Route::post('/product-image/uploads', function (Request $request, AppApiService $apiService) {
    return $apiService->uploadProductImage($request);
});
