<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\File;
use Throwable;

class StoreProfile
{
    public function name(): string
    {
        return $this->value('business_name', config('app.name', 'Ridhi Sidhi Garments'));
    }

    public function tagline(): string
    {
        return $this->value('business_tagline', 'Elegant ethnic and contemporary womenswear');
    }

    public function about(): string
    {
        return $this->value(
            'business_about',
            'Elegant ethnic and contemporary womenswear, thoughtfully curated and delivered across India.'
        );
    }

    public function email(): string
    {
        return $this->value('business_email', config('store.support_email', 'support@ridhisidhigarments.com'));
    }

    public function phone(): string
    {
        return $this->value('business_phone', config('store.support_phone', '9000000000'));
    }

    public function phoneHref(): string
    {
        $digits = preg_replace('/\D+/', '', $this->phone()) ?: '';

        return $digits === '' ? '#' : 'tel:+91'.$digits;
    }

    public function displayPhone(): string
    {
        $phone = $this->phone();

        return str_starts_with($phone, '+') ? $phone : '+91 '.$phone;
    }

    public function address(): string
    {
        return $this->value('business_address');
    }

    public function city(): string
    {
        return $this->value('business_city');
    }

    public function state(): string
    {
        return $this->value('business_state');
    }

    public function pincode(): string
    {
        return $this->value('business_pincode');
    }

    public function country(): string
    {
        return $this->value('business_country', 'India');
    }

    public function gstin(): string
    {
        return $this->value('business_gstin');
    }

    public function formattedAddress(): string
    {
        return collect([
            $this->address(),
            collect([$this->city(), $this->state(), $this->pincode()])->filter()->implode(', '),
            $this->country(),
        ])->filter()->implode("\n");
    }

    public function logoPath(): ?string
    {
        $path = $this->value('business_logo');

        return $path !== '' ? $path : null;
    }

    public function logoUrl(): string
    {
        $absolute = $this->logoAbsolutePath();

        if ($absolute) {
            $publicRoot = str_replace('\\', '/', public_path());
            $normalized = str_replace('\\', '/', $absolute);
            $relative = ltrim(str_replace($publicRoot, '', $normalized), '/');

            return '/'.$relative.'?v='.filemtime($absolute);
        }

        return '/images/logo.svg';
    }

    public function hasCustomLogo(): bool
    {
        return $this->logoAbsolutePath() !== null;
    }

    public function logoAbsolutePath(): ?string
    {
        $path = $this->logoPath();

        if (! $path) {
            return null;
        }

        foreach ([
            public_path($path),
            public_path('storage/'.$path),
        ] as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        $stored = storage_path('app/public/'.$path);
        if (is_file($stored)) {
            return $this->publishStoredLogo($path, $stored);
        }

        return null;
    }

    private function publishStoredLogo(string $path, string $stored): ?string
    {
        $name = basename($path);
        $directory = public_path('images/branding');
        File::ensureDirectoryExists($directory);
        $published = $directory.DIRECTORY_SEPARATOR.$name;

        if (! is_file($published)) {
            File::copy($stored, $published);
        }

        if (! is_file($published)) {
            return null;
        }

        Setting::set('business_logo', 'images/branding/'.$name);

        return $published;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'business_name' => $this->name(),
            'business_tagline' => $this->tagline(),
            'business_about' => $this->about(),
            'business_email' => $this->email(),
            'business_phone' => $this->phone(),
            'business_address' => $this->address(),
            'business_city' => $this->city(),
            'business_state' => $this->state(),
            'business_pincode' => $this->pincode(),
            'business_country' => $this->country(),
            'business_gstin' => $this->gstin(),
            'business_logo' => $this->logoPath() ?? '',
        ];
    }

    public function applyToConfig(): void
    {
        config([
            'app.name' => $this->name(),
            'seo.site_name' => $this->name(),
            'store.support_email' => $this->email(),
            'store.support_phone' => $this->phone(),
            'mail.from.name' => $this->name(),
            'razorpay.company_name' => $this->name(),
        ]);
    }

    private function value(string $key, mixed $default = ''): string
    {
        try {
            $value = Setting::get($key, $default);
        } catch (Throwable) {
            $value = $default;
        }

        return trim((string) ($value ?? $default));
    }
}
