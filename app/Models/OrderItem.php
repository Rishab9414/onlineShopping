<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'variant_id', 'product_name', 'sku',
        'variant_sku', 'size_snapshot', 'color_snapshot', 'material_snapshot',
        'price', 'discount', 'gst', 'total', 'quantity', 'subtotal', 'weight', 'status',
    ];

    private const SNAPSHOT_FIELDS = [
        'product_name', 'sku', 'variant_sku',
        'size_snapshot', 'color_snapshot', 'material_snapshot',
        'price', 'weight',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount' => 'decimal:2',
            'gst' => 'decimal:2',
            'total' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'weight' => 'decimal:3',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    protected static function booted(): void
    {
        static::updating(function (OrderItem $item) {
            foreach (self::SNAPSHOT_FIELDS as $field) {
                if ($item->isDirty($field)) {
                    $item->{$field} = $item->getOriginal($field);
                }
            }
        });
    }

    public function lineTotal(): float
    {
        return (float) ($this->total ?? $this->subtotal);
    }
}
