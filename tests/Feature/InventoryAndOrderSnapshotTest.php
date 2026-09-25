<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\BuildsShopData;
use Tests\TestCase;

class InventoryAndOrderSnapshotTest extends TestCase
{
    use BuildsShopData, RefreshDatabase;

    public function test_variant_availability_is_checked_instead_of_parent_stock(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['product_type' => 'variable', 'stock' => 100]);
        $variant = $this->variant($product, ['stock' => 1]);
        $item = $this->cartItem($user, $product, $variant, 2);

        $this->assertFalse(app(InventoryService::class)->checkAvailability([$item]));

        $item->update(['quantity' => 1]);
        $this->assertTrue(app(InventoryService::class)->checkAvailability([$item->fresh()]));
    }

    public function test_reserving_and_releasing_stock_updates_the_selected_variant_only(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['product_type' => 'variable', 'stock' => 20, 'reserved_stock' => 0]);
        $variant = $this->variant($product, ['stock' => 5, 'reserved_stock' => 0]);
        $order = $this->order($user);
        $orderItem = $this->orderItem($order, $product, $variant, ['quantity' => 2]);
        $inventory = app(InventoryService::class);

        $inventory->reserveStock($order->load('items'));

        $this->assertSame(3, $variant->fresh()->stock);
        $this->assertSame(2, $variant->fresh()->reserved_stock);
        $this->assertSame(20, $product->fresh()->stock);
        $this->assertSame('reserved', $orderItem->fresh()->status);
        $this->assertTrue($order->fresh()->stock_reserved);

        $inventory->releaseStock($order->fresh()->load('items'));

        $this->assertSame(5, $variant->fresh()->stock);
        $this->assertSame(0, $variant->fresh()->reserved_stock);
        $this->assertSame('cancelled', $orderItem->fresh()->status);
        $this->assertFalse($order->fresh()->stock_reserved);
    }

    public function test_reservation_fails_atomically_when_variant_stock_is_insufficient(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['product_type' => 'variable']);
        $variant = $this->variant($product, ['stock' => 1]);
        $order = $this->order($user);
        $this->orderItem($order, $product, $variant, ['quantity' => 2]);

        try {
            app(InventoryService::class)->reserveStock($order->load('items'));
            $this->fail('Expected insufficient stock validation failure.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('stock', $exception->errors());
        }

        $this->assertSame(1, $variant->fresh()->stock);
        $this->assertSame(0, $variant->fresh()->reserved_stock);
        $this->assertFalse($order->fresh()->stock_reserved);
    }

    public function test_order_item_variant_snapshots_are_immutable(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['name' => 'Original Kurti', 'sku' => 'PRODUCT-ORIGINAL']);
        $variant = $this->clothingVariant($product, ['sku' => 'VARIANT-ORIGINAL', 'price' => 1499, 'weight' => 0.45]);
        $order = $this->order($user);
        $item = $this->orderItem($order, $product, $variant, [
            'size_snapshot' => $variant->size->name,
            'color_snapshot' => $variant->color->name,
            'material_snapshot' => $variant->material->name,
            'weight' => 0.45,
        ]);

        $item->update([
            'product_name' => 'Changed',
            'sku' => 'CHANGED',
            'variant_sku' => 'CHANGED-VARIANT',
            'size_snapshot' => 'XXL',
            'color_snapshot' => 'Blue',
            'material_snapshot' => 'Polyester',
            'price' => 1,
            'weight' => 9,
            'status' => 'reserved',
        ]);

        $item->refresh();
        $this->assertSame('Original Kurti', $item->product_name);
        $this->assertSame('PRODUCT-ORIGINAL', $item->sku);
        $this->assertSame('VARIANT-ORIGINAL', $item->variant_sku);
        $this->assertSame($variant->size->name, $item->size_snapshot);
        $this->assertSame($variant->color->name, $item->color_snapshot);
        $this->assertSame($variant->material->name, $item->material_snapshot);
        $this->assertSame('1499.00', $item->price);
        $this->assertSame('0.450', $item->weight);
        $this->assertSame('reserved', $item->status);
    }
}
