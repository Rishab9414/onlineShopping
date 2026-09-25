<?php

namespace Database\Seeders;

use App\Models\Color;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Database\Seeder;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = [
            'RSG-KUR-001' => ['Chanderi', ['Ivory', 'Maroon'], ['S', 'M', 'L', 'XL']],
            'RSG-KUR-002' => ['Rayon', ['Rose Pink', 'Navy'], ['XS', 'S', 'M', 'L', 'XL', 'XXL']],
            'RSG-RBL-001' => ['Silk Blend', ['Maroon', 'Black'], ['S', 'M', 'L', 'XL']],
            'RSG-DBL-001' => ['Georgette', ['Wine', 'Emerald'], ['S', 'M', 'L', 'XL']],
            'RSG-SAR-001' => ['Georgette', ['Emerald', 'Wine'], [null]],
            'RSG-DRS-001' => ['Viscose', ['Navy', 'Black'], ['XS', 'S', 'M', 'L', 'XL']],
            'RSG-ETH-001' => ['Cotton', ['Mustard', 'Maroon'], ['S', 'M', 'L', 'XL', 'XXL']],
            'RSG-TRD-001' => ['Silk Blend', ['Maroon', 'Emerald'], ['S', 'M', 'L', 'XL']],
            'RSG-OTH-001' => ['Cotton', ['Black', 'Navy'], ['XS', 'S', 'M', 'L', 'XL']],
        ];

        $created = 0;
        foreach ($definitions as $productSku => [$materialName, $colorNames, $sizeNames]) {
            $product = Product::where('sku', $productSku)->firstOrFail();
            $material = Material::where('name', $materialName)->firstOrFail();

            foreach ($colorNames as $colorIndex => $colorName) {
                $color = Color::where('name', $colorName)->firstOrFail();
                foreach ($sizeNames as $sizeIndex => $sizeName) {
                    $size = $sizeName ? Size::where('name', $sizeName)->firstOrFail() : null;
                    $suffix = strtoupper(str_replace(' ', '', substr($colorName, 0, 3))).'-'.($sizeName ?? 'FS');

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $productSku.'-'.$suffix,
                        'color_id' => $color->id,
                        'size_id' => $size?->id,
                        'material_id' => $material->id,
                        'price' => (float) $product->price,
                        'stock' => 8 + (($colorIndex + $sizeIndex) % 7),
                        'reserved_stock' => 0,
                        'weight' => $product->weight,
                        'image' => null,
                        'is_active' => true,
                    ]);
                    $created++;
                }
            }
        }

        $this->command->info("Created {$created} valid size, color, and fabric variants.");
    }
}
