<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentController;

Route::get('/', function () {
    return view('welcome');
});

// VNPay Return URL
Route::get('/payment/return', [PaymentController::class, 'vnpayReturn']);
