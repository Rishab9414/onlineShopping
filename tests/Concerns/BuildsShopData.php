<?php

namespace Tests\Concerns;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Color;
use App\Models\Material;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\User;
use Illuminate\Support\Str;

trait BuildsShopData
{
    protected function category(array $attributes = []): Category
    {
        $name = $attributes['name'] ?? 'Kurtis '.Str::random(6);

        return Category::create(array_merge([
            'name' => $name,
            'slug' => Str::slug($name),
            'image' => 'categories/kurti.jpg',
            'is_active' => true,
            'status' => 'active',
            'display_order' => 1,
        ], $attributes));
    }

    protected function product(array $attributes = []): Product
    {
        $name = $attributes['name'] ?? 'Cotton Kurti '.Str::random(6);

        return Product::create(array_merge([
            'category_id' => $this->category()->id,
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => 'PRD-'.Str::upper(Str::random(8)),
            'product_type' => 'simple',
            'price' => 999,
            'selling_price' => 999,
            'stock' => 10,
            'status' => 'published',
            'is_active' => true,
            'tax_included' => true,
        ], $attributes));
    }

    protected function variant(Product $product, array $attributes = []): ProductVariant
    {
        return ProductVariant::create(array_merge([
            'product_id' => $product->id,
            'sku' => 'VAR-'.Str::upper(Str::random(8)),
            'price' => 1099,
            'stock' => 5,
            'reserved_stock' => 0,
            'is_active' => true,
        ], $attributes));
    }

    protected function clothingVariant(Product $product, array $attributes = []): ProductVariant
    {
        $size = Size::create(['name' => 'M '.Str::random(4), 'status' => 'active']);
        $color = Color::create(['name' => 'Maroon '.Str::random(4), 'status' => 'active']);
        $material = Material::create(['name' => 'Cotton '.Str::random(4), 'status' => 'active']);

        return $this->variant($product, array_merge([
            'size_id' => $size->id,
            'color_id' => $color->id,
            'material_id' => $material->id,
        ], $attributes));
    }

    protected function cartItem(User $user, Product $product, ?ProductVariant $variant = null, int $quantity = 1): CartItem
    {
        return CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'quantity' => $quantity,
            'unit_price' => $variant?->price ?? $product->selling_price ?? $product->price,
        ]);
    }

    protected function order(User $user, array $attributes = []): Order
    {
        return Order::create(array_merge([
            'user_id' => $user->id,
            'order_number' => 'ORD-'.Str::upper(Str::random(10)),
            'status' => 'pending',
            'subtotal' => 1099,
            'discount' => 0,
            'shipping_charge' => 0,
            'tax_amount' => 0,
            'grand_total' => 1099,
            'total' => 1099,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'shipping_address' => 'Jaipur, Rajasthan 302001',
            'billing_address' => 'Jaipur, Rajasthan 302001',
        ], $attributes));
    }

    protected function orderItem(Order $order, Product $product, ?ProductVariant $variant = null, array $attributes = []): OrderItem
    {
        return OrderItem::create(array_merge([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'variant_sku' => $variant?->sku,
            'price' => $variant?->price ?? $product->price,
            'quantity' => 1,
            'subtotal' => $variant?->price ?? $product->price,
            'total' => $variant?->price ?? $product->price,
            'status' => 'pending',
        ], $attributes));
    }

    protected function checkoutPayload(string $paymentMethod = 'cod'): array
    {
        return [
            'first_name' => 'Ridhi',
            'last_name' => 'Customer',
            'email' => 'customer@example.com',
            'country_code' => '+91',
            'mobile' => '9876543210',
            'same_billing' => '1',
            'payment_method' => $paymentMethod,
            'shipping' => [
                'address_type' => 'home',
                'full_name' => 'Ridhi Customer',
                'mobile' => '9876543210',
                'address_line_1' => '12 Garment Market',
                'address_line_2' => '',
                'landmark' => '',
                'city' => 'Jaipur',
                'district' => 'Jaipur',
                'state' => 'Rajasthan',
                'country' => 'India',
                'pincode' => '302001',
            ],
        ];
    }
}
