<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $ethnic = Category::where('slug', 'ethnic-wear')->first();

        $banners = [
            [
                'title' => 'Grace, woven for every day',
                'subtitle' => 'Elegant kurtas, sarees and occasion wear curated for women',
                'image' => 'images/fashion-hero.svg',
                'category_id' => $ethnic?->id,
                'link_url' => null,
                'button_text' => 'Explore the Collection',
                'sort_order' => 1,
            ],
            [
                'title' => 'Festive silhouettes',
                'subtitle' => 'New-season sarees and festive sets, ready to ship across India',
                'image' => 'images/fashion-hero.svg',
                'category_id' => null,
                'link_url' => null,
                'button_text' => 'Shop Festive Wear',
                'sort_order' => 2,
            ],
        ];

        Banner::query()->delete();

        foreach ($banners as $banner) {
            Banner::create($banner + ['is_active' => true]);
        }
    }
}
