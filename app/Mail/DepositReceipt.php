<?php

namespace App\Mail;

use App\Models\Transaction;
use App\Models\Deposit;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DepositReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public $transaction;

    public function __construct(Transaction|Deposit $transactionOrDeposit)
    {
        if ($transactionOrDeposit instanceof Deposit) {
            // Create a compatible object for the email template
            // The template expects $transaction with user, reference, amount, updated_at
            $this->transaction = (object) [
                'user' => $transactionOrDeposit->user,
                'reference' => $transactionOrDeposit->reference,
                'amount' => $transactionOrDeposit->amount,
                'updated_at' => $transactionOrDeposit->completed_at ?? $transactionOrDeposit->updated_at,
            ];
        } else {
            $this->transaction = $transactionOrDeposit;
        }
    }

    public function envelope(): Envelope
    {
        $reference = $this->deposit ? $this->deposit->reference : $this->transaction->reference;
        return new Envelope(
            subject: 'Deposit Successful - #' . $reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.deposit-receipt',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}


