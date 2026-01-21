<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $task;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, Task $task)
    {
        $this->invoice = $invoice;
        $this->task = $task;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Invoice #' . $this->invoice->invoice_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'task' => $this->task,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Generate PDF
        $pdf = Pdf::loadView('tasks.invoices.print', [
            'task' => $this->task,
            'invoice' => $this->invoice,
        ]);

        return [
            Attachment::fromData(fn () => $pdf->output(), 'invoice-' . $this->invoice->invoice_number . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
