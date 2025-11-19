<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;

//Trang chủ 
Route::get('/', [HomeController::class, 'home'])->name('home');

// Trang đăng ký
Route::get('/dang-ky', [RegisterController::class, 'showRegistrationForm'])
    ->name('register')->middleware('guest');

Route::post('/dang-ky', [RegisterController::class, 'register'])
    ->name('register.store')->middleware('guest');

Route::get('/dang-nhap', [LoginController::class, 'showLoginForm'])
    ->name('login')
    ->middleware('guest');

Route::post('/dang-nhap', [LoginController::class, 'login'])
    ->name('login.store')
    ->middleware(['guest', 'throttle:10,1']); 

Route::post('/dang-xuat', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// Frontend
Route::get('/danh-muc', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/danh-muc/{slug}', [CategoryController::class, 'show'])->name('categories.show');

Route::get('/san-pham/{slug}', [ProductController::class, 'show'])->name('products.show');

// Admin (dùng middleware group nếu có)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {

    // Category admin
    Route::resource('categories', AdminCategoryController::class);
    Route::post('categories/{category}/toggle', [AdminCategoryController::class, 'toggle'])
        ->name('categories.toggle');

    // Product admin
    Route::resource('products', AdminProductController::class);
    Route::post('products/{product}/toggle', [AdminProductController::class, 'toggle'])
        ->name('products.toggle');
});
