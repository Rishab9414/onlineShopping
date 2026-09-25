<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsShopData;
use Tests\TestCase;

class ClothingCatalogTest extends TestCase
{
    use BuildsShopData, RefreshDatabase;

    public function test_home_displays_active_clothing_categories_and_featured_products(): void
    {
        $category = $this->category(['name' => 'Designer Blouses', 'slug' => 'designer-blouses']);
        $product = $this->product([
            'category_id' => $category->id,
            'name' => 'Wine Sequin Designer Blouse',
            'slug' => 'wine-sequin-designer-blouse',
            'featured' => true,
        ]);
        $hidden = $this->product(['name' => 'Hidden Garment', 'is_active' => false, 'featured' => true]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Designer Blouses')
            ->assertSee($product->name)
            ->assertDontSee($hidden->name)
            ->assertSee('womenswear', false);
    }

    public function test_catalog_filters_products_by_clothing_category(): void
    {
        $kurtis = $this->category(['name' => 'Kurtis', 'slug' => 'kurtis']);
        $sarees = $this->category(['name' => 'Sarees', 'slug' => 'sarees']);
        $kurti = $this->product(['category_id' => $kurtis->id, 'name' => 'Ivory Chanderi Kurti']);
        $saree = $this->product(['category_id' => $sarees->id, 'name' => 'Emerald Georgette Saree']);

        $this->get('/products?category=kurtis')
            ->assertOk()
            ->assertSee($kurti->name)
            ->assertDontSee($saree->name)
            ->assertSee('Shop the Collection');
    }

    public function test_catalog_search_only_returns_active_matching_clothing(): void
    {
        $matching = $this->product(['name' => 'Rose Pink Rayon Kurti']);
        $other = $this->product(['name' => 'Black Cotton Leggings']);
        $hidden = $this->product(['name' => 'Archived Rayon Kurti', 'is_active' => false]);

        $this->get('/products?search=Rayon')
            ->assertOk()
            ->assertSee($matching->name)
            ->assertDontSee($other->name)
            ->assertDontSee($hidden->name);
    }

    public function test_product_page_exposes_active_clothing_variants(): void
    {
        $product = $this->product([
            'name' => 'Maroon Anarkali Set',
            'slug' => 'maroon-anarkali-set',
            'product_type' => 'variable',
            'stock' => 0,
        ]);
        $variant = $this->clothingVariant($product, ['sku' => 'ANARKALI-M-MAROON']);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee($product->name)
            ->assertSee($variant->sku);
    }
}
