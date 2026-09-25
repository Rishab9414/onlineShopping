<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'shopper@ridhisidhi.test'],
            [
                'name' => 'Ananya Sharma',
                'password' => bcrypt('Customer@12345'),
                'is_admin' => false,
                'phone' => null,
                'status' => true,
                'email_verified_at' => now(),
            ],
        );

        $customer = Customer::fromUser($user, [
            'mobile' => '9900000001',
            'gender' => 'female',
            'registration_source' => 'website',
            'email_verified' => true,
            'mobile_verified' => true,
            'newsletter_subscription' => true,
            'customer_type' => 'regular',
        ]);

        $customer->addresses()->create([
            'address_type' => 'home',
            'full_name' => $customer->full_name,
            'mobile' => $customer->mobile,
            'address_line_1' => 'Demo Address',
            'address_line_2' => 'For local testing only',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'country' => 'India',
            'pincode' => '560001',
            'is_default_shipping' => true,
            'is_default_billing' => true,
            'status' => 'active',
        ]);
    }
}
