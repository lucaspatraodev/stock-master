<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/can-register', [AuthController::class, 'canRegister']);
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('products', ProductController::class)->only([
        'index',
        'store',
        'update',
    ]);
    Route::apiResource('users', UserController::class)->only([
        'index',
        'store',
        'update',
        'destroy',
    ]);
    Route::patch('/products/{product}/inactivate', [ProductController::class, 'inactivate']);
    Route::delete('/products/{product}/images/{image}', [ProductController::class, 'destroyImage']);
    Route::patch('/products/{product}/images/reorder', [ProductController::class, 'reorderImages']);
});
