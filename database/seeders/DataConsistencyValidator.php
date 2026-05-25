<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data Consistency Validation Seeder
 * 
 * Run validation queries to ensure data integrity after migrations and transfers.
 * Usage: php artisan db:seed --class=DataConsistencyValidator
 */
class DataConsistencyValidator extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔍 Starting Data Consistency Validation...');
        $this->command->newLine();

        $issues = [];

        // 1. Check for orphaned bookings
        $this->command->info('1️⃣  Checking for orphaned bookings...');
        $orphanedBookings = DB::table('bookings')
            ->whereNotIn('user_id', DB::table('users')->select('id'))
            ->count();
        if ($orphanedBookings > 0) {
            $issues[] = "❌ Found $orphanedBookings bookings with non-existent users";
        } else {
            $this->command->line('✅ All bookings have valid user references');
        }

        // 2. Check for bookings with invalid workers
        $this->command->info('2️⃣  Checking for bookings with invalid workers...');
        $orphanedWorkerBookings = DB::table('bookings')
            ->whereNotIn('worker_id', DB::table('workers')->select('id'))
            ->count();
        if ($orphanedWorkerBookings > 0) {
            $issues[] = "❌ Found $orphanedWorkerBookings bookings with non-existent workers";
        } else {
            $this->command->line('✅ All bookings have valid worker references');
        }

        // 3. Check for bookings with invalid services
        $this->command->info('3️⃣  Checking for bookings with invalid services...');
        $orphanedServiceBookings = DB::table('bookings')
            ->whereNotIn('service_id', DB::table('services')->select('id'))
            ->count();
        if ($orphanedServiceBookings > 0) {
            $issues[] = "❌ Found $orphanedServiceBookings bookings with non-existent services";
        } else {
            $this->command->line('✅ All bookings have valid service references');
        }

        // 4. Check for bookings with invalid addresses
        $this->command->info('4️⃣  Checking for bookings with invalid addresses...');
        $invalidAddressBookings = DB::table('bookings')
            ->whereNotNull('address_id')
            ->whereNotIn('address_id', DB::table('addresses')->select('id'))
            ->count();
        if ($invalidAddressBookings > 0) {
            $issues[] = "❌ Found $invalidAddressBookings bookings with non-existent addresses";
        } else {
            $this->command->line('✅ All bookings have valid address references');
        }

        // 5. Check for reviews with invalid bookings
        $this->command->info('5️⃣  Checking for reviews with invalid bookings...');
        $orphanedReviews = DB::table('reviews')
            ->whereNotIn('booking_id', DB::table('bookings')->select('id'))
            ->count();
        if ($orphanedReviews > 0) {
            $issues[] = "❌ Found $orphanedReviews reviews with non-existent bookings";
        } else {
            $this->command->line('✅ All reviews have valid booking references');
        }

        // 6. Check for reviews with invalid users
        $this->command->info('6️⃣  Checking for reviews with invalid users...');
        $orphanedReviewUsers = DB::table('reviews')
            ->whereNotIn('user_id', DB::table('users')->select('id'))
            ->count();
        if ($orphanedReviewUsers > 0) {
            $issues[] = "❌ Found $orphanedReviewUsers reviews with non-existent users";
        } else {
            $this->command->line('✅ All reviews have valid user references');
        }

        // 7. Check for reviews with invalid workers
        $this->command->info('7️⃣  Checking for reviews with invalid workers...');
        $orphanedReviewWorkers = DB::table('reviews')
            ->whereNotIn('worker_id', DB::table('workers')->select('id'))
            ->count();
        if ($orphanedReviewWorkers > 0) {
            $issues[] = "❌ Found $orphanedReviewWorkers reviews with non-existent workers";
        } else {
            $this->command->line('✅ All reviews have valid worker references');
        }

        // 8. Check for messages with invalid senders
        $this->command->info('8️⃣  Checking for messages with invalid senders...');
        $orphanedMessages = DB::table('messages')
            ->whereNotIn('sender_id', DB::table('users')->select('id'))
            ->count();
        if ($orphanedMessages > 0) {
            $issues[] = "❌ Found $orphanedMessages messages with non-existent senders";
        } else {
            $this->command->line('✅ All messages have valid sender references');
        }

        // 9. Check for messages with invalid receivers
        $this->command->info('9️⃣  Checking for messages with invalid receivers...');
        $orphanedMessageReceivers = DB::table('messages')
            ->whereNotIn('receiver_id', DB::table('workers')->select('id'))
            ->count();
        if ($orphanedMessageReceivers > 0) {
            $issues[] = "❌ Found $orphanedMessageReceivers messages with non-existent receivers";
        } else {
            $this->command->line('✅ All messages have valid receiver references');
        }

        // 10. Check for notifications with invalid users
        $this->command->info('🔟 Checking for notifications with invalid users...');
        $orphanedNotifications = DB::table('notifications')
            ->whereNotIn('user_id', DB::table('users')->select('id'))
            ->count();
        if ($orphanedNotifications > 0) {
            $issues[] = "❌ Found $orphanedNotifications notifications with non-existent users";
        } else {
            $this->command->line('✅ All notifications have valid user references');
        }

        // 11. Check for notifications with invalid bookings
        $this->command->info('1️⃣1️⃣  Checking for notifications with invalid bookings...');
        $orphanedNotificationBookings = DB::table('notifications')
            ->whereNotNull('booking_id')
            ->whereNotIn('booking_id', DB::table('bookings')->select('id'))
            ->count();
        if ($orphanedNotificationBookings > 0) {
            $issues[] = "❌ Found $orphanedNotificationBookings notifications with non-existent bookings";
        } else {
            $this->command->line('✅ All notifications have valid booking references');
        }

        // 12. Check for services with invalid workers
        $this->command->info('1️⃣2️⃣  Checking for services with invalid workers...');
        $orphanedServices = DB::table('services')
            ->whereNotIn('worker_id', DB::table('workers')->select('id'))
            ->count();
        if ($orphanedServices > 0) {
            $issues[] = "❌ Found $orphanedServices services with non-existent workers";
        } else {
            $this->command->line('✅ All services have valid worker references');
        }

        // 13. Check for addresses with invalid users
        $this->command->info('1️⃣3️⃣  Checking for addresses with invalid users...');
        $orphanedAddresses = DB::table('addresses')
            ->whereNotIn('user_id', DB::table('users')->select('id'))
            ->count();
        if ($orphanedAddresses > 0) {
            $issues[] = "❌ Found $orphanedAddresses addresses with non-existent users";
        } else {
            $this->command->line('✅ All addresses have valid user references');
        }

        // 14. Check for duplicate payment intent IDs
        $this->command->info('1️⃣4️⃣  Checking for duplicate payment intent IDs...');
        $duplicatePaymentIntents = DB::table('bookings')
            ->whereNotNull('payment_intent_id')
            ->groupBy('payment_intent_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        if ($duplicatePaymentIntents > 0) {
            $issues[] = "❌ Found $duplicatePaymentIntents duplicate payment intent IDs";
        } else {
            $this->command->line('✅ All payment intent IDs are unique');
        }

        // 15. Check for duplicate worker emails
        $this->command->info('1️⃣5️⃣  Checking for duplicate worker emails...');
        $duplicateWorkerEmails = DB::table('workers')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        if ($duplicateWorkerEmails > 0) {
            $issues[] = "❌ Found $duplicateWorkerEmails workers with duplicate emails";
        } else {
            $this->command->line('✅ All worker emails are unique');
        }

        // 16. Check for duplicate user emails
        $this->command->info('1️⃣6️⃣  Checking for duplicate user emails...');
        $duplicateUserEmails = DB::table('users')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        if ($duplicateUserEmails > 0) {
            $issues[] = "❌ Found $duplicateUserEmails users with duplicate emails";
        } else {
            $this->command->line('✅ All user emails are unique');
        }

        // 17. Check for invalid booking statuses
        $this->command->info('1️⃣7️⃣  Checking for invalid booking statuses...');
        $validStatuses = ['pending', 'accepted', 'upcoming', 'completed', 'cancelled', 'saved'];
        $invalidStatuses = DB::table('bookings')
            ->whereNotIn('status', $validStatuses)
            ->count();
        if ($invalidStatuses > 0) {
            $issues[] = "❌ Found $invalidStatuses bookings with invalid status values";
        } else {
            $this->command->line('✅ All booking statuses are valid');
        }

        // 18. Check for invalid payment statuses
        $this->command->info('1️⃣8️⃣  Checking for invalid payment statuses...');
        $validPaymentStatuses = ['pending', 'completed', 'failed', 'refunded'];
        $invalidPaymentStatuses = DB::table('bookings')
            ->whereNotIn('payment_status', $validPaymentStatuses)
            ->count();
        if ($invalidPaymentStatuses > 0) {
            $issues[] = "❌ Found $invalidPaymentStatuses bookings with invalid payment status values";
        } else {
            $this->command->line('✅ All payment statuses are valid');
        }

        // Summary
        $this->command->newLine();
        $this->command->info('═════════════════════════════════════════════════════════════');
        
        if (empty($issues)) {
            $this->command->info('✅ VALIDATION PASSED: All data is consistent!');
        } else {
            $this->command->error('❌ VALIDATION FAILED: Issues detected:');
            foreach ($issues as $issue) {
                $this->command->error("   $issue");
            }
        }
        
        $this->command->info('═════════════════════════════════════════════════════════════');
    }
}
