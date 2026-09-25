<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Services\ProductReviewService;
use App\Services\SeoService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::withStorefront()->where('is_active', true);

        if ($request->filled('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(12);
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        $activeCategory = $request->filled('category')
            ? Category::where('slug', $request->category)->first()
            : null;

        $seo = app(SeoService::class)->forProducts($activeCategory, $request->search);

        return view('shop.products.index', compact('products', 'categories', 'seo', 'activeCategory'));
    }

    public function show(Product $product)
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'brand', 'approvedReviews.customer', 'variants.color', 'variants.size', 'variants.material']);

        $seo = app(SeoService::class)->forProduct($product);
        $reviewSummary = app(ProductReviewService::class)->summaryForProduct($product);
        $canReview = false;
        $reviewOrderItemId = null;

        if (auth()->check()) {
            $customer = Customer::where('user_id', auth()->id())->first();
            if ($customer) {
                $orderItem = app(ProductReviewService::class)->findReviewableOrderItem($product, $customer);
                $canReview = $orderItem !== null;
                $reviewOrderItemId = $orderItem?->id;
            }
        }

        $relatedProducts = Product::withStorefront()
            ->where('is_active', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return view('shop.products.show', compact(
            'product',
            'relatedProducts',
            'seo',
            'reviewSummary',
            'canReview',
            'reviewOrderItemId',
        ));
    }
}
