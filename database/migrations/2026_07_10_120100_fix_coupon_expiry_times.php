<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('coupons')) {
            return;
        }

        DB::table('coupons')
            ->whereNotNull('expires_at')
            ->orderBy('id')
            ->each(fn ($coupon) => DB::table('coupons')->where('id', $coupon->id)->update([
                'expires_at' => Carbon::parse($coupon->expires_at)->endOfDay(),
            ]));

        DB::table('coupons')
            ->whereNotNull('starts_at')
            ->orderBy('id')
            ->each(fn ($coupon) => DB::table('coupons')->where('id', $coupon->id)->update([
                'starts_at' => Carbon::parse($coupon->starts_at)->startOfDay(),
            ]));
    }

    public function down(): void
    {
        // no rollback needed
    }
};
