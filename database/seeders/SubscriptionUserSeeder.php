<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Subscription;
use App\Models\SubscriptionUser;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SubscriptionUserSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    // Get the free subscription plan
    $freePlan = Subscription::where('plan', 'Free')->first();

    if ($freePlan) {
      // Get all users and assign them to free plan by default
      $users = User::whereDoesntHave('subscriptionUsers')->get();

      foreach ($users as $user) {
        SubscriptionUser::create([
          'user_id' => $user->id,
          'subscription_id' => $freePlan->id,
          'status' => 'active',
          'start_date' => now(),
          'end_date' => now()->addYear(), // Free plan expires in 1 year
          'order_id' => 'FREE' . time() . $user->id,
          'amount' => 0,
          'payment_method' => 'free',
        ]);
      }

      $this->command->info('Assigned free subscription plan to all users without active subscriptions.');
    }

    // Example: Create a paid subscription for testing
    $basicPlan = Subscription::where('plan', 'Paid Basic')->first();
    $adminUser = User::where('email', 'admin@admin.com')->first();

    if ($basicPlan && $adminUser) {
      // Remove existing free subscription for admin
      SubscriptionUser::where('user_id', $adminUser->id)->delete();

      // Add basic plan for admin
      SubscriptionUser::create([
        'user_id' => $adminUser->id,
        'subscription_id' => $basicPlan->id,
        'status' => 'active',
        'start_date' => now(),
        'end_date' => now()->addMonth(),
        'order_id' => 'BASIC' . time() . $adminUser->id,
        'amount' => $basicPlan->price,
        'payment_method' => 'bank_transfer',
      ]);

      $this->command->info('Assigned basic subscription plan to admin user for testing.');
    }
  }
}
