<?php

namespace Database\Seeders;

use App\Models\Worker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WorkerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $workers = [
            [
                'first_name' => 'John',
                'last_name' => 'Reyes',
                'email' => 'johnlsf@gmail.com',
                'category' => 'cleaning',
                'profile_photo' => 'workers/CleaningService.jpg',
                'description' => 'Experienced cleaner with a passion for making spaces shine.',
                'rating' => 4.5,
                'review_count' => 150,
            ],

            [
                'first_name' => 'Joseph',
                'last_name' => 'Santos',
                'email' => 'josephlsf@gmail.com',
                'category' => 'plumbing',
                'profile_photo' => 'workers/Plumbing.jpg',
                'description' => 'Skilled plumber with years of experience in residential and commercial installations.',
                'rating' => 4.8,
                'review_count' => 100
            ],

            [
                'first_name' => 'Henry',
                'last_name' => 'Cruz',
                'email' => 'henrylsf@gmail.com',
                'category' => 'repair',
                'profile_photo' => 'workers/ApplianceRepair.jpg',
                'description' => 'Experienced repair specialist with a keen eye for detail.',
                'rating' => 4.7,
                'review_count' => 200
            ],

            [
                'first_name' => 'William',
                'last_name' => 'Tan',
                'email' => 'williamlsf@gmail.com',
                'category' => 'roofing',
                'profile_photo' => 'workers/RoofingGutterCleaning.jpg',
                'description' => 'Skilled roofer with expertise in various roofing materials and techniques.',
                'rating' => 4.6,
                'review_count' => 120
            ],

            [
                'first_name' => 'Thomas',
                'last_name' => 'Garcia',
                'email' => 'thomaslsf@gmail.com',
                'category' => 'electrical',
                'profile_photo' => 'workers/Electrician.jpg',
                'description' => 'Experienced electrician with a focus on residential and commercial electrical work.',
                'rating' => 4.8,
                'review_count' => 250
            ],
        ];

        foreach ($workers as $i => $worker) {
            Worker::create([
                'first_name' => $worker['first_name'],
                'last_name' => $worker['last_name'],
                'email' => $worker['email'],
                'password' => Hash::make('password'),
                'category' => $worker['category'],
                'profile_photo' => $worker['profile_photo'],
                'description' => $worker['description'],
                'is_available' => true,
                'rating' => $worker['rating'],
                'review_count' => $worker['review_count'],
            ]);
        }
    }
}
