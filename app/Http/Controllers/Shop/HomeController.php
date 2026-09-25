<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HomeReel;
use App\Models\Product;
use App\Models\PromoPopup;
use App\Models\Setting;
use App\Services\SeoService;

class HomeController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::withStorefront()
            ->where('is_active', true)
            ->where('featured', true)
            ->latest()
            ->take(8)
            ->get();

        $trendingProducts = Product::withStorefront()
            ->where('is_active', true)
            ->where('trending', true)
            ->latest()
            ->take(4)
            ->get();

        $newArrivals = Product::withStorefront()
            ->where('is_active', true)
            ->where('new_arrival', true)
            ->latest()
            ->take(4)
            ->get();

        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('display_order')
            ->take(8)
            ->get();

        $brands = Brand::where('status', 'active')->orderBy('name')->take(9)->get();

        $banners = Banner::with('category')->active()->get();
        $promoPopup = PromoPopup::current();

        $homeReelsEnabled = Setting::homeReelsEnabled();
        $homeReelsAutoplay = Setting::homeReelsAutoplay();
        $homeReels = $homeReelsEnabled
            ? HomeReel::with('category')->active()->get()
            : collect();

        $seo = app(SeoService::class)->forHome();

        return view('shop.home', compact(
            'seo',
            'promoPopup',
            'featuredProducts',
            'trendingProducts',
            'newArrivals',
            'categories',
            'brands',
            'banners',
            'homeReelsEnabled',
            'homeReelsAutoplay',
            'homeReels',
        ));
    }
}
