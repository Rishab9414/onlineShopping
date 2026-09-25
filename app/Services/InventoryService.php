<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function checkAvailability(iterable $items): bool
    {
        foreach ($items as $item) {
            $product = $item->product ?? Product::find($item->product_id);
            $variant = $item->variant_id
                ? ($item->variant ?? ProductVariant::find($item->variant_id))
                : null;
            $available = $variant?->stock ?? $product?->stock ?? 0;

            if (! $product
                || ($product->product_type === 'variable' && ! $variant)
                || ($variant && (int) $variant->product_id !== (int) $product->id)
                || $available < $item->quantity) {
                return false;
            }
        }

        return true;
    }

    public function reserveStock(Order $order): void
    {
        if ($order->stock_reserved) {
            return;
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $stockModel = $this->lockStockModel($item);
                if (! $stockModel || $stockModel->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient stock for {$item->product_name}.",
                    ]);
                }
                $stockModel->decrement('stock', $item->quantity);
                $stockModel->increment('reserved_stock', $item->quantity);
                $item->update(['status' => 'reserved']);
            }
            $order->update(['stock_reserved' => true]);
        });
    }

    public function releaseStock(Order $order): void
    {
        if (! $order->stock_reserved) {
            return;
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $stockModel = $this->lockStockModel($item);
                if ($stockModel) {
                    $released = min((int) $stockModel->reserved_stock, (int) $item->quantity);
                    if ($released > 0) {
                        $stockModel->decrement('reserved_stock', $released);
                        $stockModel->increment('stock', $released);
                    }
                }
                $item->update(['status' => 'cancelled']);
            }
            $order->update(['stock_reserved' => false]);
        });
    }

    private function lockStockModel(OrderItem $item): Product|ProductVariant|null
    {
        if ($item->variant_id) {
            return ProductVariant::lockForUpdate()
                ->where('product_id', $item->product_id)
                ->find($item->variant_id);
        }

        return Product::lockForUpdate()->find($item->product_id);
    }
}
