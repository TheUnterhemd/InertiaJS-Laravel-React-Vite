<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Email extends Mailable
{
    use Queueable, SerializesModels;

    protected $maildata;

    /**
     * Create a new message instance.
     *
     * @param array $maildata The data to be used in the email.
     */
    public function __construct(array $maildata)
    {
        $this->maildata = $maildata;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Your new Book')
                    ->view('emails.book_email')
                    ->with('maildata', $this->maildata);
    }
}
