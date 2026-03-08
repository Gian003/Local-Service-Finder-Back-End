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
            ['first_name' => 'John', 'last_name' => 'Reyes', 'category' => 'cleaning', 'email' => 'johnlsf@gmail.com'],
            ['first_name' => 'Joseph', 'last_name' => 'Santos', 'category' => 'plumbing', 'email' => 'josephlsf@gmail.com'],
            ['first_name' => 'Henry', 'last_name' => 'Cruz',  'category' => 'repair', 'email' => 'henrylsf@gmail.com'],
            ['first_name' => 'William', 'last_name' => 'Tan', 'category' => 'roofing', 'email' => 'williamlsf@gmail.com'],
            ['first_name' => 'Thomas', 'last_name' => 'Garcia', 'category' => 'furniture', 'email' => 'thomaslsf@gmail.com'],
        ];

        foreach ($workers as $i => $worker) {
            Worker::create([
                'first_name' => $worker['first_name'],
                'last_name' => $worker['last_name'],
                'email' => $worker['email'],
                'password' => Hash::make('password'),
                'profile_photo' => "https://picsum.photos/seed/worker{$i}/200",
                'category' => $worker['category'],
                'description' => "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
                'is_available' => true,
                'rating' => rand(40, 50) / 10,
                'review_count' => rand(100, 500),
            ]);
        }
    }
}
