<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Events\InvoiceEmailEvent;

class InvoiceController extends Controller
{
    /**
     * Display invoices for a specific task
     */
    public function index($taskId)
    {
        $task = Task::with('project')->findOrFail($taskId);
        
        // Get invoices related to this task (we'll need to add task_id to invoices)
        $invoices = Invoice::where('task_id', $taskId)
            ->with(['customer', 'items', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tasks.invoices.index', compact('task', 'invoices'));
    }

    /**
     * Show the form for creating a new invoice for a task
     */
    public function create($taskId)
    {
        $task = Task::with('project.teamMembers')->findOrFail($taskId);
        
        // Get all active customers
        $customers = Customer::where('status', 'Active')->get();
        
        // Get team members for person responsible dropdown
        $teamMembers = $task->project->teamMembers;
        
        // Get sales tax rate from config or settings
        $sales_tax = config('app.sales_tax', 0.0825); // Default 8.25%
        
        $invoiceItems = [];
        $jsonInvoiceItems = json_encode($invoiceItems);
        
        // Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber();

        return view('tasks.invoices.create', [
            'task' => $task,
            'customers' => $customers,
            'teamMembers' => $teamMembers,
            'invoiceItems' => $jsonInvoiceItems,
            'sales_tax' => $sales_tax,
            'invoiceNumber' => $invoiceNumber,
        ]);
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request, $taskId)
    {
        $validated = $request->validate([
            'customer_email' => 'required|email',
            'customer_data' => 'required|json',
            'invoice_number' => 'required|string',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'subtotal' => 'required|numeric',
            'tax' => 'required|numeric',
            'total' => 'required|numeric',
            'invoice_notes' => 'nullable|string',
            'invoice_data' => 'required|json',
        ]);

        DB::beginTransaction();

        try {
            $task = Task::findOrFail($taskId);
            $invoiceItems = json_decode($validated['invoice_data'], true) ?? [];
            $customerData = json_decode($validated['customer_data'], true);

            // Find or create customer based on email from service call
            $customer = Customer::where('email', $validated['customer_email'])->first();

            if (!$customer) {
                // Create new customer from service call data
                $customer = Customer::create([
                    'first_name' => $customerData['firstName'] ?? '',
                    'last_name' => $customerData['lastName'] ?? '',
                    'email' => $validated['customer_email'],
                    'phone' => $customerData['phone'] ?? null,
                    'company_name' => $customerData['company'] ?? null,
                    'status' => 'Active',
                    'customer_type' => 'individual',
                    'created_by' => Auth::id(),
                ]);
            }

            $invoice = Invoice::create([
                'task_id' => $task->id,
                'invoice_number' => $validated['invoice_number'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'customer_id' => $customer->id,
                'invoice_created_by' => Auth::id(),
                'subtotal' => $validated['subtotal'],
                'sales_tax' => $validated['tax'],
                'total' => $validated['total'],
                'invoice_notes' => $validated['invoice_notes'] ?? null,
                'invoice_status' => 'pending',
            ]);

            // Save invoice items with invoice_type = 1 for project_manager
            foreach ($invoiceItems as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'invoice_type' => 1, // Project Manager
                    'type' => $item['type'] ?? null,
                    'item_name' => $item['name'] ?? null,
                    'item_id' => $item['id'] ?? null,
                    'qty' => $item['qty'] ?? 1,
                    'sku' => $item['sku'] ?? null,
                    'unit' => $item['unit'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'total' => $item['total'] ?? 0,
                    'extras' => $item['extras'] ?? null,
                    'notes' => $item['notes'] ?? null,
                    'reference' => $item['reference'] ?? null,
                    'responsible_person_id' => $item['responsible_id'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully.',
                'invoice_id' => $invoice->id,
                'redirect_url' => route('tasks.show', $task->id),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            \Log::error('Invoice creation failed', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'task_id' => $taskId,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified invoice
     */
    public function show($taskId, $invoiceId)
    {
        $task = Task::with('project')->findOrFail($taskId);
        $invoice = Invoice::with(['customer', 'items', 'creator'])
            ->where('task_id', $taskId)
            ->findOrFail($invoiceId);

        return view('tasks.invoices.show', compact('task', 'invoice'));
    }

    /**
     * Download invoice as PDF
     */
    public function download($taskId, $invoiceId)
    {
        $task = Task::with('project')->findOrFail($taskId);
        $invoice = Invoice::with(['customer', 'items', 'creator'])
            ->where('task_id', $taskId)
            ->findOrFail($invoiceId);

        // Generate PDF from print view
        $pdf = Pdf::loadView('tasks.invoices.print', [
            'task' => $task,
            'invoice' => $invoice,
        ]);

        // Set paper size and orientation
        $pdf->setPaper('letter', 'portrait');

        // Download the PDF
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Send invoice via email
     */
    public function email($taskId, $invoiceId)
    {
        try {
            $task = Task::with('project')->findOrFail($taskId);
            $invoice = Invoice::with(['customer', 'items', 'creator'])
                ->where('task_id', $taskId)
                ->findOrFail($invoiceId);

            // Check if customer has an email
            if (!$invoice->customer->email) {
                return redirect()->route('tasks.invoices.show', [$taskId, $invoiceId])
                    ->with('error', 'Customer does not have an email address.');
            }

            // Dispatch event — Laravel will automatically resolve the listener and inject MailService
            event(new InvoiceEmailEvent($invoice, $task));

            // If no exception -> success
            return redirect()->route('tasks.invoices.show', [$taskId, $invoiceId])
                ->with('success', 'Invoice email sent successfully to ' . $invoice->customer->email);

        } catch (\Throwable $e) {
            // If listener threw exception -> failure
            Log::error('InvoiceController: Failed to send invoice email', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'invoice_id' => $invoiceId,
                'task_id' => $taskId,
            ]);

            return redirect()->route('tasks.invoices.show', [$taskId, $invoiceId])
                ->with('error', 'Something went wrong while sending the invoice email.');
        }
    }

    /**
     * Generate a unique invoice number
     */
    private function generateInvoiceNumber()
    {
        $year = date('Y');
        $lastInvoice = Invoice::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice && preg_match('/INV-' . $year . '-(\d+)/', $lastInvoice->invoice_number, $matches)) {
            $number = intval($matches[1]) + 1;
        } else {
            $number = 1;
        }

        return 'INV-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
