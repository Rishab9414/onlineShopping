<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use App\Services\ActivityLogger;
use App\Support\StoreProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StoreSettingsController extends Controller
{
    public function store(StoreProfile $store): View
    {
        return view('admin.settings.store', [
            'store' => $store,
            'values' => $store->toArray(),
        ]);
    }

    public function updateStore(Request $request, StoreProfile $store): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:120'],
            'business_tagline' => ['nullable', 'string', 'max:180'],
            'business_about' => ['nullable', 'string', 'max:500'],
            'business_email' => ['required', 'email', 'max:150'],
            'business_phone' => ['required', 'string', 'max:30'],
            'business_address' => ['nullable', 'string', 'max:255'],
            'business_city' => ['nullable', 'string', 'max:80'],
            'business_state' => ['nullable', 'string', 'max:80'],
            'business_pincode' => ['nullable', 'string', 'max:12'],
            'business_country' => ['nullable', 'string', 'max:80'],
            'business_gstin' => ['nullable', 'string', 'max:20'],
            'business_logo' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,svg', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        foreach ([
            'business_name', 'business_tagline', 'business_about', 'business_email',
            'business_phone', 'business_address', 'business_city', 'business_state',
            'business_pincode', 'business_country', 'business_gstin',
        ] as $key) {
            Setting::set($key, trim((string) ($validated[$key] ?? '')));
        }

        if ($request->boolean('remove_logo') && $store->logoPath()) {
            $this->deleteLogoFiles($store);
            Setting::set('business_logo', '');
        }

        if ($request->hasFile('business_logo')) {
            $this->deleteLogoFiles($store);

            $file = $request->file('business_logo');
            $directory = public_path('images/branding');
            File::ensureDirectoryExists($directory);

            $filename = Str::uuid().'.'.strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'png');
            $file->move($directory, $filename);

            Setting::set('business_logo', 'images/branding/'.$filename);
        }

        app(StoreProfile::class)->applyToConfig();

        ActivityLogger::log('updated', 'settings', null, 'Store profile updated');

        return back()->with('success', 'Store details saved. The logo, name, and address now appear across the website.');
    }

    private function deleteLogoFiles(StoreProfile $store): void
    {
        $absolute = $store->logoAbsolutePath();
        if ($absolute && is_file($absolute) && ! str_ends_with(str_replace('\\', '/', $absolute), '/images/logo.svg')) {
            File::delete($absolute);
        }

        if ($store->logoPath()) {
            Storage::disk('public')->delete($store->logoPath());
        }
    }

    public function payments(): View
    {
        return view('admin.settings.payments', [
            'codEnabled' => Setting::codEnabled(),
            'freeShippingEnabled' => Setting::freeShippingEnabled(),
            'freeShippingMinAmount' => Setting::freeShippingMinAmount(),
        ]);
    }

    public function updatePayments(Request $request): RedirectResponse
    {
        $request->validate([
            'cod_enabled' => ['nullable', 'boolean'],
            'free_shipping_enabled' => ['nullable', 'boolean'],
            'free_shipping_min_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $cod = $request->boolean('cod_enabled');
        $freeShipping = $request->boolean('free_shipping_enabled');
        $freeShippingMin = max(0, (float) $request->input('free_shipping_min_amount', 5000));

        Setting::set('cod_enabled', $cod);
        Setting::set('free_shipping_enabled', $freeShipping);
        Setting::set('free_shipping_min_amount', $freeShippingMin);

        ActivityLogger::log(
            'updated',
            'settings',
            null,
            'Payment settings updated — COD '.($cod ? 'enabled' : 'disabled')
                .', Free shipping '.($freeShipping ? 'enabled above ₹'.$freeShippingMin : 'disabled')
        );

        return back()->with('success', 'Payment settings saved successfully.');
    }

    public function homepage(): View
    {
        return view('admin.settings.homepage', [
            'homeReelsEnabled' => Setting::homeReelsEnabled(),
            'homeReelsAutoplay' => Setting::homeReelsAutoplay(),
            'couponsEnabled' => Setting::couponsEnabled(),
        ]);
    }

    public function updateHomepage(Request $request): RedirectResponse
    {
        $request->validate([
            'home_reels_enabled' => ['nullable', 'boolean'],
            'home_reels_autoplay' => ['nullable', 'boolean'],
            'coupons_enabled' => ['nullable', 'boolean'],
        ]);

        $reelsEnabled = $request->boolean('home_reels_enabled');
        $reelsAutoplay = $request->boolean('home_reels_autoplay');
        $couponsEnabled = $request->boolean('coupons_enabled');

        Setting::set('home_reels_enabled', $reelsEnabled);
        Setting::set('home_reels_autoplay', $reelsAutoplay);
        Setting::set('coupons_enabled', $couponsEnabled);

        ActivityLogger::log(
            'updated',
            'settings',
            null,
            'Homepage settings updated — Reels '.($reelsEnabled ? 'enabled' : 'disabled')
                .', Reels autoplay '.($reelsAutoplay ? 'on' : 'off')
                .', Coupons '.($couponsEnabled ? 'enabled' : 'disabled')
        );

        return back()->with('success', 'Homepage settings saved successfully.');
    }

    public function tax(): View
    {
        return view('admin.settings.tax', [
            'defaultTaxIncluded' => Setting::defaultTaxIncluded(),
        ]);
    }

    public function updateTax(Request $request): RedirectResponse
    {
        $request->validate([
            'default_tax_included' => ['required', 'in:0,1'],
        ]);

        $included = $request->input('default_tax_included') === '1';
        Setting::set('default_tax_included', $included);

        Product::query()->update(['tax_included' => $included]);

        ActivityLogger::log(
            'updated',
            'settings',
            null,
            'Tax settings updated — default prices '.($included ? 'include GST' : 'exclude GST (added at checkout)')
        );

        return back()->with('success', 'Tax settings saved. All products updated to match the default GST price type.');
    }

    public function maintenance(): View
    {
        return view('admin.settings.maintenance', [
            'isDown' => app()->isDownForMaintenance(),
            'message' => Setting::maintenanceMessage(),
            'eta' => Setting::maintenanceEta(),
        ]);
    }

    public function updateMaintenance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'message' => ['nullable', 'string', 'max:500'],
            'eta' => ['nullable', 'date'],
        ]);

        $enabled = $request->boolean('enabled');
        $message = trim((string) ($validated['message'] ?? ''))
            ?: 'We are refreshing Ridhi Sidhi Garments with a more beautiful shopping experience. Please check back shortly.';
        $eta = $validated['eta'] ?? null;

        Setting::set('maintenance_message', $message);
        Setting::set('maintenance_eta', $eta ? (string) $eta : '');

        if ($enabled) {
            Artisan::call('down', [
                '--render' => 'errors.maintenance',
                '--retry' => 60,
                '--refresh' => 30,
                '--secret' => 'ridhisidhi-admin-bypass',
            ]);

            ActivityLogger::log('updated', 'settings', null, 'Website maintenance mode ENABLED');

            return back()->with('success', 'Maintenance mode is ON. Customers see the maintenance page. Admin panel still works.');
        }

        Artisan::call('up');

        ActivityLogger::log('updated', 'settings', null, 'Website maintenance mode DISABLED');

        return back()->with('success', 'Maintenance mode is OFF. The storefront is live again.');
    }
}
