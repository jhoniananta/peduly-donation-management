<?php

namespace App\Console\Commands;

use App\Models\SubscriptionUser;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessExpiredSubscriptions extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'subscription:process-expired';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Process expired subscriptions and send renewal notifications';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    $this->info('Processing expired subscriptions...');

    try {
      // Mark expired subscriptions
      $expiredCount = SubscriptionUser::where('status', 'active')
        ->where('end_date', '<', now())
        ->update(['status' => 'expired']);

      $this->info("Marked {$expiredCount} subscriptions as expired.");

      // Send notifications for subscriptions expiring in 7 days
      $expiringSoon = SubscriptionUser::where('status', 'active')
        ->with(['user', 'subscription'])
        ->whereBetween('end_date', [now(), now()->addDays(7)])
        ->get();

      foreach ($expiringSoon as $subscriptionUser) {
        try {
          $daysRemaining = now()->diffInDays($subscriptionUser->end_date);

          // Check if notification already sent for this subscription
          $existingNotification = Notification::where('user_id', $subscriptionUser->user_id)
            ->where('content', 'LIKE', '%subscription%akan berakhir%')
            ->where('created_at', '>=', now()->subDays(1))
            ->exists();

          if (!$existingNotification) {
            $content = "⚠️ Subscription plan {$subscriptionUser->subscription->plan} Anda akan berakhir dalam {$daysRemaining} hari. Silakan perpanjang untuk melanjutkan akses fitur premium.";

            Notification::create([
              'user_id' => $subscriptionUser->user_id,
              'status' => 'unread',
              'content' => $content
            ]);

            $this->info("Sent renewal notification to user ID: {$subscriptionUser->user_id}");
          }
        } catch (\Throwable $e) {
          Log::error("Error sending renewal notification for subscription ID {$subscriptionUser->id}: " . $e->getMessage());
        }
      }

      $this->info("Sent renewal notifications to {$expiringSoon->count()} users.");

      // Process pending payments that are expired
      $expiredPayments = SubscriptionUser::where('status', 'pending')
        ->where('expiring_time', '<', now())
        ->get();

      foreach ($expiredPayments as $payment) {
        $payment->update(['status' => 'payment_expired']);
        $this->info("Marked payment as expired for order ID: {$payment->order_id}");
      }

      $this->info("Processed {$expiredPayments->count()} expired payments.");
      $this->info('Subscription processing completed successfully!');
    } catch (\Throwable $th) {
      Log::error("Error processing expired subscriptions: " . $th->getMessage());
      $this->error("Error processing expired subscriptions: " . $th->getMessage());
    }
  }
}
