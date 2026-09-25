<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\Material;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\BuildsShopData;
use Tests\TestCase;

class AdminProductVariantTest extends TestCase
{
    use BuildsShopData, RefreshDatabase;

    public function test_admin_can_create_a_variable_clothing_product_with_variant_data(): void
    {
        $category = $this->category();
        $size = Size::create(['name' => 'M', 'status' => 'active']);
        $color = Color::create(['name' => 'Maroon', 'status' => 'active']);
        $material = Material::create(['name' => 'Cotton', 'status' => 'active']);

        $response = $this->withoutMiddleware()->post(route('admin.products.store'), [
            'name' => 'Maroon Cotton Kurti',
            'slug' => 'maroon-cotton-kurti',
            'sku' => 'KURTI-001',
            'category_id' => $category->id,
            'product_type' => 'variable',
            'product_condition' => 'new',
            'selling_price' => 999,
            'stock' => 0,
            'status' => 'published',
            'variants' => [[
                'sku' => 'KURTI-001-M-MAROON',
                'size_id' => $size->id,
                'color_id' => $color->id,
                'material_id' => $material->id,
                'price' => 1099,
                'stock' => 7,
                'reserved_stock' => 1,
                'weight' => 0.45,
            ]],
        ]);

        $product = Product::where('sku', 'KURTI-001')->firstOrFail();
        $response->assertRedirect(route('admin.products.edit', $product));
        $this->assertTrue($product->is_active);
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'sku' => 'KURTI-001-M-MAROON',
            'price' => 1099,
            'stock' => 7,
            'reserved_stock' => 1,
            'size_id' => $size->id,
            'color_id' => $color->id,
            'material_id' => $material->id,
        ]);
    }

    public function test_admin_rejects_duplicate_clothing_variant_combinations_atomically(): void
    {
        $category = $this->category();
        $size = Size::create(['name' => 'L '.Str::random(4), 'status' => 'active']);
        $color = Color::create(['name' => 'Navy '.Str::random(4), 'status' => 'active']);

        $payload = [
            'name' => 'Navy Rayon Kurti',
            'sku' => 'KURTI-DUPLICATE',
            'category_id' => $category->id,
            'product_type' => 'variable',
            'product_condition' => 'new',
            'selling_price' => 899,
            'stock' => 0,
            'status' => 'published',
            'variants' => [
                ['sku' => 'DUP-1', 'size_id' => $size->id, 'color_id' => $color->id, 'price' => 899, 'stock' => 2],
                ['sku' => 'DUP-2', 'size_id' => $size->id, 'color_id' => $color->id, 'price' => 899, 'stock' => 2],
            ],
        ];

        $this->withoutMiddleware()
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $payload)
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('variants.1');

        $this->assertDatabaseMissing('products', ['sku' => 'KURTI-DUPLICATE']);
        $this->assertDatabaseMissing('product_variants', ['sku' => 'DUP-1']);
    }
}
