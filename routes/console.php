<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduler
|--------------------------------------------------------------------------
| No recurring application tasks are registered. Pending Razorpay payments
| can be reconciled manually or through the token-protected cron endpoint.
| A queue worker is still required when QUEUE_CONNECTION=database.
*/
