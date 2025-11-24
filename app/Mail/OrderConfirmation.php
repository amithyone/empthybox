<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $product = $this->order->product;
        $amount = $this->order->amount; // Amount in NGN
        
        return $this->subject('Order Confirmation - Order #' . $this->order->order_number)
                    ->view('emails.order-confirmation')
                    ->with([
                        'order' => $this->order,
                        'product' => $product,
                        'amount' => $amount,
                        'verificationUrl' => route('orders.show', $this->order),
                    ]);
    }
}

