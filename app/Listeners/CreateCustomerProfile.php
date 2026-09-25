<?php

namespace App\Listeners;

use App\Models\Customer;
use Illuminate\Auth\Events\Registered;

class CreateCustomerProfile
{
    public function handle(Registered $event): void
    {
        $user = $event->user;

        if ($user->is_admin || Customer::where('user_id', $user->id)->exists()) {
            return;
        }

        Customer::fromUser($user);
    }
}
