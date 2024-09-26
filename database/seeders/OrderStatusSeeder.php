<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Seeder;
use Ranium\SeedOnce\Traits\SeedOnce;

class OrderStatusSeeder extends Seeder
{
    use SeedOnce;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OrderStatus::create([
            'status' => 'In queue',
            'slug' => 'in-queue',
            'end_status' => false,
        ]);

        OrderStatus::create([
            'status' => 'In production',
            'slug' => 'in-production',
            'end_status' => false,
        ]);

        OrderStatus::create([
            'status' => 'Available for shipping',
            'slug' => 'available-for-shipping',
            'end_status' => false,
        ]);

        OrderStatus::create([
            'status' => 'In transit to DC',
            'slug' => 'in-transit-to-dc',
            'end_status' => false,
        ]);

        OrderStatus::create([
            'status' => 'At DC',
            'slug' => 'at-dc',
            'end_status' => false,
        ]);

        OrderStatus::create([
            'status' => 'In transit to customer',
            'slug' => 'in-transit-to-customer',
            'end_status' => false,
        ]);

        OrderStatus::create([
            'status' => 'Completed',
            'slug' => 'completed',
            'end_status' => true,
        ]);

        OrderStatus::create([
            'status' => 'Rejection request',
            'slug' => 'rejection-request',
            'end_status' => false,
        ]);

        OrderStatus::create([
            'status' => 'Cancelled',
            'slug' => 'cancelled',
            'end_status' => true,
        ]);

        OrderStatus::create([
            'status' => 'Reprinted',
            'slug' => 'reprinted',
            'end_status' => true,
        ]);
    }
}
