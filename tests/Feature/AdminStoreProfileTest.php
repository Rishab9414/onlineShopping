<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Support\StoreProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminStoreProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_profile_can_be_updated_and_appears_on_the_storefront(): void
    {
        $role = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'status' => true,
        ]);
        $admin = User::factory()->create([
            'is_admin' => true,
            'status' => true,
            'role_id' => $role->id,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.settings.store.update'), [
            'business_name' => 'Meera Couture',
            'business_tagline' => 'Festive wear for every celebration',
            'business_about' => 'Handpicked ethnic wear from Jaipur.',
            'business_email' => 'hello@meeracouture.test',
            'business_phone' => '9876543210',
            'business_address' => '12 Textile Market',
            'business_city' => 'Jaipur',
            'business_state' => 'Rajasthan',
            'business_pincode' => '302001',
            'business_country' => 'India',
            'business_gstin' => '08AAAAA0000A1Z5',
            'business_logo' => UploadedFile::fake()->image('logo.png', 200, 80),
        ]);

        $response->assertRedirect();
        $this->assertSame('Meera Couture', Setting::get('business_name'));
        $this->assertNotEmpty(Setting::get('business_logo'));
        $this->assertFileExists(public_path(Setting::get('business_logo')));

        $store = app(StoreProfile::class);
        $this->assertSame('Meera Couture', $store->name());
        $this->assertSame('hello@meeracouture.test', $store->email());
        $this->assertStringContainsString('Jaipur', $store->formattedAddress());

        $this->get('/')->assertOk()->assertSee('Meera Couture');

        $this->actingAs($admin)
            ->get(route('admin.settings.store'))
            ->assertOk()
            ->assertSee('Meera Couture')
            ->assertSee('12 Textile Market');

        $logo = Setting::get('business_logo');
        if ($logo && File::exists(public_path($logo))) {
            File::delete(public_path($logo));
        }
    }
}
