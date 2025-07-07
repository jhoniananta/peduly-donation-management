<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DonationExpired extends Mailable
{
    use Queueable, SerializesModels;

    private $donation, $donor, $fundraising;
    /**
     * Create a new message instance.
     */
    public function __construct($donation, $donor, $fundraising)
    {
        $this->donation = $donation;
        $this->donor = $donor;
        $this->fundraising = $fundraising;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pembayaran Donasimu ke " . $this->fundraising->company->name . " telah Kadaluwarsa ",
        );
    }
    /**
     * Get the message content definition.
     */
    function build()
    {
        return $this->view('mail.fundraising_unpaidexpired')
            ->with([
                'donor_name' => $this->donor->name,
                'donor_email' => $this->donor->email,
                'amount' => $this->donation->total,
                'campaign_name' => $this->fundraising->name,
                'donate_again_url' => $this->fundraising->company->link_default,
                'company_name' => $this->fundraising->company->name,
                'company_logo' => $this->fundraising->company->logo ? asset('storage/' . $this->fundraising->company->logo) : null,
            ]);
    }
}
