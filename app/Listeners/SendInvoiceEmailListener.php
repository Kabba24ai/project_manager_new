<?php

namespace App\Listeners;

use App\Events\InvoiceEmailEvent;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use App\Services\MailService;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class SendInvoiceEmailListener
{
    protected MailService $mailService;

    public function __construct(MailService $mailService)
    {
        // Inject the MailService
        $this->mailService = $mailService;
    }

    public function handle(InvoiceEmailEvent $event)
    {
        $invoice = $event->invoice;
        $task = $event->task;

        try {
            // Generate PDF
            $pdf = Pdf::loadView('tasks.invoices.print', [
                'invoice'   => $invoice,
                'task'      => $task,
            ]);

            $pdfData = $pdf->output();

            // Get mail settings from MailService
            $settings = $this->mailService->getSettings();

            // Prepare from address
            $fromAddress = new Address($settings['from']['address'], $settings['from']['name']);

            $email = (new Email())
                ->from($fromAddress)
                ->to($invoice->customer->email)
                ->subject('Your Invoice #' . $invoice->invoice_number)
                ->text('Please check your attached invoice.')
                ->attach($pdfData, 'invoice-' . $invoice->invoice_number . '.pdf', 'application/pdf');

            // Prepare Symfony DSN for sending
            $dsn = sprintf(
                '%s://%s:%s@%s:%s',
                $settings['mailer'],
                urlencode($settings['username']),
                urlencode($settings['password']),
                $settings['host'],
                $settings['port']
            );

            $transport = Transport::fromDsn($dsn);
            $mailer = new Mailer($transport);

            // Send the email
            $mailer->send($email);

            // Update invoice only if sent successfully
            $invoice->is_email_send = 'send';
            $invoice->mail_send_at = now();
            $invoice->save();

        } catch (\Throwable $e) {
            Log::error('SendInvoiceEmailListener: Failed to send invoice email', [
                'invoice_id' => $invoice->id,
                'customer_email' => $invoice->customer->email,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Rethrow the exception so controller knows it failed
            throw $e;
        }
    }
}
