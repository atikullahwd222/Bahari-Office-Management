<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployeeSalaryPaidNotification extends Mailable
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
            view: 'emails.selary-email',
            with: [
            'type' => $this->data['type'] ?? 'bulk_payment',
            'name' => $this->data['name'] ?? 'Employee',
            // 'company_name' => $this->data['company_name'] ?? 'Employee',
            'month' => $this->data['month'] ?? 'N/A',
            'amount' => $this->data['amount'] ?? '0.00',
            'total_amount' => $this->data['total_amount'] ?? '0.00',
            'payment_date' => $this->data['payment_date'] ?? 'N/A',
            'payment_method' => $this->data['payment_method'] ?? 'N/A',
            'payroll_count' => $this->data['payroll_count'] ?? 0,
            'payroll_details' => collect($this->data['payroll_details'] ?? [])->map(function($payroll) {
            return [
            'type' => $payroll['type'] ?? 'N/A',
            'reference_id' => $payroll['reference_id'] ?? 'N/A',
            'purpose' => $payroll['purpose'] ?? 'N/A',
            'pay_to' => $payroll['pay_to'] ?? 'N/A',
            'due_date' => $payroll['due_date'] ?? 'N/A',
            'status' => $payroll['status'] ?? 'N/A',
            'previous_status' => $payroll['previous_status'] ?? 'N/A',
            'amount' => $payroll['amount'] ?? '0.00',
            'payment_date' => $payroll['payment_date'] ?? 'N/A'
            ];
            })->toArray()
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
