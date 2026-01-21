<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .content {
            padding: 20px 0;
        }
        .invoice-details {
            background-color: #f1f5f9;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .invoice-details p {
            margin: 5px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">{{ config('app.company_name', 'Project Manager') }}</div>
        <p style="margin: 0; color: #6b7280;">{{ config('app.company_tagline', 'Professional Service') }}</p>
    </div>

    <div class="content">
        <h2>Invoice Receipt</h2>
        
        <p>Dear {{ $invoice->customer->full_name ?? 'Valued Customer' }},</p>
        
        <p>Thank you for your business. Please find your invoice attached to this email.</p>
        
        <div class="invoice-details">
            <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</p>
            <p><strong>Due Date:</strong> {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Pay Upon Receipt' }}</p>
            <p><strong>Total Amount:</strong> ${{ number_format($invoice->total, 2) }}</p>
            <p><strong>Task Reference:</strong> {{ $task->title }}</p>
        </div>

        <p>The invoice has been attached as a PDF document. Please review it and contact us if you have any questions.</p>
        
        @if(config('app.company_phone') || config('app.company_email'))
        <p>If you have any questions, please contact us:</p>
        <ul style="list-style: none; padding: 0;">
            @if(config('app.company_phone'))
            <li>📞 Phone: {{ config('app.company_phone') }}</li>
            @endif
            @if(config('app.company_email'))
            <li>✉️ Email: {{ config('app.company_email') }}</li>
            @endif
        </ul>
        @endif
    </div>

    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.company_name', 'Project Manager') }}. All rights reserved.</p>
        @if(config('app.company_address'))
        <p>{{ config('app.company_address') }}@if(config('app.company_city')), {{ config('app.company_city') }}@endif</p>
        @endif
    </div>
</body>
</html>
