<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        if (Order::count() > 0) {
            return;
        }

        $customerUser = User::where('email', 'shopper@ridhisidhi.test')->firstOrFail();
        $customer = Customer::where('user_id', $customerUser->id)->firstOrFail();

        $variants = ProductVariant::with(['product.tax', 'size', 'color', 'material'])
            ->where('is_active', true)
            ->limit(6)
            ->get();
        if ($variants->isEmpty()) {
            $this->command->warn('No variants found. Run ProductVariantSeeder first.');

            return;
        }

        $samples = [
            ['status' => 'pending', 'payment_status' => 'pending', 'payment_method' => 'cod', 'days_ago' => 0],
            ['status' => 'confirmed', 'payment_status' => 'paid', 'payment_method' => 'razorpay', 'days_ago' => 1],
            ['status' => 'packing', 'payment_status' => 'paid', 'payment_method' => 'razorpay', 'days_ago' => 2],
            ['status' => 'delivered', 'payment_status' => 'paid', 'payment_method' => 'cod', 'days_ago' => 8],
            ['status' => 'completed', 'payment_status' => 'paid', 'payment_method' => 'razorpay', 'days_ago' => 14],
            ['status' => 'cancelled', 'payment_status' => 'pending', 'payment_method' => 'cod', 'days_ago' => 20],
        ];

        foreach ($samples as $i => $sample) {
            $variant = $variants[$i % $variants->count()];
            $product = $variant->product;
            $qty = ($i % 3) + 1;
            $lineSubtotal = (float) $variant->price * $qty;
            $taxRate = (float) ($product->tax?->percentage ?? 5);
            $tax = round($lineSubtotal - ($lineSubtotal / (1 + ($taxRate / 100))), 2);
            $shipping = 79;
            $grandTotal = $lineSubtotal + $shipping;
            $createdAt = now()->subDays($sample['days_ago'])->subHours($i + 1);
            $address = "Ananya Sharma\nDemo Address, For local testing only\nBengaluru, Karnataka\n560001\n9900000001";

            $order = Order::create([
                'user_id' => $customerUser->id,
                'customer_id' => $customer->id,
                'order_number' => 'RSG-DEMO-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'status' => $sample['status'],
                'subtotal' => $lineSubtotal,
                'shipping_charge' => $shipping,
                'tax_amount' => $tax,
                'grand_total' => $grandTotal,
                'total' => $grandTotal,
                'payment_method' => $sample['payment_method'],
                'payment_status' => $sample['payment_status'],
                'paid_at' => $sample['payment_status'] === 'paid' ? $createdAt->copy()->addMinutes(3) : null,
                'stock_reserved' => in_array($sample['status'], ['confirmed', 'packing', 'delivered', 'completed'], true),
                'shipping_address' => $address,
                'billing_address' => $address,
                'shipping_address_json' => [
                    'name' => $customer->full_name,
                    'line_1' => 'Demo Address',
                    'line_2' => 'For local testing only',
                    'city' => 'Bengaluru',
                    'state' => 'Karnataka',
                    'pincode' => '560001',
                    'phone' => $customer->mobile,
                ],
                'billing_address_json' => [
                    'name' => $customer->full_name,
                    'line_1' => 'Demo Address',
                    'city' => 'Bengaluru',
                    'state' => 'Karnataka',
                    'pincode' => '560001',
                    'phone' => $customer->mobile,
                ],
                'expected_delivery' => now()->addDays(5)->toDateString(),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'variant_id' => $variant->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'variant_sku' => $variant->sku,
                'size_snapshot' => $variant->size?->name,
                'color_snapshot' => $variant->color?->name,
                'material_snapshot' => $variant->material?->name,
                'price' => $variant->price,
                'gst' => $tax,
                'total' => $lineSubtotal,
                'quantity' => $qty,
                'subtotal' => $lineSubtotal,
                'weight' => $product->weight ?? 0.5,
                'status' => $sample['status'] === 'cancelled' ? 'cancelled' : 'reserved',
            ]);

            OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => 'pending',
                'title' => 'Order Placed',
                'remarks' => 'Customer placed the order',
                'actor' => 'customer',
                'created_at' => $createdAt,
            ]);

            if ($sample['status'] !== 'pending') {
                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'status' => $sample['status'],
                    'title' => ucwords(str_replace('_', ' ', $sample['status'])),
                    'actor' => 'system',
                    'created_at' => $createdAt->copy()->addHours(2),
                ]);
            }
        }

        $this->command->info('Created '.count($samples).' variant-aware Ridhi Sidhi sample orders.');
    }
}
