<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\SizeChart;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Ivory Chanderi Embroidered Kurti',
                'sku' => 'RSG-KUR-001', 'category' => 'Kurtis', 'brand' => 'Ridhi Sidhi',
                'material' => 'Chanderi', 'colors' => ['Ivory', 'Maroon'], 'sizes' => ['S', 'M', 'L', 'XL'],
                'mrp' => 1899, 'price' => 1499, 'hsn' => '62063000',
                'description' => 'An elegant straight kurti with delicate thread embroidery and a soft cotton lining.',
                'features' => ['Fabric: Chanderi', 'Fit: Straight', 'Neckline: Round neck', 'Sleeve: Three-quarter', 'Pattern: Embroidered', 'Occasion: Festive', 'Care: Gentle hand wash'],
                'featured' => true, 'new_arrival' => true,
            ],
            [
                'name' => 'Rose Pink Printed Rayon Kurti',
                'sku' => 'RSG-KUR-002', 'category' => 'Kurtis', 'brand' => 'Aarohi',
                'material' => 'Rayon', 'colors' => ['Rose Pink', 'Navy'], 'sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
                'mrp' => 1199, 'price' => 899, 'hsn' => '62063000',
                'description' => 'A fluid A-line kurti with a cheerful floral print for effortless everyday dressing.',
                'features' => ['Fabric: Rayon', 'Fit: A-line', 'Neckline: V-neck', 'Sleeve: Three-quarter', 'Pattern: Floral print', 'Occasion: Casual', 'Care: Machine wash cold'],
                'best_seller' => true,
            ],
            [
                'name' => 'Maroon Sweetheart Readymade Blouse',
                'sku' => 'RSG-RBL-001', 'category' => 'Readymade Blouses', 'brand' => 'Meher',
                'material' => 'Silk Blend', 'colors' => ['Maroon', 'Black'], 'sizes' => ['S', 'M', 'L', 'XL'],
                'mrp' => 1299, 'price' => 999, 'hsn' => '62114290',
                'description' => 'A polished ready-to-wear blouse with princess seams and a flattering sweetheart neckline.',
                'features' => ['Fabric: Silk Blend', 'Fit: Tailored', 'Neckline: Sweetheart', 'Sleeve: Elbow', 'Pattern: Solid', 'Occasion: Festive', 'Care: Dry clean'],
                'featured' => true,
            ],
            [
                'name' => 'Wine Sequin Designer Blouse',
                'sku' => 'RSG-DBL-001', 'category' => 'Designer Blouses', 'brand' => 'Meher',
                'material' => 'Georgette', 'colors' => ['Wine', 'Emerald'], 'sizes' => ['S', 'M', 'L', 'XL'],
                'mrp' => 2399, 'price' => 1899, 'hsn' => '62114300',
                'description' => 'A celebration blouse finished with tonal sequins, soft lining, and a sculpted fit.',
                'features' => ['Fabric: Georgette', 'Fit: Tailored', 'Neckline: Boat neck', 'Sleeve: Short', 'Pattern: Sequin embellished', 'Occasion: Wedding', 'Care: Dry clean only'],
                'trending' => true,
            ],
            [
                'name' => 'Emerald Georgette Celebration Saree',
                'sku' => 'RSG-SAR-001', 'category' => 'Sarees', 'brand' => 'Meher',
                'material' => 'Georgette', 'colors' => ['Emerald', 'Wine'], 'sizes' => [],
                'mrp' => 2999, 'price' => 2299, 'hsn' => '62114210',
                'description' => 'A graceful georgette saree with a finely embellished border and matching blouse piece.',
                'features' => ['Fabric: Georgette', 'Fit: Free size', 'Neckline: Not applicable', 'Sleeve: Unstitched blouse piece', 'Pattern: Embellished border', 'Occasion: Celebration', 'Care: Dry clean'],
                'featured' => true, 'best_seller' => true,
            ],
            [
                'name' => 'Navy Wrap Midi Dress',
                'sku' => 'RSG-DRS-001', 'category' => 'Dresses', 'brand' => 'Tavisha',
                'material' => 'Viscose', 'colors' => ['Navy', 'Black'], 'sizes' => ['XS', 'S', 'M', 'L', 'XL'],
                'mrp' => 1999, 'price' => 1599, 'hsn' => '62044390',
                'description' => 'A softly draped wrap midi dress designed for comfortable day-to-evening wear.',
                'features' => ['Fabric: Viscose', 'Fit: Regular', 'Neckline: Wrap V-neck', 'Sleeve: Flutter', 'Pattern: Solid', 'Occasion: Smart casual', 'Care: Gentle machine wash'],
                'new_arrival' => true,
            ],
            [
                'name' => 'Mustard Cotton Kurta Palazzo Set',
                'sku' => 'RSG-ETH-001', 'category' => 'Ethnic Wear', 'brand' => 'Ridhi Sidhi',
                'material' => 'Cotton', 'colors' => ['Mustard', 'Maroon'], 'sizes' => ['S', 'M', 'L', 'XL', 'XXL'],
                'mrp' => 2499, 'price' => 1899, 'hsn' => '62042200', 'unit' => 'Set',
                'description' => 'A breathable cotton kurta and palazzo set with artisanal block-inspired motifs.',
                'features' => ['Fabric: Cotton', 'Fit: Relaxed', 'Neckline: Mandarin collar', 'Sleeve: Three-quarter', 'Pattern: Block inspired print', 'Occasion: Day festive', 'Care: Hand wash separately'],
                'featured' => true,
            ],
            [
                'name' => 'Maroon Anarkali Dupatta Set',
                'sku' => 'RSG-TRD-001', 'category' => 'Traditional Wear', 'brand' => 'Ridhi Sidhi',
                'material' => 'Silk Blend', 'colors' => ['Maroon', 'Emerald'], 'sizes' => ['S', 'M', 'L', 'XL'],
                'mrp' => 3499, 'price' => 2799, 'hsn' => '62042990', 'unit' => 'Set',
                'description' => 'A flowing Anarkali set with a coordinated dupatta and understated zari accents.',
                'features' => ['Fabric: Silk Blend', 'Fit: Flared', 'Neckline: Round neck', 'Sleeve: Full', 'Pattern: Woven accents', 'Occasion: Traditional celebration', 'Care: Dry clean'],
                'trending' => true,
            ],
            [
                'name' => 'Black Everyday Cotton Leggings',
                'sku' => 'RSG-OTH-001', 'category' => 'Other Clothing Products', 'brand' => 'Tavisha',
                'material' => 'Cotton', 'colors' => ['Black', 'Navy'], 'sizes' => ['XS', 'S', 'M', 'L', 'XL'],
                'mrp' => 799, 'price' => 599, 'hsn' => '61046200',
                'description' => 'Soft stretch-cotton leggings with a comfortable waistband for everyday layering.',
                'features' => ['Fabric: Cotton', 'Fit: Slim', 'Neckline: Not applicable', 'Sleeve: Not applicable', 'Pattern: Solid', 'Occasion: Everyday', 'Care: Machine wash cold'],
                'best_seller' => true,
            ],
        ];

        $brands = Brand::pluck('id', 'name');
        $categories = Category::pluck('id', 'name');
        $manufacturer = Manufacturer::firstOrFail();
        $supplier = Supplier::firstOrFail();
        $unitIds = Unit::pluck('id', 'name');
        $sizeChart = SizeChart::first();

        foreach ($products as $p) {
            $slug = Str::slug($p['name']);
            $tax = Tax::where('percentage', $p['price'] > 1000 ? 12 : 5)->firstOrFail();
            $product = Product::create([
                'category_id' => $categories[$p['category']],
                'size_chart_id' => $p['sizes'] !== [] ? $sizeChart?->id : null,
                'brand_id' => $brands[$p['brand']] ?? null,
                'manufacturer_id' => $manufacturer->id,
                'supplier_id' => $supplier->id,
                'tax_id' => $tax->id,
                'unit_id' => $unitIds[$p['unit'] ?? 'Piece'],
                'name' => $p['name'],
                'short_name' => Str::limit($p['name'], 30),
                'slug' => $slug,
                'sku' => $p['sku'],
                'product_type' => 'variable',
                'product_condition' => 'new',
                'hsn_code' => $p['hsn'],
                'country_of_origin' => 'India',
                'return_days' => 7,
                'replace_days' => 7,
                'min_order_qty' => 1,
                'short_description' => $p['description'],
                'description' => $p['description'],
                'long_description' => $p['description'].' Thoughtfully finished for comfort, repeat wear, and an elegant drape.',
                'specification' => implode(' · ', $p['features']),
                'care_instructions' => Str::after($p['features'][array_key_last($p['features'])], 'Care: '),
                'box_contents' => ($p['unit'] ?? 'Piece') === 'Set' ? 'One coordinated apparel set' : 'One garment',
                'purchase_price' => round($p['price'] * 0.58, 2),
                'landing_cost' => round($p['price'] * 0.65, 2),
                'selling_price' => $p['price'],
                'mrp' => $p['mrp'],
                'price' => $p['price'],
                'compare_price' => $p['mrp'],
                'discount_percent' => round((($p['mrp'] - $p['price']) / $p['mrp']) * 100, 1),
                'offer_price' => $p['price'],
                'tax_included' => true,
                'stock' => 0,
                'reserved_stock' => 0,
                'low_stock_alert' => 4,
                'warehouse' => 'Ridhi Sidhi Main',
                'primary_image' => null,
                'gallery' => [],
                'weight' => ($p['unit'] ?? 'Piece') === 'Set' ? 0.85 : 0.45,
                'shipping_class' => 'apparel',
                'shipping_cost' => 79,
                'free_shipping' => false,
                'cod_available' => true,
                'status' => 'published',
                'is_active' => true,
                'featured' => $p['featured'] ?? false,
                'trending' => $p['trending'] ?? false,
                'new_arrival' => $p['new_arrival'] ?? false,
                'best_seller' => $p['best_seller'] ?? false,
                'meta_title' => $p['name'].' | '.config('app.name'),
                'meta_keywords' => strtolower($p['category'].', women clothing, '.$p['material']),
                'meta_description' => $p['description'],
            ]);

            foreach ($p['features'] as $order => $feature) {
                $product->features()->create(['feature' => $feature, 'sort_order' => $order + 1]);
            }
            foreach (array_unique([Str::slug($p['category']), Str::slug($p['material']), 'women']) as $tag) {
                $product->tags()->create(['tag' => $tag]);
            }
        }

        $this->command->info('Created '.count($products).' Ridhi Sidhi women’s apparel products.');
    }
}
