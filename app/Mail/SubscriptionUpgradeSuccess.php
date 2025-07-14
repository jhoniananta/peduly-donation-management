<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionUpgradeSuccess extends Mailable
{
    use Queueable, SerializesModels;

    public $subscriptionUser;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct($subscriptionUser, $user)
    {
        $this->subscriptionUser = $subscriptionUser;
        $this->user = $user;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('🎉 Upgrade Subscription Berhasil - Peduly Donation')
            ->view('emails.subscription-upgrade-success')
            ->with([
                'subscriptionUser' => $this->subscriptionUser,
                'user' => $this->user,
                'subscription' => $this->subscriptionUser->subscription ?? null
            ]);
    }
}
