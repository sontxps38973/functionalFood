<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;

Route::prefix('v1')->group(function () {
    // Category CRUD
    Route::apiResource('categories', CategoryController::class);

    // Product CRUD
    Route::apiResource('products', ProductController::class);

    // Product search
    Route::get('products-search', [ProductController::class, 'search']);

    // Product filter
    Route::get('products-filter', [ProductController::class, 'filter']);
});
Route::get('/test-api', function () {
    return 'API đang hoạt động';
});
