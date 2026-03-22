<?php

namespace App\Mail;

use App\Models\ClaimRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClaimRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ClaimRequest $claimRequest)
    {
    }

    public function build(): static
    {
        return $this->subject('Claim Request Rejected')
            ->view('emails.claim-rejected');
    }
}