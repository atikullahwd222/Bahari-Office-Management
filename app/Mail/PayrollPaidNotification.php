<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PayrollPaidNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    /**
     * Create a new message instance.
     *
     * @param array $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: 'Payroll Payment Notification',
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'emails.payroll-paid',
            with: [
                'name' => $this->data['name'] ?? 'Employee',
                'month' => $this->data['month'] ?? 'N/A',
                'amount' => $this->data['amount'] ?? '0.00',
                'total_amount' => $this->data['total_amount'] ?? '0.00',
                'payment_date' => $this->data['payment_date'] ?? 'N/A',
                'payment_method' => $this->data['payment_method'] ?? 'N/A',
                'payroll_details' => [
                    'type' => $this->data['payroll_details']['type'] ?? 'N/A',
                    'reference_id' => $this->data['payroll_details']['reference_id'] ?? 'N/A',
                    'due_date' => $this->data['payroll_details']['due_date'] ?? 'N/A',
                    'status' => $this->data['payroll_details']['status'] ?? 'N/A'
                ],
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}