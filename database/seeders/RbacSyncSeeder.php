<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RbacSyncSeeder extends Seeder
{
    /**
     * Idempotently ensures all admin permissions exist and keeps the
     * Super Admin role synced with every permission. Safe to re-run.
     */
    public function run(): void
    {
        $groups = [
            'dashboard' => ['view-dashboard'],
            'masters' => ['manage-categories', 'manage-brands', 'manage-manufacturers', 'manage-suppliers', 'manage-taxes', 'manage-units', 'manage-sizes', 'manage-colors', 'manage-materials'],
            'users' => ['manage-admin-users', 'manage-roles', 'manage-permissions', 'view-login-history', 'view-activity-logs'],
            'products' => ['manage-products', 'manage-inventory'],
            'orders' => ['manage-orders', 'manage-returns'],
            'customers' => ['manage-customers'],
            'marketing' => ['manage-banners', 'manage-home-themes', 'manage-promo-popups', 'manage-home-reels', 'manage-coupons', 'manage-announcements', 'manage-blog'],
            'reports' => ['view-reports'],
            'settings' => ['manage-settings'],
        ];

        foreach ($groups as $group => $slugs) {
            foreach ($slugs as $slug) {
                Permission::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => Str::title(str_replace('-', ' ', $slug)), 'group' => $group],
                );
            }
        }

        $superAdmin = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'description' => 'Full retained store administration access.', 'status' => true],
        );
        $superAdmin->permissions()->sync(Permission::pluck('id'));

        $manager = Role::updateOrCreate(
            ['slug' => 'store-manager'],
            ['name' => 'Store Manager', 'description' => 'Manage catalog, inventory, orders, and customers.', 'status' => true],
        );
        $manager->permissions()->sync(
            Permission::whereIn('group', ['dashboard', 'masters', 'products', 'orders', 'customers', 'marketing', 'reports'])->pluck('id')
        );

        User::where('email', 'admin@ridhisidhi.test')->update(['role_id' => $superAdmin->id]);
    }
}
