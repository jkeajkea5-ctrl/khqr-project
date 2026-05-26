<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\UserAuthController;
use App\Http\Controllers\UserOrderController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminAccountController;
use App\Http\Controllers\Admin\CategoryAdminController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\SlideAdminController;
use App\Http\Controllers\Admin\SetupController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Models\Admin;

/*
|--------------------------------------------------------------------------
| SHOP
|--------------------------------------------------------------------------
*/
Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/product/{id}', [ProductController::class, 'show'])->name('product.show');
Route::get('/media/{path}', [MediaController::class, 'show'])->where('path', '.*')->name('media.show');

/*
|--------------------------------------------------------------------------
| USER AUTH + ORDERS
|--------------------------------------------------------------------------
*/
Route::get('/user/login', [UserAuthController::class, 'showLogin'])->name('user.login');
Route::post('/user/login', [UserAuthController::class, 'login'])->name('user.login.submit');
Route::get('/user/register', [UserAuthController::class, 'showRegister'])->name('user.register');
Route::post('/user/register', [UserAuthController::class, 'register'])->name('user.register.submit');
Route::post('/user/logout', [UserAuthController::class, 'logout'])->name('user.logout');
Route::get('/user/orders', [UserOrderController::class, 'index'])->name('user.orders');
Route::middleware('user')->group(function () {
    Route::get('/user/profile', [UserProfileController::class, 'show'])->name('user.profile');
    Route::put('/user/profile', [UserProfileController::class, 'update'])->name('user.profile.update');
});

/*
|--------------------------------------------------------------------------
| CART
|--------------------------------------------------------------------------
*/
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::put('/cart/update/{key}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{key}', [CartController::class, 'remove'])->name('cart.remove');
Route::match(['get','post'], '/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

/*
|--------------------------------------------------------------------------
| PAYMENT (KHQR)
|--------------------------------------------------------------------------
*/
Route::middleware('user')->group(function () {
    Route::match(['get','post'], '/checkout/cart', [PaymentController::class, 'checkoutCart'])->name('checkout.cart');
    Route::post('/checkout/{id}', [PaymentController::class, 'checkout'])->name('checkout');
    Route::post('/verify', [PaymentController::class, 'verifyTransaction'])->name('verify.transaction');
    Route::get('/invoice/{order}', [PaymentController::class, 'invoice'])->name('invoice');
});

/*
|--------------------------------------------------------------------------
| AUTH (CUSTOM)
|--------------------------------------------------------------------------
*/
Route::get('/admin/login', [LoginController::class, 'show'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');
Route::get('/admin/setup', [SetupController::class, 'show'])->name('admin.setup');
Route::post('/admin/setup', [SetupController::class, 'store'])->name('admin.setup.store');
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::middleware('admin.role:'.implode(',', [
            Admin::ROLE_ADMIN,
            Admin::ROLE_STOCK_CONTROLLER,
            Admin::ROLE_EMPLOYEE,
        ]))->group(function () {
            Route::get('/dashboard', [OrderAdminController::class, 'dashboard'])->name('dashboard');
            Route::get('/users', [UserAdminController::class, 'index'])->name('users.index');
            Route::get('/products', [ProductAdminController::class, 'index'])->name('products.index');
            Route::get('/categories', [CategoryAdminController::class, 'index'])->name('categories.index');
            Route::get('/orders', [OrderAdminController::class, 'index'])->name('orders.index');
            Route::get('/payments/paid', [OrderAdminController::class, 'paid'])->name('payments.paid');
        });

        Route::middleware('admin.role:'.implode(',', [
            Admin::ROLE_ADMIN,
            Admin::ROLE_STOCK_CONTROLLER,
        ]))->group(function () {
            Route::get('/products/create', [ProductAdminController::class, 'create'])->name('products.create');
            Route::post('/products', [ProductAdminController::class, 'store'])->name('products.store');
            Route::get('/products/{product}/edit', [ProductAdminController::class, 'edit'])->name('products.edit');
            Route::put('/products/{product}', [ProductAdminController::class, 'update'])->name('products.update');

            Route::get('/categories/create', [CategoryAdminController::class, 'create'])->name('categories.create');
            Route::post('/categories', [CategoryAdminController::class, 'store'])->name('categories.store');
            Route::get('/categories/{category}/edit', [CategoryAdminController::class, 'edit'])->name('categories.edit');
            Route::put('/categories/{category}', [CategoryAdminController::class, 'update'])->name('categories.update');
        });

        Route::middleware('admin.role:'.Admin::ROLE_ADMIN)->group(function () {
            Route::get('/admins', [AdminAccountController::class, 'index'])->name('admins.index');
            Route::post('/admins', [AdminAccountController::class, 'store'])->name('admins.store');
            Route::get('/admins/{admin}/edit', [AdminAccountController::class, 'edit'])->name('admins.edit');
            Route::put('/admins/{admin}', [AdminAccountController::class, 'update'])->name('admins.update');
            Route::delete('/admins/{admin}', [AdminAccountController::class, 'destroy'])->name('admins.destroy');

            Route::delete('/products/{product}', [ProductAdminController::class, 'destroy'])->name('products.destroy');
            Route::delete('/categories/{category}', [CategoryAdminController::class, 'destroy'])->name('categories.destroy');

            Route::get('/slides', [SlideAdminController::class, 'index'])->name('slides.index');
            Route::get('/slides/create', [SlideAdminController::class, 'create'])->name('slides.create');
            Route::post('/slides', [SlideAdminController::class, 'store'])->name('slides.store');
            Route::get('/slides/{slide}/edit', [SlideAdminController::class, 'edit'])->name('slides.edit');
            Route::put('/slides/{slide}', [SlideAdminController::class, 'update'])->name('slides.update');
            Route::delete('/slides/{slide}', [SlideAdminController::class, 'destroy'])->name('slides.destroy');
        });
    });
