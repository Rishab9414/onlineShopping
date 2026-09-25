<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Manufacturer;
use App\Models\Material;
use App\Models\Setting;
use App\Models\Size;
use App\Models\SizeChart;
use App\Models\Supplier;
use App\Models\Tax;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCategories();
        $this->seedBrands();
        $this->seedManufacturers();
        $this->seedSuppliers();
        $this->seedTaxes();
        $this->seedUnits();
        $this->seedSizes();
        $this->seedColors();
        $this->seedMaterials();
        $this->seedSizeChart();
        $this->seedSettings();
    }

    private function seedCategories(): void
    {
        $categories = [
            'Kurtis' => 'Everyday and occasion-ready kurtis for women.',
            'Readymade Blouses' => 'Ready-to-wear blouses in versatile cuts and fabrics.',
            'Designer Blouses' => 'Statement blouses with refined festive detailing.',
            'Sarees' => 'Elegant sarees for work, celebrations, and special occasions.',
            'Dresses' => 'Contemporary women’s dresses with comfortable silhouettes.',
            'Ethnic Wear' => 'Coordinated ethnic styles designed for modern wardrobes.',
            'Traditional Wear' => 'Timeless women’s clothing inspired by Indian craft.',
            'Other Clothing Products' => 'Essential women’s clothing and complementary apparel.',
        ];

        foreach ($categories as $name => $description) {
            $order = array_search($name, array_keys($categories), true) + 1;
            Category::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $description,
                'seo_title' => $name.' for Women | Ridhi Sidhi Garments',
                'meta_description' => $description,
                'display_order' => $order,
                'featured' => $order <= 4,
                'show_in_menu' => true,
                'status' => 'active',
                'is_active' => true,
            ]);
        }
    }

    private function seedBrands(): void
    {
        foreach ([
            'Ridhi Sidhi' => 'Signature women’s apparel from Ridhi Sidhi Garments.',
            'Aarohi' => 'Refined contemporary ethnic wear.',
            'Meher' => 'Celebration-ready sarees and blouses.',
            'Tavisha' => 'Easy, modern silhouettes for everyday dressing.',
        ] as $name => $description) {
            Brand::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $description,
                'country' => 'India',
                'status' => 'active',
            ]);
        }
    }

    private function seedManufacturers(): void
    {
        Manufacturer::create([
            'name' => 'Ridhi Sidhi Garments',
            'address' => 'India',
            'status' => 'active',
        ]);
    }

    private function seedSuppliers(): void
    {
        Supplier::create([
            'name' => 'Ridhi Sidhi Apparel Supply',
            'address' => 'India',
            'country' => 'India',
            'status' => 'active',
        ]);
    }

    private function seedTaxes(): void
    {
        Tax::create(['name' => 'Apparel GST 5%', 'percentage' => 5, 'hsn_code' => '6204', 'description' => 'GST rate for eligible apparel.', 'status' => 'active']);
        Tax::create(['name' => 'Apparel GST 12%', 'percentage' => 12, 'hsn_code' => '6204', 'description' => 'GST rate for eligible premium apparel.', 'status' => 'active']);
    }

    private function seedUnits(): void
    {
        foreach ([['Piece', 'PC'], ['Set', 'SET']] as [$name, $shortName]) {
            Unit::create(['name' => $name, 'short_name' => $shortName, 'status' => 'active']);
        }
    }

    private function seedSizes(): void
    {
        foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $i => $name) {
            Size::create(['name' => $name, 'display_order' => $i + 1, 'status' => 'active']);
        }
    }

    private function seedColors(): void
    {
        $colors = [
            ['Ivory', '#FFFFF0'], ['Black', '#111111'], ['Maroon', '#7F1D1D'],
            ['Emerald', '#047857'], ['Navy', '#1E3A8A'], ['Rose Pink', '#F9A8D4'],
            ['Mustard', '#D4A017'], ['Wine', '#722F37'],
        ];
        foreach ($colors as [$name, $hex]) {
            Color::create(['name' => $name, 'hex_code' => $hex, 'status' => 'active']);
        }
    }

    private function seedMaterials(): void
    {
        foreach ([
            'Cotton' => 'Breathable natural cotton fabric.',
            'Rayon' => 'Soft, fluid rayon fabric.',
            'Silk Blend' => 'Silk-blend fabric with a graceful sheen.',
            'Georgette' => 'Lightweight, flowing georgette.',
            'Chanderi' => 'Lightweight traditional Chanderi fabric.',
            'Viscose' => 'Smooth and comfortable viscose fabric.',
        ] as $name => $description) {
            Material::create(['name' => $name, 'description' => $description, 'status' => 'active']);
        }
    }

    private function seedSizeChart(): void
    {
        $chart = SizeChart::create([
            'name' => 'Women’s Apparel Standard',
            'unit' => 'in',
            'instructions' => 'Measure around the fullest part while keeping the tape comfortably level.',
            'is_active' => true,
        ]);

        $measurements = [
            'XS' => [32, 34, 26, 28],
            'S' => [34, 36, 28, 30],
            'M' => [36, 38, 30, 32],
            'L' => [38, 40, 32, 34],
            'XL' => [40, 42, 34, 36],
            'XXL' => [42, 44, 36, 38],
        ];

        foreach ($measurements as $label => [$bustMin, $bustMax, $waistMin, $waistMax]) {
            $size = Size::where('name', $label)->firstOrFail();
            foreach ([['Bust', $bustMin, $bustMax], ['Waist', $waistMin, $waistMax]] as $order => [$name, $min, $max]) {
                $chart->measurements()->create([
                    'size_id' => $size->id,
                    'size_label' => $label,
                    'measurement' => $name,
                    'min_value' => $min,
                    'max_value' => $max,
                    'sort_order' => ($size->display_order * 10) + $order,
                ]);
            }
        }

        Category::whereIn('slug', ['kurtis', 'readymade-blouses', 'designer-blouses', 'dresses', 'ethnic-wear', 'traditional-wear'])
            ->update(['size_chart_id' => $chart->id]);
    }

    private function seedSettings(): void
    {
        foreach ([
            'cod_enabled' => true,
            'razorpay_enabled' => false,
            'razorpay_mock' => true,
            'shipping_method' => 'flat',
            'flat_shipping_charge' => 79,
            'free_shipping_enabled' => false,
            'free_shipping_min_amount' => 2500,
            'default_tax_included' => true,
            'default_tax_rate' => 5,
            'return_window_days' => 7,
            'business_name' => 'Ridhi Sidhi Garments',
            'business_tagline' => 'Elegant ethnic and contemporary womenswear',
            'business_about' => 'Elegant ethnic and contemporary womenswear, thoughtfully curated and delivered across India.',
            'business_email' => 'support@ridhisidhi.test',
            'business_phone' => '9743663260',
            'business_address' => '',
            'business_city' => '',
            'business_state' => '',
            'business_pincode' => '',
            'business_country' => 'India',
            'business_gstin' => '',
            'business_logo' => '',
        ] as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
