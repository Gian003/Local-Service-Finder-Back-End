<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// Populates the demo database with a believable history — repeat customers,
// varied reviews, and a spread of booking states — so switching between a
// customer login and a worker login during a presentation shows a system
// that's actually been used, not five empty accounts. Safe to re-run: each
// step checks for what it already created before adding more.
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->fixServiceData();
        $customers = $this->seedCustomers();
        $this->seedReviewHistory($customers);
        $this->seedUpcomingDemoBookings();
    }

    // The original seed data had every service at the same price with a
    // description that just repeated the title, and worker #5 (an
    // electrician) was oddly selling "Furniture Assembly" — small things,
    // but they're the first thing that reads as fake test data.
    private function fixServiceData(): void
    {
        $updates = [
            1 => [
                'description' => 'Deep home cleaning covering kitchens, bathrooms, and living areas — sanitized surfaces and streak-free floors guaranteed.',
                'price' => 850,
            ],
            2 => [
                'description' => 'Leak repairs, pipe installation, and clog removal for kitchens and bathrooms. Same-day service for urgent jobs.',
                'price' => 1200,
            ],
            3 => [
                'description' => 'Diagnostics and repair for refrigerators, washing machines, and air-conditioning units — most repairs completed in a single visit.',
                'price' => 1000,
            ],
            4 => [
                'description' => 'Gutter clearing, moss removal, and leak inspection to keep your roof in good condition ahead of the rainy season.',
                'price' => 1800,
            ],
            5 => [
                'title' => 'Electrical Wiring & Repair',
                'description' => 'Home rewiring, outlet installation, and circuit breaker troubleshooting from a licensed electrician.',
                'price' => 1100,
            ],
        ];

        foreach ($updates as $id => $fields) {
            Service::where('id', $id)->update($fields);
        }

        // Worker #7 is a real registered test account (not seed data) — its
        // category was left blank at registration even though it already has
        // a cleaning service, which looks broken in the UI.
        Worker::where('id', 7)
            ->where(function ($q) {
                $q->where('category', '')->orWhereNull('category');
            })
            ->update(['category' => 'cleaning']);
    }

    /** @return array<int, array{user: User, address: Address}> */
    private function seedCustomers(): array
    {
        $people = [
            ['Maria', 'Santos', 'maria.santos.demo@lsf.com', 'Purok 3, Brgy. Nancayasan'],
            ['Carlo', 'Dela Cruz', 'carlo.delacruz.demo@lsf.com', 'Brgy. Bactad East'],
            ['Angela', 'Reyes', 'angela.reyes.demo@lsf.com', 'Brgy. San Vicente'],
            ['Mark', 'Villanueva', 'mark.villanueva.demo@lsf.com', 'Brgy. Poblacion'],
            ['Precious', 'Aquino', 'precious.aquino.demo@lsf.com', 'Brgy. Camanang'],
            ['Ronald', 'Bautista', 'ronald.bautista.demo@lsf.com', 'Brgy. Cayambanan'],
            ['Katrina', 'Ramos', 'katrina.ramos.demo@lsf.com', 'Brgy. Tulong'],
            ['Julius', 'Mendoza', 'julius.mendoza.demo@lsf.com', 'Brgy. Anonas'],
        ];

        $customers = [];

        foreach ($people as [$first, $last, $email, $street]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'first_name' => $first,
                    'last_name' => $last,
                    'password' => Hash::make('password'),
                ]
            );

            $address = Address::firstOrCreate(
                ['user_id' => $user->id, 'label' => 'Home'],
                [
                    'address' => $street,
                    'city' => 'Urdaneta City',
                    'is_default' => true,
                ]
            );

            $customers[] = ['user' => $user, 'address' => $address];
        }

        return $customers;
    }

    private function seedReviewHistory(array $customers): void
    {
        $reviewBank = [
            1 => [
                ['rating' => 5, 'comment' => 'Very thorough, arrived on time and left the house spotless!'],
                ['rating' => 5, 'comment' => 'Great attention to detail, will book again.'],
                ['rating' => 4, 'comment' => 'Good service overall, a bit late but did solid work.'],
                ['rating' => 5, 'comment' => 'My kitchen has never looked this clean. Highly recommend!'],
                ['rating' => 4, 'comment' => 'Professional and friendly, missed a spot under the couch though.'],
                ['rating' => 5, 'comment' => 'Booked for a move-out clean, landlord was impressed.'],
                ['rating' => 5, 'comment' => 'Fast, careful with our things, and reasonably priced.'],
                ['rating' => 4, 'comment' => 'Solid cleaning job, would use again for regular upkeep.'],
            ],
            2 => [
                ['rating' => 5, 'comment' => 'Fixed my leaking sink quickly, very professional.'],
                ['rating' => 5, 'comment' => 'Excellent work, explained everything clearly.'],
                ['rating' => 4, 'comment' => 'Solved a stubborn clog that two other plumbers couldn\'t.'],
                ['rating' => 5, 'comment' => 'On time and cleaned up after the job, appreciated that.'],
                ['rating' => 4, 'comment' => 'Good work but pricier than expected for the callout.'],
                ['rating' => 5, 'comment' => 'Reliable — this is the third time I\'ve booked him.'],
                ['rating' => 5, 'comment' => 'Diagnosed a hidden pipe leak we didn\'t even know about.'],
                ['rating' => 3, 'comment' => 'Fixed the issue but took longer than quoted.'],
            ],
            3 => [
                ['rating' => 4, 'comment' => 'Fixed my aircon, works great now.'],
                ['rating' => 5, 'comment' => 'Diagnosed the issue fast and had the parts ready.'],
                ['rating' => 5, 'comment' => 'Washing machine is running like new again.'],
                ['rating' => 5, 'comment' => 'Very knowledgeable, answered all my questions.'],
                ['rating' => 4, 'comment' => 'Repaired our fridge same day, good service.'],
                ['rating' => 5, 'comment' => 'Honest about what needed replacing vs. what didn\'t.'],
                ['rating' => 4, 'comment' => 'Solid repair work, a little pricey on parts.'],
            ],
            4 => [
                ['rating' => 4, 'comment' => 'Solid work, gutter is clear now.'],
                ['rating' => 5, 'comment' => 'Found and patched a leak we\'d been dealing with for months.'],
                ['rating' => 4, 'comment' => 'Good job clearing debris ahead of the rainy season.'],
                ['rating' => 5, 'comment' => 'Thorough inspection, gave honest recommendations.'],
                ['rating' => 4, 'comment' => 'Did the work carefully, took a bit longer than planned.'],
                ['rating' => 5, 'comment' => 'No more leaks after the last big storm — great work.'],
            ],
            5 => [
                ['rating' => 5, 'comment' => 'Rewired my kitchen outlets safely and quickly.'],
                ['rating' => 5, 'comment' => 'Fixed a breaker issue that was tripping constantly.'],
                ['rating' => 4, 'comment' => 'Installed new lighting fixtures, clean work.'],
                ['rating' => 5, 'comment' => 'Very safety-conscious, explained the wiring issue clearly.'],
                ['rating' => 5, 'comment' => 'Solved an outlet problem two other electricians missed.'],
                ['rating' => 4, 'comment' => 'Good work, arrived a little later than scheduled.'],
                ['rating' => 5, 'comment' => 'Upgraded our panel, very professional throughout.'],
            ],
        ];

        foreach ($reviewBank as $workerId => $reviews) {
            $service = Service::where('worker_id', $workerId)->first();
            if (!$service) continue;

            foreach ($reviews as $i => $entry) {
                $customer = $customers[$i % count($customers)];

                // Re-running the seeder shouldn't pile up duplicate reviews
                // from the same customer for the same worker.
                $alreadyReviewed = Review::where('user_id', $customer['user']->id)
                    ->where('worker_id', $workerId)
                    ->exists();
                if ($alreadyReviewed) continue;

                $daysAgo = 5 + $i * 6; // spread across the last ~2 months
                $isCardPayment = $i % 3 === 0;

                $booking = Booking::create([
                    'user_id' => $customer['user']->id,
                    'service_id' => $service->id,
                    'worker_id' => $workerId,
                    'address_id' => $customer['address']->id,
                    'status' => 'completed',
                    'scheduled_at' => now()->subDays($daysAgo)->setTime(9 + ($i % 6), 0),
                    'total_price' => $service->price,
                    'payment_method' => $isCardPayment ? 'card' : 'cash',
                    'payment_status' => $isCardPayment ? 'completed' : 'pending',
                    'payment_intent_id' => $isCardPayment ? 'pi_seed_' . Str::random(16) : null,
                    'idempotency_key' => "seed-review-{$workerId}-{$i}",
                ]);

                $review = new Review([
                    'booking_id' => $booking->id,
                    'user_id' => $customer['user']->id,
                    'worker_id' => $workerId,
                    'rating' => $entry['rating'],
                    'comment' => $entry['comment'],
                ]);
                $review->created_at = now()->subDays($daysAgo);
                $review->updated_at = now()->subDays($daysAgo);
                $review->save();
            }

            // Recompute from the real rows now backing this worker's stats,
            // the same way ReviewController::store does — keeps "My
            // Reviews" and the aggregate rating/review_count consistent, so
            // a review submitted live during the demo changes the number
            // instead of it looking disconnected from reality.
            $avg = Review::where('worker_id', $workerId)->avg('rating');
            $count = Review::where('worker_id', $workerId)->count();
            Worker::where('id', $workerId)->update([
                'rating' => round($avg, 1),
                'review_count' => $count,
            ]);
        }
    }

    // Wired to the two real accounts used for live testing/demos, so logging
    // in as either shows a connected, ready-to-demo story: the customer
    // account has a pending request (demo the accept/reject flow) and an
    // already-accepted job (demo live tracking); the matching worker
    // accounts use the seeded password "password".
    private function seedUpcomingDemoBookings(): void
    {
        $devCustomerUserId = 10; // dev.rodriguez2111@gmail.com
        $devCustomerAddress = Address::where('user_id', $devCustomerUserId)->first();
        if (!$devCustomerAddress) return;

        $demoBookings = [
            [
                'worker_id' => 1, // John Reyes — johnlsf@gmail.com / password
                'status' => 'pending',
                'scheduled_at' => now()->addDays(2)->setTime(10, 0),
            ],
            [
                'worker_id' => 2, // Joseph Santos — josephlsf@gmail.com / password
                'status' => 'accepted',
                'scheduled_at' => now()->addDay()->setTime(14, 0),
            ],
        ];

        foreach ($demoBookings as $i => $b) {
            $exists = Booking::where('user_id', $devCustomerUserId)
                ->where('worker_id', $b['worker_id'])
                ->whereIn('status', ['pending', 'accepted'])
                ->exists();
            if ($exists) continue;

            $service = Service::where('worker_id', $b['worker_id'])->first();
            if (!$service) continue;

            Booking::create([
                'user_id' => $devCustomerUserId,
                'service_id' => $service->id,
                'worker_id' => $b['worker_id'],
                'address_id' => $devCustomerAddress->id,
                'status' => $b['status'],
                'scheduled_at' => $b['scheduled_at'],
                'total_price' => $service->price,
                'payment_method' => 'cash',
                'payment_status' => 'pending',
                'idempotency_key' => "seed-demo-{$devCustomerUserId}-{$i}",
            ]);
        }
    }
}
