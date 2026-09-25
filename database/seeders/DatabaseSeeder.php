<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            MasterDataSeeder::class,
            RbacSyncSeeder::class,
            ProductSeeder::class,
            ProductVariantSeeder::class,
            CustomerSeeder::class,
            OrderSeeder::class,
            BannerSeeder::class,
            AnnouncementSeeder::class,
            BlogPostSeeder::class,
            MarketingSeeder::class,
        ]);
    }
}
