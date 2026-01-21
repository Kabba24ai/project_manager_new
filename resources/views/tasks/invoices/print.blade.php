<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        
        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }
        
        .header-right {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: top;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .company-info {
            font-size: 11px;
            color: #666;
        }
        
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .invoice-number {
            font-size: 11px;
            color: #666;
        }
        
        .info-section {
            display: table;
            width: 100%;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .info-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        
        .info-label {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .info-item {
            margin-bottom: 8px;
        }
        
        .bill-to {
            margin-bottom: 20px;
        }
        
        .bill-to-title {
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 8px;
        }
        
        .customer-name {
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 3px;
        }
        
        .customer-info {
            font-size: 11px;
            color: #555;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        thead th {
            background-color: #f5f5f5;
            padding: 10px;
            text-align: left;
            border-bottom: 2px solid #333;
            font-weight: bold;
        }
        
        tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .totals {
            width: 300px;
            margin-left: auto;
            margin-top: 20px;
        }
        
        .total-row {
            display: table;
            width: 100%;
            padding: 5px 0;
        }
        
        .total-label {
            display: table-cell;
            text-align: left;
        }
        
        .total-value {
            display: table-cell;
            text-align: right;
        }
        
        .grand-total {
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
            font-weight: bold;
            font-size: 16px;
        }
        
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 3px solid #333;
        }
        
        .notes-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #333;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="logo">{{ config('app.company_name', 'Project Manager') }}</div>
                <div class="company-info">
                    {{ config('app.company_tagline', 'Professional Project Management') }}<br>
                    @if(config('app.company_address'))
                        {{ config('app.company_address') }}<br>
                    @endif
                    @if(config('app.company_city'))
                        {{ config('app.company_city') }}<br>
                    @endif
                    @if(config('app.company_phone'))
                        Phone: {{ config('app.company_phone') }}<br>
                    @endif
                    @if(config('app.company_email'))
                        Email: {{ config('app.company_email') }}
                    @endif
                </div>
            </div>
            <div class="header-right">
                <div class="invoice-title">RECEIPT</div>
                <div class="invoice-number">#{{ $invoice->invoice_number }}</div>
            </div>
        </div>

        <!-- Invoice Info -->
        <div class="info-section">
            <div class="info-left">
                <div class="info-label">From:</div>
                <div class="company-info">
                    {{ config('app.company_name', 'Project Manager') }}<br>
                    @if(config('app.company_address'))
                        {{ config('app.company_address') }}<br>
                    @endif
                    @if(config('app.company_city'))
                        {{ config('app.company_city') }}<br>
                    @endif
                </div>
            </div>
            <div class="info-right">
                <div class="info-item">
                    <strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}
                </div>
                <div class="info-item">
                    <strong>Due Date:</strong> {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Pay Upon Receipt' }}
                </div>
                <div class="info-item">
                    <strong>Task Reference:</strong> {{ $task->title }}
                </div>
            </div>
        </div>

        <!-- Bill To -->
        <div class="bill-to">
            <div class="bill-to-title">Bill To:</div>
            <div class="customer-name">{{ $invoice->customer->full_name ?? 'N/A' }}</div>
            @if($invoice->customer->company_name)
                <div class="customer-info">{{ $invoice->customer->company_name }}</div>
            @endif
            <div class="customer-info">
                @if($invoice->customer->phone)
                    Phone: {{ $invoice->customer->phone }}<br>
                @endif
                @if($invoice->customer->email)
                    Email: {{ $invoice->customer->email }}
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td>
                        <strong>{{ $item->item_name }}</strong>
                        @if($item->notes)
                            <br><small style="color: #666;">{{ $item->notes }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->qty ?? 1 }}</td>
                    <td class="text-right">${{ number_format($item->unit ?? 0, 2) }}</td>
                    <td class="text-right">
                        @php
                            $rowPrice = $item->unit ?? 0;
                            $qty = $item->qty ?? 1;
                            $total = $rowPrice * $qty;
                        @endphp
                        <strong>${{ number_format($total, 2) }}</strong>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <div class="total-row">
                <div class="total-label">Subtotal:</div>
                <div class="total-value">${{ number_format($invoice->subtotal, 2) }}</div>
            </div>
            <div class="total-row">
                <div class="total-label">Tax ({{ number_format((config('app.sales_tax', 0.0825) * 100), 2) }}%):</div>
                <div class="total-value">${{ number_format($invoice->sales_tax, 2) }}</div>
            </div>
            <div class="total-row grand-total">
                <div class="total-label">Total:</div>
                <div class="total-value">${{ number_format($invoice->total, 2) }}</div>
            </div>
        </div>

        <!-- Notes -->
        @if($invoice->invoice_notes)
        <div class="notes">
            <div class="notes-title">Notes:</div>
            <div>{{ $invoice->invoice_notes }}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>For questions about this receipt, contact us at {{ config('app.company_phone', config('app.company_email')) }}</p>
        </div>
    </div>
</body>
</html>
