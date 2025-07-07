<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;
    private $token, $email;
    /**
     * Create a new message instance.
     */
    public function __construct($token, $email)
    {
        //
        $this->token = $token;
        $this->email = $email;
    }

    public function build()
    {   
        // change url to frontend url
        $activate_url = 'http://localhost:8000/api/user/reset?token='.$this->token.'&email='.$this->email;
        
        return $this->view('mail.reset')
                    ->with(['activate_url' => $activate_url]);
    }
}
