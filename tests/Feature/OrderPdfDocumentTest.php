<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsShopData;
use Tests\TestCase;

class OrderPdfDocumentTest extends TestCase
{
    use BuildsShopData, RefreshDatabase;

    public function test_admin_can_download_invoice_and_packing_slip_pdfs(): void
    {
        $admin = $this->adminUser();
        [$user, $order] = $this->paidOrder();

        $this->actingAs($admin)
            ->get(route('admin.orders.invoice', $order))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->assertDatabaseHas('invoices', [
            'order_id' => $order->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.packing-slip', $order))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_customer_can_download_their_invoice_pdf(): void
    {
        [$user, $order] = $this->paidOrder();

        $this->actingAs($user)
            ->get(route('orders.invoice', $order))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_customer_cannot_download_another_users_invoice(): void
    {
        [, $order] = $this->paidOrder();
        $other = User::factory()->create();

        $this->actingAs($other)
            ->get(route('orders.invoice', $order))
            ->assertForbidden();
    }

    /**
     * @return array{0: User, 1: \App\Models\Order}
     */
    private function paidOrder(): array
    {
        $user = User::factory()->create();
        $product = $this->product();
        $order = $this->order($user, [
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => 'cod',
        ]);
        $this->orderItem($order, $product);

        return [$user, $order->fresh('items')];
    }

    private function adminUser(): User
    {
        $role = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'status' => true,
        ]);

        return User::factory()->create([
            'is_admin' => true,
            'status' => true,
            'role_id' => $role->id,
        ]);
    }
}
