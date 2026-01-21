@extends('layouts.dashboard')

@section('title', 'View Invoice - ' . $invoice->invoice_number)

@section('content')

@push('styles')
<style>
    @media print {
        /* allow normal scrolling and page breaking */
        html,
        body {
            overflow: visible !important;
            height: auto !important;
        }

        /* IMPORTANT: allow the main container to break */
        .min-h-screen.flex {
            display: block !important;
        }

        #printable-area {
            max-width: none !important;
            width: 100% !important;
            overflow: visible !important;
        }

        #printable-area .grid {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 1.5rem !important;
        }

        table,
        thead,
        tbody,
        tr,
        td,
        th {
            page-break-inside: auto !important;
        }

        thead {
            display: table-header-group !important;
        }

        tfoot {
            display: table-footer-group !important;
        }

        tr {
            page-break-inside: avoid !important;
            page-break-after: auto !important;
        }

        /* remove any overflow:hidden globally */
        * {
            overflow: visible !important;
        }

        /* hide stuff not for print */
        .no-print {
            display: none !important;
        }
    }
</style>
@endpush

<div class="bg-white border-b border-gray-200 px-4 mb-4 py-4 border-b border-gray-200 no-print">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 max-w-6xl mx-auto ">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
            <a href="{{ route('tasks.show', $task->id) }}" class="flex items-center text-gray-600 hover:text-gray-800 transition">
                <svg class="w-5 h-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"></path>
                </svg>
                <span class="text-sm font-medium">Back to Task</span>
            </a>
            <div class="hidden sm:block h-6 border-l border-gray-300"></div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Invoice Details</h1>
                <p class="text-sm text-gray-500">{{ $invoice->customer->full_name ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            <button onclick="window.close()" type="button" class="inline-flex items-center px-6 py-2 rounded-md text-gray-700 bg-white text-sm font-medium shadow transition hover:bg-gray-50">
                Cancel
            </button>
        </div>
    </div>
</div>

<div class="min-h-screen flex flex-col items-center">
    <!-- Header -->
    <div class="text-center mb-6 no-print">
        <h2 class="text-xl font-semibold">Receipt Preview</h2>
        <p class="text-gray-500 text-sm">
            This is how your receipt will look when printed on 8.5" × 11" paper
        </p>
    </div>

    <!-- Receipt Card -->
    <div class="bg-white w-full max-w-3xl shadow-md rounded-md p-6" id="printable-area">
        <!-- Top Section -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <div class="flex items-center space-x-3">
                <div class="w-15 h-15 flex items-center justify-center rounded-lg text-white">
                    @if(file_exists(public_path('storage/logo.png')))
                        <img class="dark:hidden w-20" src="{{ asset('storage/logo.png') }}" alt="Logo" />
                    @elseif(file_exists(public_path('images/logo.png')))
                        <img class="dark:hidden w-20" src="{{ asset('images/logo.png') }}" alt="Logo" />
                    @else
                        <div class="w-20 h-20 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold text-2xl">
                            {{ strtoupper(substr(config('app.company_name', 'PM'), 0, 2)) }}
                        </div>
                    @endif
                </div>
                <div>
                    <h3 class="font-semibold text-2xl">{{ config('app.company_name', 'Project Manager') }}</h3>
                    <p class="text-sm text-gray-500">{{ config('app.company_tagline', 'Professional Service') }}</p>
                </div>
            </div>
            <div class="text-right mt-4 sm:mt-0">
                <h3 class="text-2xl font-bold">RECEIPT</h3>
                <p class="text-sm text-gray-500">#{{ $invoice->invoice_number }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm border-b-2 border-gray-800 pb-4 mb-6">
            <!-- Left Column -->
            <div class="text-gray-600 space-y-1">
                <p class="font-medium text-gray-700 text-lg">From:</p>
                <p>{{ config('app.company_name', "Rent 'n King .") }}</p>
                @if(config('app.company_address'))
                    <p>{{ config('app.company_address', "10296 Highway 46") }}</p>
                @endif
                @if(config('app.company_city'))
                    <p>{{ config('app.company_city', "Bon Aqua, TN 37025") }}</p>
                @endif
                @if(config('app.company_phone'))
                    <p>{{ config('app.company_phone') }}</p>
                @endif
                @if(config('app.company_email'))
                    <p>{{ config('app.company_email') }}</p>
                @endif
            </div>
            <!-- Right Column -->
            <div class="space-y-3">
                <div>
                    <p class="flex items-center gap-2 font-semibold text-gray-900">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-gray-600">
                            <path d="M8 2v4"></path>
                            <path d="M16 2v4"></path>
                            <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                            <path d="M3 10h18"></path>
                        </svg>
                        Invoice Date: {{ $invoice->invoice_date->format('M d, Y') }}
                    </p>
                </div>

                <div>
                    <p class="flex items-center gap-2 font-semibold text-gray-900">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-gray-600">
                            <path d="M8 2v4"></path>
                            <path d="M16 2v4"></path>
                            <rect width="18" height="18" x="3" y="4" rx="2"></rect>
                            <path d="M3 10h18"></path>
                        </svg>
                        Due Date: {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'Pay Upon Receipt' }}
                    </p>
                </div>

                <div>
                    <p class="font-semibold text-gray-900">Task Reference:</p>
                    <p class="text-lg font-medium text-gray-900">{{ $task->title }}</p>
                </div>
            </div>
        </div>

        <!-- Bill To Label -->
        <div>
            <p class="font-medium mb-2 flex items-center space-x-2 text-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Bill To:</span>
            </p>
        </div>
        
        <!-- Customer Card -->
        <div class="p-4">
            <p class="font-medium text-lg text-gray-900">{{ $invoice->customer->full_name ?? 'N/A' }}</p>
            <p class="text-gray-700 font-medium">{{ $invoice->customer->company_name ?? '' }}</p>
            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm text-gray-700">
                <!-- Phone -->
                <div class="flex text-md items-center space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    <span>{{ $invoice->customer->phone ?? 'N/A' }}</span>
                </div>

                <!-- Email -->
                <div class="flex items-start space-x-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4">
                        <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                    </svg>
                    <span>{{ $invoice->customer->email ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <!-- Table Head -->
                <thead>
                    <tr class="border-b border-gray-300 mb-4">
                        <th class="text-left py-2 font-semibold text-gray-700 whitespace-nowrap">Item</th>
                        <th class="text-center py-2 font-semibold text-gray-700 whitespace-nowrap">Qty</th>
                        <th class="text-right py-2 font-semibold text-gray-700 whitespace-nowrap">Unit Price</th>
                        <th class="text-right py-2 font-semibold text-gray-700 whitespace-nowrap">Total</th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody>
                    @foreach($invoice->items as $item)
                    {{-- Main Item Row --}}
                    <tr>
                        <td class="pt-2 pb-2 align-top whitespace-nowrap">
                            <div class="font-medium text-gray-900">{{ $item->item_name }}</div>
                            @if($item->notes)
                                <div class="text-xs text-gray-500 mt-1">{{ $item->notes }}</div>
                            @endif
                        </td>
                        <td class="pt-2 text-center align-top whitespace-nowrap">
                            {{ $item->qty ?? 1 }}
                        </td>
                        <td class="pt-2 text-right align-top whitespace-nowrap">
                            ${{ number_format($item->unit ?? 0, 2) }}
                        </td>
                        <td class="pt-2 text-right align-top font-semibold whitespace-nowrap">
                            @php
                                $rowPrice = $item->unit ?? 0;
                                $qty = $item->qty ?? 1;
                                $total = $rowPrice * $qty;
                            @endphp
                            ${{ number_format($total, 2) }}
                        </td>
                    </tr>
                    <tr class="border-b"></tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Section -->
        <div class="mt-6 text-sm">
            <div class="flex justify-between py-1">
                <span class="text-gray-700 text-md">Subtotal:</span>
                <span>${{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            <div class="flex justify-between py-1">
                <span class="text-gray-700">Tax ({{ number_format((config('app.sales_tax', 0.0825) * 100), 2) }}%):</span>
                <span>${{ number_format($invoice->sales_tax, 2) }}</span>
            </div>
            <div class="flex justify-between border-t mt-2 pt-2 font-bold text-lg">
                <span>Total:</span>
                <span>${{ number_format($invoice->total, 2) }}</span>
            </div>
        </div>

        <!-- Invoice Notes -->
        @if($invoice->invoice_notes)
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <p class="font-medium text-gray-700 mb-2">Notes:</p>
            <p class="text-gray-600 text-sm whitespace-pre-line">{{ $invoice->invoice_notes }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="mt-6 text-center text-sm text-gray-500 border-t border-gray-800">
            <p class="mt-6">Thank you for your business!</p>
            <p>For questions about this receipt, contact us at {{ config('app.company_phone', config('app.company_email')) }}</p>
        </div>
        
        <!-- Buttons -->
        <div class="mt-6 flex flex-col sm:flex-row justify-center gap-3 no-print">
            {{-- Print Receipt --}}
            <button onclick="window.print();" class="bg-blue-600 hover:bg-blue-700 text-white gap-2 text-sm px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Print Receipt
            </button>

            {{-- Email Receipt --}}
            <a href="{{ route('tasks.invoices.email', [$task->id, $invoice->id]) }}" class="bg-green-600 hover:bg-green-700 text-white gap-2 text-sm px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Email Receipt
            </a>
            
            {{-- Download PDF --}}
            <a href="{{ route('tasks.invoices.download', [$task->id, $invoice->id]) }}" class="bg-purple-600 hover:bg-purple-700 text-white gap-2 text-sm px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Download PDF
            </a>
            
            
        </div>
    </div>
</div>

@endsection
