<?php

use App\Http\Controllers\Api\CartController;
use Illuminate\Support\Facades\Route;


Route::resource('cart', CartController::class, [
    'parameters' => [
        'cart' => 'product:url'
    ]
]);

Route::post('cart/add/{product:url}',[CartController::class,'add']);
Route::post('cart/coupon/apply', [CartController::class, 'applyCouponCode']);
Route::delete('cart/coupon/delete', [CartController::class, 'removeCouponCode']);
