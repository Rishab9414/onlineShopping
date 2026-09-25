<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        if (BlogPost::count() > 0) {
            return;
        }

        $posts = [
            [
                'title' => 'How to Style a Cotton Kurta for Everyday Wear',
                'slug' => 'style-cotton-kurta-everyday',
                'excerpt' => 'Simple layering and jewellery ideas to take a cotton kurta from home to a day out.',
                'content' => "A well-cut cotton kurta is the easiest everyday piece in an Indian wardrobe.\n\n## Keep the base clean\nChoose a solid or lightly printed kurta in breathable cotton.\n\n## Add one accent\nA stole, jhumkas, or a structured bag is enough.\n\n## Pair with the right bottom\nStraight pants for work, palazzos for ease, and churidar when you want a traditional line.",
                'meta_title' => 'How to Style a Cotton Kurta | Ridhi Sidhi Garments',
                'meta_description' => 'Everyday kurta styling ideas for work, errands, and relaxed festive days.',
                'meta_keywords' => 'cotton kurta styling, everyday ethnic wear, womens kurta guide',
                'status' => 'published',
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Choosing a Saree Fabric for Indian Weather',
                'slug' => 'choosing-saree-fabric-indian-weather',
                'excerpt' => 'Cotton, georgette, and silk — what to wear in summer, monsoon, and wedding season.',
                'content' => "Fabric decides how a saree feels after an hour of wearing.\n\n## Summer\nCotton and linen-blend sarees stay cooler.\n\n## Monsoon\nGeorgette drapes well and dries faster than heavy silk.\n\n## Weddings\nSilk and tissue hold embroidery and jewellery better.",
                'meta_title' => 'Saree Fabric Guide for Indian Weather | Ridhi Sidhi Garments',
                'meta_description' => 'Pick the right saree fabric for summer, monsoon, and wedding season.',
                'meta_keywords' => 'saree fabric guide, cotton saree, georgette saree, wedding silk',
                'status' => 'published',
                'published_at' => now()->subDays(2),
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::create($post);
        }
    }
}
