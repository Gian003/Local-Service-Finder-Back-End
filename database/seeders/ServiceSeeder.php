<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'worker_id' => 1,
                'title' => 'House Cleaning',
                'description' => 'House Cleaning',
                'price' => 1500,
                'category' => 'cleaning',
                'image_url' => 'https://picsum.photos/seed/cleaning/400',
                'discount_percent' => 30,
            ],

            [
                'worker_id' => 2,
                'title' => 'Plumbing',
                'description' => 'Plumbing',
                'price' => 1500,
                'category' => 'plumbing',
                'image_url' => 'https://picsum.photos/seed/plumbing/400',
                'discount_percent' => 30,
            ],

            [
                'worker_id' => 3,
                'title' => 'Appliance Repair',
                'description' => 'Repair',
                'price' => 1500,
                'category' => 'repair',
                'image_url' => 'https://picsum.photos/seed/repair/400',
                'discount_percent' => 30,
            ],

            [
                'worker_id' => 4,
                'title' => 'Roof Cleaning',
                'description' => 'Roof Cleaning',
                'price' => 1500,
                'category' => 'roofing',
                'image_url' => 'https://picsum.photos/seed/roofing/400',
                'discount_percent' => 30,
            ],

            [
                'worker_id' => 5,
                'title' => 'Furniture Assembly',
                'description' => 'Furniture Assembly',
                'price' => 1500,
                'category' => 'furnitrure',
                'image_url' => 'https://picsum.photos/seed/furniture/400',
                'discount_percent' => null,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
