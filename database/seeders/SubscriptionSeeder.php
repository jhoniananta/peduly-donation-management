<?php

namespace Database\Seeders;

use App\Models\Subscription;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Subscription::create([
            'plan' => 'Free',
            'feature' => '',
            'price' => 0,
        ]);

        Subscription::create([
            'plan' => 'Paid Basic',
            'feature' => '', // feature lebih lanjut
            'price' => 10000,
        ]);

        Subscription::create([
            'plan' => 'Paid Premmium',
            'feature' => '', // feature lebih lanjut
            'price' => 50000,
        ]);
    }
}
