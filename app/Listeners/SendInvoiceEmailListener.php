<?php

namespace App\Listeners;

use App\Events\InvoiceEmailEvent;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\MailService;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use App\Mail\InvoiceEmail;

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

            // Try using Laravel Mail facade first (more reliable)
            try {
                Mail::to($invoice->customer->email)->send(new InvoiceEmail($invoice, $task));
                Log::info('Invoice email sent successfully using Laravel Mail', [
                    'invoice_id' => $invoice->id,
                    'customer_email' => $invoice->customer->email,
                ]);
            } catch (\Throwable $mailException) {
                // Fallback to Symfony Mailer if Laravel Mail fails
                Log::warning('Laravel Mail failed, trying Symfony Mailer', [
                    'error' => $mailException->getMessage()
                ]);
                
                // Validate required settings
                if (empty($settings['host']) || empty($settings['port'])) {
                    throw new \Exception('Mail host and port are required. Please configure mail settings.');
                }

                // Prepare Symfony DSN for sending
                // Format: smtp://user:pass@smtp.example.com:port?encryption=tls
                $encryption = $settings['encryption'] ?? 'tls';
                $username = !empty($settings['username']) ? urlencode($settings['username']) : '';
                $password = !empty($settings['password']) ? urlencode($settings['password']) : '';
                
                // Build DSN with authentication if credentials exist
                if (!empty($username) && !empty($password)) {
                    $dsn = sprintf(
                        '%s://%s:%s@%s:%s?encryption=%s',
                        $settings['mailer'] ?? 'smtp',
                        $username,
                        $password,
                        $settings['host'],
                        $settings['port'],
                        $encryption
                    );
                } else {
                    // DSN without authentication (for local/dev)
                    $dsn = sprintf(
                        '%s://%s:%s?encryption=%s',
                        $settings['mailer'] ?? 'smtp',
                        $settings['host'],
                        $settings['port'],
                        $encryption
                    );
                }

                $transport = Transport::fromDsn($dsn);
                $mailer = new Mailer($transport);

                // Send the email
                $mailer->send($email);
            }

            // Update invoice only if sent successfully
            $invoice->is_email_send = 'send';
            $invoice->mail_send_at = now();
            $invoice->save();

        } catch (\Throwable $e) {
            Log::error('SendInvoiceEmailListener: Failed to send invoice email', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_email' => $invoice->customer->email ?? 'N/A',
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'mail_settings' => [
                    'mailer' => $this->mailService->get('mailer'),
                    'host' => $this->mailService->get('host'),
                    'port' => $this->mailService->get('port'),
                    'has_username' => !empty($this->mailService->get('username')),
                    'has_password' => !empty($this->mailService->get('password')),
                ],
            ]);

            // Rethrow the exception so controller knows it failed
            throw $e;
        }
    }
}
