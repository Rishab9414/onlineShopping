<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\HomeTheme;
use App\Models\PromoPopup;
use Illuminate\Database\Seeder;

class MarketingSeeder extends Seeder
{
    public function run(): void
    {
        if (Coupon::count() === 0) {
            Coupon::create([
                'code' => 'FESTIVE10',
                'description' => '10% off festive womenswear',
                'type' => 'percent',
                'value' => 10,
                'min_order_amount' => 1499,
                'max_discount' => 500,
                'usage_per_customer' => 1,
                'is_active' => true,
            ]);
        }

        if (PromoPopup::count() === 0) {
            PromoPopup::create([
                'title' => 'Festive Edit is here',
                'subtitle' => 'Use FESTIVE10 on orders above ₹1,499',
                'image' => 'images/fashion-hero.svg',
                'link_url' => url('/products'),
                'button_text' => 'Shop the Edit',
                'is_active' => true,
            ]);
        }

        if (HomeTheme::count() === 0) {
            HomeTheme::create([
                'name' => 'Boutique Maroon',
                'slug' => 'boutique-maroon',
                'preset' => 'default',
                'primary_color' => '#761737',
                'secondary_color' => '#32161f',
                'accent_color' => '#4f1028',
                'ticker_bg_color' => '#761737',
                'decoration' => 'none',
                'hero_overlay' => 'warm',
                'hero_badge_text' => 'New Season',
                'priority' => 1,
                'is_active' => true,
            ]);
        }
    }
}
