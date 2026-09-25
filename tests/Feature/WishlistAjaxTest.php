<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsShopData;
use Tests\TestCase;

class WishlistAjaxTest extends TestCase
{
    use BuildsShopData, RefreshDatabase;

    public function test_guests_cannot_toggle_wishlist_via_ajax(): void
    {
        $product = $this->product();

        $this->postJson(route('wishlist.toggle', $product))
            ->assertUnauthorized();
    }

    public function test_ajax_wishlist_toggle_adds_and_removes_the_product(): void
    {
        $user = User::factory()->create();
        $product = $this->product();

        $this->actingAs($user)
            ->postJson(route('wishlist.toggle', $product))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('wishlisted', true)
            ->assertJsonPath('count', 1);

        $this->actingAs($user)
            ->postJson(route('wishlist.toggle', $product))
            ->assertOk()
            ->assertJsonPath('wishlisted', false)
            ->assertJsonPath('count', 0);
    }
}
