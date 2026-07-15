<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;
use Stripe\Refund;
use Stripe\Stripe;

class ReconcileOrphanedPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:reconcile-orphaned
        {--grace=30 : Minutes to wait after a charge succeeds before treating it as orphaned}
        {--window=180 : How far back, in minutes, to look for charges to check}
        {--dry-run : List what would be refunded without actually refunding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safety net for confirmBooking failing after a Stripe charge already '
        . 'succeeded (network blip, server error, crash mid-request): finds our succeeded '
        . 'PaymentIntents with no matching booking and refunds them.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $secret = config('services.stripe.secret');

        if (!$secret) {
            $this->error('Stripe secret key is not configured.');
            return self::FAILURE;
        }

        Stripe::setApiKey($secret);

        $graceCutoff = now()->subMinutes((int) $this->option('grace'));
        $windowStart = now()->subMinutes((int) $this->option('window'));
        $dryRun = (bool) $this->option('dry-run');

        $intents = PaymentIntent::all([
            'created' => [
                'gte' => $windowStart->timestamp,
                'lte' => $graceCutoff->timestamp,
            ],
            'limit' => 100,
        ]);

        $checked = 0;
        $refunded = 0;

        foreach ($intents->autoPagingIterator() as $intent) {
            $checked++;

            if ($intent->status !== 'succeeded') {
                continue;
            }

            // Not one of ours, or already handled on a previous run.
            if (empty($intent->metadata->user_id ?? null)
                || ($intent->metadata->reconciled ?? null) === 'true'
            ) {
                continue;
            }

            if (Booking::where('payment_intent_id', $intent->id)->exists()) {
                continue;
            }

            $this->warn("Orphaned payment: {$intent->id} (amount: {$intent->amount}, user_id: {$intent->metadata->user_id})");

            if ($dryRun) {
                continue;
            }

            try {
                Refund::create(['payment_intent' => $intent->id]);

                // Tag it so future runs don't try to refund it again.
                PaymentIntent::update($intent->id, [
                    'metadata' => ['reconciled' => 'true'],
                ]);

                $refunded++;
                Log::warning("Refunded orphaned PaymentIntent {$intent->id} — charge succeeded but no booking was ever linked to it.");
            } catch (\Exception $e) {
                $this->error("Failed to refund {$intent->id}: {$e->getMessage()}");
                Log::error("Failed to refund orphaned PaymentIntent {$intent->id}: {$e->getMessage()}");
            }
        }

        $this->info("Checked {$checked} payment intent(s), refunded {$refunded}.");

        return self::SUCCESS;
    }
}
