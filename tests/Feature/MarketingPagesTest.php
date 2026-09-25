<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Coupon;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsShopData;
use Tests\TestCase;

class MarketingPagesTest extends TestCase
{
    use BuildsShopData, RefreshDatabase;

    public function test_admin_can_open_marketing_pages(): void
    {
        $admin = $this->adminUser();

        foreach ([
            'admin.banners.index',
            'admin.home-themes.index',
            'admin.promo-popups.index',
            'admin.home-reels.index',
            'admin.coupons.index',
            'admin.announcements.index',
            'admin.blog.index',
            'admin.settings.homepage',
        ] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
    }

    public function test_homepage_shows_banner_and_blog_is_public(): void
    {
        Banner::create([
            'title' => 'Festive Kurtas',
            'subtitle' => 'New season edit',
            'image' => 'images/fashion-hero.svg',
            'button_text' => 'Shop Now',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Festive Kurtas')
            ->assertSee('Blog');

        $this->get(route('blog.index'))->assertOk();
    }

    public function test_customer_can_apply_coupon_on_cart(): void
    {
        $user = User::factory()->create();
        $product = $this->product(['selling_price' => 1999, 'price' => 1999]);
        $this->cartItem($user, $product, null, 1);

        Coupon::create([
            'code' => 'FESTIVE10',
            'type' => 'percent',
            'value' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->postJson(route('cart.coupon.apply'), ['code' => 'FESTIVE10'])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('coupon_code', 'FESTIVE10');
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
