<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => route('products.index'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => route('search.index'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => route('blog.index'), 'priority' => '0.6', 'changefreq' => 'weekly'],
        ]);

        foreach (BlogPost::published()->get(['slug', 'updated_at']) as $post) {
            $urls->push([
                'loc' => route('blog.show', $post),
                'lastmod' => $post->updated_at?->toAtomString(),
                'priority' => '0.5',
                'changefreq' => 'monthly',
            ]);
        }

        foreach (Category::where('is_active', true)->get() as $category) {
            $urls->push([
                'loc' => route('products.index', ['category' => $category->slug]),
                'priority' => '0.8',
                'changefreq' => 'weekly',
            ]);
        }

        foreach (Product::where('is_active', true)->get(['slug', 'updated_at']) as $product) {
            $urls->push([
                'loc' => route('products.show', $product),
                'lastmod' => $product->updated_at?->toAtomString(),
                'priority' => '0.7',
                'changefreq' => 'weekly',
            ]);
        }

        foreach (['privacy-policy', 'terms-and-conditions', 'shipping-policy', 'return-refund-policy', 'cancellation-policy'] as $slug) {
            $urls->push([
                'loc' => route('pages.show', $slug),
                'priority' => '0.3',
                'changefreq' => 'monthly',
            ]);
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
