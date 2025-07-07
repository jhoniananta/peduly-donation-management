<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class DonationInstruction extends Mailable
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
            subject: 'Satu Langkah Lagi untuk Menyelesaikan Donasimu! 💗',
        );
    }

    /**
     * Get the message content definition.
     */
    public function build()
    {
        return $this->view('mail.fundraising_howto')
            ->with([
                'donor_name' => $this->donor->name,
                'donor_email' => $this->donor->email,
                'amount' => $this->donation->total,
                'campaign_name' => $this->fundraising->name,
                'qris_image_url' => $this->donation->payment_link,
                'expired_at' => Carbon::parse($this->donation->expiring_time)->format('H:i:s Y-M-d '),
                'company_name' => $this->fundraising->company->name,
                'company_logo' => $this->fundraising->company->logo ? asset('storage/' . $this->fundraising->company->logo) : null,
            ]);
    }
}
