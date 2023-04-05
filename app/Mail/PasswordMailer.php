<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;

class PasswordMailer extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct( $email)
    {
        $this->email = $email;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $encryptedValue = Crypt::encryptString($this->email);
        $url = 'http://13.231.134.208/password-reset?data=' . urlencode($encryptedValue);
        return $this->view('modules.mail.passwordmail')->subject('Request password reset from cafe App')->with(['url' => $url]);
    }
}
