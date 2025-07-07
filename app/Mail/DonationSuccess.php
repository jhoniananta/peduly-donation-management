<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DonationSuccess extends Mailable
{
    use Queueable, SerializesModels;

    private $donation;
    private $donor;
    private $fundraising;

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
            subject: "Yey, Donasimu Berhasil Tersalurkan! 😍",
        );
    }
    function build()
    {
        return $this->view('mail.fundraising_paidsuccess')
            ->with([
                'donor_name' => $this->donor->name,
                'donor_email' => $this->donor->email,
                'amount' => $this->donation->total,
                'campaign_name' => $this->fundraising->name,
                'company_name' => $this->fundraising->company->name,
                'company_logo' => $this->fundraising->company->logo ? asset('storage/' . $this->fundraising->company->logo) : null,
            ]);
    }
}
