<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Safety net: refund Stripe charges that succeeded but never got linked to a
// booking (confirmBooking failed after the charge went through). Requires
// the server's cron to run `php artisan schedule:run` every minute.
Schedule::command('payments:reconcile-orphaned')->everyThirtyMinutes();
