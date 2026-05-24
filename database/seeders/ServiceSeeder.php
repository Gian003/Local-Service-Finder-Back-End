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
                'image_url' => 'services/CleaningService.jpg',
                'discount_percent' => 30,
            ],

            [
                'worker_id' => 2,
                'title' => 'Plumbing',
                'description' => 'Plumbing',
                'price' => 1500,
                'category' => 'plumbing',
                'image_url' => 'services/Plumbing.jpg',
                'discount_percent' => 30,
            ],

            [
                'worker_id' => 3,
                'title' => 'Appliance Repair',
                'description' => 'Repair',
                'price' => 1500,
                'category' => 'repair',
                'image_url' => 'services/ApplianceRepair.jpg',
                'discount_percent' => 30,
            ],

            [
                'worker_id' => 4,
                'title' => 'Roof Cleaning',
                'description' => 'Roof Cleaning',
                'price' => 1500,
                'category' => 'roofing',
                'image_url' => 'services/RoofingGutterCleaning.jpg',
                'discount_percent' => 30,
            ],

            [
                'worker_id' => 5,
                'title' => 'Furniture Assembly',
                'description' => 'Furniture Assembly',
                'price' => 1500,
                'category' => 'electrician',
                'image_url' => 'services/Electrician.jpg',
                'discount_percent' => null,
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
