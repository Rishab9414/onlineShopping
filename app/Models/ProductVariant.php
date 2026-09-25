<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'barcode', 'color_id', 'size_id', 'material_id',
        'price', 'stock', 'reserved_stock', 'weight', 'image', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'reserved_stock' => 'integer',
            'weight' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ProductVariant $variant) {
            $variant->color_key = $variant->color_id ?: 0;
            $variant->size_key = $variant->size_id ?: 0;
            $variant->material_key = $variant->material_id ?: 0;
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'variant_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'variant_id');
    }

    public function availableStock(): int
    {
        return max(0, (int) $this->stock);
    }

    public function imageUrl(): ?string
    {
        if (empty($this->image)) {
            return null;
        }

        return str_starts_with($this->image, 'http') ? $this->image : asset('storage/'.$this->image);
    }

    public function label(): string
    {
        $parts = array_filter([
            $this->color?->name,
            $this->size?->name,
            $this->material?->name,
        ]);

        return $parts !== [] ? implode(' / ', $parts) : ($this->sku ?: 'Variant #'.$this->id);
    }

    public function shortLabel(): string
    {
        $parts = array_filter([
            $this->color?->name,
            $this->size?->name,
        ]);

        return $parts !== [] ? implode(' · ', $parts) : $this->label();
    }
}
