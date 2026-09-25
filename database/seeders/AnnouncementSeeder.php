<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        if (Announcement::count() > 0) {
            return;
        }

        $items = [
            ['text' => 'Complimentary shipping on qualifying orders', 'icon' => '🚚', 'position' => 'top_bar', 'type' => 'info', 'sort_order' => 1],
            ['text' => 'Thoughtfully curated womenswear', 'icon' => '✦', 'position' => 'top_bar', 'type' => 'info', 'sort_order' => 2],
            ['text' => 'Festive Edit is live — use code FESTIVE10', 'icon' => '🎉', 'position' => 'ticker', 'type' => 'promo', 'sort_order' => 1, 'link_url' => '/products'],
            ['text' => 'Easy exchanges on unused garments', 'icon' => '↩', 'position' => 'ticker', 'type' => 'trust', 'sort_order' => 2],
            ['text' => 'COD and Razorpay available', 'icon' => '💳', 'position' => 'ticker', 'type' => 'trust', 'sort_order' => 3],
            ['text' => 'Pan-India delivery', 'icon' => '🇮🇳', 'position' => 'ticker', 'type' => 'trust', 'sort_order' => 4],
        ];

        foreach ($items as $item) {
            Announcement::create($item + ['is_active' => true]);
        }
    }
}
