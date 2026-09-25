<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\RazorpayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsShopData;
use Tests\TestCase;

class CheckoutAndRazorpayTest extends TestCase
{
    use BuildsShopData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'razorpay.mock' => true,
            'razorpay.key_id' => null,
            'razorpay.key_secret' => null,
            'shipping.default_product_shipping' => 0,
        ]);
        Setting::set('cod_enabled', true);
    }

    public function test_cod_checkout_creates_pending_order_with_variant_snapshot_and_clears_cart(): void
    {
        $user = User::factory()->create(['email' => 'customer@example.com']);
        $product = $this->product(['product_type' => 'variable', 'stock' => 0]);
        $variant = $this->clothingVariant($product, [
            'sku' => 'KURTI-M-MAROON',
            'price' => 1299,
            'stock' => 4,
        ]);
        $this->cartItem($user, $product, $variant, 2);

        $response = $this->actingAs($user)->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::with('items')->sole();
        $response->assertRedirect(route('orders.confirmation', $order));
        $this->assertSame('cod', $order->payment_method);
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame($variant->id, $order->items->sole()->variant_id);
        $this->assertSame('KURTI-M-MAROON', $order->items->sole()->variant_sku);
        $this->assertSame('1299.00', $order->items->sole()->price);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_cod_checkout_is_rejected_when_cod_is_disabled(): void
    {
        Setting::set('cod_enabled', false);
        $user = User::factory()->create(['email' => 'customer@example.com']);
        $product = $this->product();
        $this->cartItem($user, $product);

        $this->actingAs($user)
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), $this->checkoutPayload())
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_online_checkout_stops_at_mock_razorpay_boundary_without_network_access(): void
    {
        $user = User::factory()->create(['email' => 'customer@example.com']);
        $product = $this->product();
        $this->cartItem($user, $product);

        $response = $this->actingAs($user)->post(
            route('checkout.store'),
            $this->checkoutPayload('online')
        );

        $order = Order::sole();
        $response->assertRedirect(route('orders.payment', $order));
        $this->assertSame('online', $order->payment_method);
        $this->assertSame('pending', $order->payment_status);
        $this->assertStringStartsWith('order_mock_', $order->razorpay_order_id);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_mock_payment_verification_requires_matching_razorpay_order(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user, [
            'payment_method' => 'online',
            'razorpay_order_id' => 'order_mock_expected',
        ]);

        $this->expectException(\InvalidArgumentException::class);

        app(RazorpayService::class)->verifyPayment(
            $order,
            'pay_mock_123',
            'order_mock_wrong',
            'mock_signature'
        );
    }

    public function test_mock_payment_verification_marks_order_paid_and_is_idempotent(): void
    {
        $user = User::factory()->create();
        $order = $this->order($user, [
            'payment_method' => 'online',
            'razorpay_order_id' => 'order_mock_expected',
        ]);
        $service = app(RazorpayService::class);

        $service->verifyPayment($order, 'pay_mock_success', 'order_mock_expected', 'mock_signature');
        $service->verifyPayment($order->fresh(), 'pay_mock_duplicate', 'order_mock_expected', 'mock_signature');

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('pay_mock_success', $order->razorpay_payment_id);
        $this->assertNotNull($order->paid_at);
        $this->assertDatabaseCount('order_status_logs', 1);
    }
}
