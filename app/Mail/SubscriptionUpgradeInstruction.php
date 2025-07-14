<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionUpgradeInstruction extends Mailable
{
  use Queueable, SerializesModels;

  public $subscriptionUser;
  public $user;
  public $subscription;

  /**
   * Create a new message instance.
   */
  public function __construct($subscriptionUser, $user, $subscription)
  {
    $this->subscriptionUser = $subscriptionUser;
    $this->user = $user;
    $this->subscription = $subscription;
  }

  /**
   * Build the message.
   */
  public function build()
  {
    return $this->subject('💳 Instruksi Pembayaran Upgrade Subscription - Peduly Donation')
      ->view('emails.subscription-upgrade-instruction')
      ->with([
        'subscriptionUser' => $this->subscriptionUser,
        'user' => $this->user,
        'subscription' => $this->subscription
      ]);
  }
}
