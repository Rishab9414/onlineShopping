<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsShopData;
use Tests\TestCase;

class CartVariantTest extends TestCase
{
    use BuildsShopData, RefreshDatabase;

    public function test_same_product_with_two_variants_creates_separate_cart_lines(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['product_type' => 'variable', 'stock' => 0]);
        $small = $this->clothingVariant($product, ['sku' => 'KURTI-S', 'price' => 899]);
        $large = $this->clothingVariant($product, ['sku' => 'KURTI-L', 'price' => 1099]);

        $this->actingAs($user)->post(route('cart.store', $product), ['variant_id' => $small->id, 'quantity' => 1])
            ->assertRedirect(route('cart.index'));
        $this->actingAs($user)->post(route('cart.store', $product), ['variant_id' => $large->id, 'quantity' => 2])
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseCount('cart_items', 2);
        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'variant_id' => $small->id, 'quantity' => 1]);
        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'variant_id' => $large->id, 'quantity' => 2]);
    }

    public function test_variable_product_requires_a_variant(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['product_type' => 'variable', 'stock' => 10]);

        $this->actingAs($user)
            ->from(route('products.show', $product))
            ->post(route('cart.store', $product), ['quantity' => 1])
            ->assertRedirect(route('products.show', $product))
            ->assertSessionHasErrors('variant_id');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_variant_must_be_active_and_belong_to_the_requested_product(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['product_type' => 'variable']);
        $other = $this->product(['product_type' => 'variable']);
        $foreignVariant = $this->variant($other);
        $inactiveVariant = $this->variant($product, ['is_active' => false]);

        $this->actingAs($user)->post(route('cart.store', $product), [
            'variant_id' => $foreignVariant->id,
            'quantity' => 1,
        ])->assertNotFound();

        $this->actingAs($user)->post(route('cart.store', $product), [
            'variant_id' => $inactiveVariant->id,
            'quantity' => 1,
        ])->assertNotFound();
    }

    public function test_cart_uses_variant_price_and_enforces_variant_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['product_type' => 'variable', 'selling_price' => 799, 'stock' => 50]);
        $variant = $this->variant($product, ['price' => 1299, 'stock' => 2]);

        $this->actingAs($user)->post(route('cart.store', $product), [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ])->assertRedirect(route('cart.index'));

        $item = CartItem::sole();
        $this->assertSame('1299.00', $item->unit_price);
        $this->assertSame(2598.0, $item->subtotal());

        $this->actingAs($user)
            ->from(route('products.show', $product))
            ->post(route('cart.store', $product), ['variant_id' => $variant->id, 'quantity' => 3])
            ->assertSessionHasErrors('quantity');
    }

    public function test_cart_service_rejects_a_missing_or_foreign_variant(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['product_type' => 'variable']);
        $foreignVariant = $this->variant($this->product(['product_type' => 'variable']));
        $service = app(CartService::class);

        $this->actingAs($user);

        try {
            $service->add($product);
            $this->fail('A variable product must require a variant.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertSame('Please select a product variant.', $exception->getMessage());
        }

        try {
            $service->add($product, 1, $foreignVariant);
            $this->fail('A foreign variant must be rejected.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertSame('The selected variant does not belong to this product.', $exception->getMessage());
        }

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_ajax_add_to_cart_returns_json_count_without_redirect(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['product_type' => 'variable', 'name' => 'Silk Kurti']);
        $variant = $this->clothingVariant($product, ['sku' => 'KURTI-AJAX-M']);

        $this->actingAs($user)
            ->postJson(route('cart.store', $product), [
                'variant_id' => $variant->id,
                'quantity' => 2,
            ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('count', 2)
            ->assertJsonFragment(['message' => 'Silk Kurti ('.$variant->fresh()->load(['size', 'color', 'material'])->label().') added to cart.']);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);
    }

    public function test_ajax_add_to_cart_requires_a_variant_for_variable_products(): void
    {
        $product = $this->product(['product_type' => 'variable']);

        $this->postJson(route('cart.store', $product), ['quantity' => 1])
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors('variant_id');

        $this->assertDatabaseCount('cart_items', 0);
    }
}
