<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetCode extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $resetCode;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $resetCode)
    {
        $this->user = $user;
        $this->resetCode = $resetCode;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Reset Your BiggestLogs Password 🔒')
                    ->view('emails.password-reset')
                    ->with([
                        'user' => $this->user,
                        'resetCode' => $this->resetCode,
                    ]);
    }
}

