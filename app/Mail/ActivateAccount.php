<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActivateAccount extends Mailable
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
        // $activate_url = route('activate_user', ['token' => $this->token,'email' => $this->email]);
        $activate_url = 'http://localhost:8000/api/user/activate?token='.$this->token.'&email='.$this->email;
        return $this->view('mail.activate')
                    ->with(['activate_url' => $activate_url]);
    }

    
}
