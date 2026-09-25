<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RemovedFeatureRoutesTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('removedRoutes')]
    public function test_removed_feature_routes_return_not_found(string $method, string $uri): void
    {
        $this->call($method, $uri)->assertNotFound();
    }

    public static function removedRoutes(): array
    {
        return [
            'legacy profile' => ['GET', '/profile'],
            'shop by bike' => ['GET', '/shop-by-bike'],
            'wallet' => ['GET', '/account/wallet'],
            'loyalty' => ['GET', '/account/loyalty'],
            'admin bike models' => ['GET', '/admin/masters/bike-models'],
            'admin vehicle brands' => ['GET', '/admin/masters/vehicle-brands'],
        ];
    }
}
