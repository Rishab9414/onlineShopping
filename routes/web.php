<?php

use App\Http\Controllers\Cron\PaymentSyncController;
use App\Http\Controllers\Shop\AccountController;
use App\Http\Controllers\Shop\BlogController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\CouponController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\OrderController;
use App\Http\Controllers\Shop\PageController;
use App\Http\Controllers\Shop\ProductController;
use App\Http\Controllers\Shop\RazorpayPaymentController;
use App\Http\Controllers\Shop\ReviewController;
use App\Http\Controllers\Shop\SearchController;
use App\Http\Controllers\Shop\WishlistController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/razorpay', [WebhookController::class, 'razorpay'])->name('webhooks.razorpay');

Route::get('/cron/sync-payments', [PaymentSyncController::class, 'run'])->name('cron.sync-payments');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/search', [SearchController::class, 'index'])->name('search.index');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/pages/{slug}', [PageController::class, 'show'])->name('pages.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

Route::post('/cart/coupon', [CouponController::class, 'apply'])->name('cart.coupon.apply');
Route::delete('/cart/coupon', [CouponController::class, 'remove'])->name('cart.coupon.remove');
Route::post('/cart/{product:slug}', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/check-pincode', [CheckoutController::class, 'checkPincode'])->name('checkout.check-pincode');
    Route::post('/checkout/shipping-quote', [CheckoutController::class, 'shippingQuote'])->name('checkout.shipping-quote');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders/payment/verify', [RazorpayPaymentController::class, 'verify'])->name('orders.payment.verify');
    Route::get('/orders/{order}/payment', [RazorpayPaymentController::class, 'show'])->name('orders.payment');
    Route::get('/orders/{order}/confirmation', [OrderController::class, 'confirmation'])->name('orders.confirmation');
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    Route::get('/dashboard', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/account/profile', [AccountController::class, 'profile'])->name('account.profile');
    Route::patch('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::get('/account/addresses', [AccountController::class, 'addresses'])->name('account.addresses');
    Route::post('/account/addresses', [AccountController::class, 'storeAddress'])->name('account.addresses.store');
    Route::put('/account/addresses/{address}', [AccountController::class, 'updateAddress'])->name('account.addresses.update');
    Route::delete('/account/addresses/{address}', [AccountController::class, 'destroyAddress'])->name('account.addresses.destroy');
    Route::patch('/account/addresses/{address}/default', [AccountController::class, 'defaultAddress'])->name('account.addresses.default');
    Route::get('/account/wishlist', [AccountController::class, 'wishlist'])->name('account.wishlist');
    Route::post('/wishlist/{product:slug}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/wishlist/{product:slug}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    Route::get('/account/reviews', [AccountController::class, 'reviews'])->name('account.reviews');
});

require __DIR__.'/auth.php';
