@php
$invoiceNumber = 'INV-' . date('Y') . '-' . str_pad((\App\Models\Invoice::whereYear('created_at', date('Y'))->count() + 1), 4, '0', STR_PAD_LEFT);
$sales_tax = config('app.sales_tax', 0.0825);
$teamMembers = $task->project->teamMembers;
$orderId = $task->serviceCall->order_id ?? null;
@endphp

<form id="invoiceForm" method="POST" action="{{ route('tasks.invoices.store', $task->id) }}" class="space-y-6">
    @csrf
    
    <input type="hidden" name="invoice_data" id="invoiceDataInput" value="">
    <input type="hidden" name="subtotal" id="invoiceSubtotalInput" value="0">
    <input type="hidden" name="tax" id="invoiceTaxInput" value="0">
    <input type="hidden" name="total" id="invoiceTotalInput" value="0">
    <input type="hidden" value="{{ $sales_tax }}" data-sales-tax-rate>

    <!-- Form Header -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Create Invoice</h2>
            <p class="text-sm text-gray-600">Invoice <span id="invoiceNumberDisplay">#{{ $invoiceNumber }}</span> for Task: {{ $task->title }}</p>
        </div>
    </div>

    <div class="px-6 pb-6 max-h-[calc(100vh-200px)] overflow-y-auto">
        <div class="space-y-6">
            <!-- Customer Information (from Service Call) -->
            <div class="bg-white rounded-lg shadow p-6" x-data="invoiceCustomerInfo()">
                <h3 class="flex items-center gap-2 text-lg font-semibold mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building w-5 h-5 mr-2">
                        <rect width="16" height="20" x="4" y="2" rx="2" ry="2"></rect>
                        <path d="M9 22v-4h6v4"></path>
                        <path d="M8 6h.01"></path>
                        <path d="M16 6h.01"></path>
                        <path d="M12 6h.01"></path>
                        <path d="M12 10h.01"></path>
                        <path d="M12 14h.01"></path>
                        <path d="M16 10h.01"></path>
                        <path d="M16 14h.01"></path>
                        <path d="M8 10h.01"></path>
                        <path d="M8 14h.01"></path>
                    </svg>
                    Bill To
                </h3>
                
                <!-- Hidden input for customer email (will be used by backend to match/create customer) -->
                <input type="hidden" name="customer_email" id="customer_email" x-model="customerEmail">
                <input type="hidden" name="customer_data" id="customer_data" x-model="customerDataJson">
                
                <!-- Loading State -->
                <div x-show="loading" class="flex items-center justify-center py-4">
                    <i class="fas fa-spinner fa-spin text-blue-600 mr-2"></i>
                    <span class="text-sm text-gray-600">Loading customer...</span>
                </div>
                
                <!-- Customer Info Display -->
                <div x-show="!loading && customerData" x-cloak>
                    <p class="font-semibold" x-text="customerData?.company || 'N/A'"></p>
                    <p class="text-gray-600" x-text="(customerData?.firstName || '') + ' ' + (customerData?.lastName || '')"></p>

                    <div class="flex items-center text-sm text-gray-600 gap-2 mt-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-900">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        <span x-text="customerData?.email || 'N/A'"></span>
                    </div>

                    <div class="flex items-center text-sm text-gray-600 gap-2 mt-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-900">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                        </svg>
                        <span x-text="customerData?.phone || 'N/A'"></span>
                    </div>

                    <!-- Billing Address -->
                    <template x-if="customerData?.billingAddress && customerData?.billingAddress !== 'N/A'">
                        <div class="flex items-start text-sm text-gray-600 gap-2 mt-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-900 mt-0.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <span>
                                <span x-text="customerData?.billingAddress"></span>
                                <span class="text-gray-500"> - Billing Address</span>
                            </span>
                        </div>
                    </template>

                    <!-- Shipping Address -->
                    <template x-if="customerData?.shippingAddress && customerData?.shippingAddress !== 'N/A'">
                        <div class="flex items-start text-sm text-gray-600 gap-2 mt-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-900 mt-0.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                            <span>
                                <span x-text="customerData?.shippingAddress"></span>
                                <span class="text-gray-500"> - Delivery Address</span>
                            </span>
                        </div>
                    </template>

                    
                </div>
                
                <!-- Error State -->
                <div x-show="!loading && error" x-cloak class="text-center py-4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-red-600 mx-auto mb-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <p class="text-sm text-red-600" x-text="error"></p>
                </div>
            </div>

            <!-- Invoice Details -->
            <div class="bg-white rounded-lg border border-gray-200 p-5">
                <h3 class="flex items-center gap-2 text-lg font-semibold mb-4">
                    <i class="fas fa-file-invoice text-blue-600"></i>
                    Invoice Details
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Number</label>
                        <input type="text" name="invoice_number" id="invoiceNumberInput" readonly 
                               value="{{ $invoiceNumber }}" required
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm bg-gray-50">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Date</label>
                        <input type="date" name="invoice_date" id="invoice_date" required
                               value="{{ date('Y-m-d') }}"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Date Period</label>
                        <select id="paymentTerms" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="0" selected>Pay Upon Receipt</option>
                            <option value="10">10 Days</option>
                            <option value="20">20 Days</option>
                            <option value="30">30 Days</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                        <input type="date" name="due_date" id="due_date" required
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    </div>
                </div>
            </div>

            <!-- Add Items -->
            <div class="bg-white rounded-lg border border-gray-200 p-5">
                <h3 class="text-lg font-semibold mb-4">Add Items to Invoice</h3>
                <div class="grid grid-cols-3 gap-3 mb-5">
                    <button type="button" id="openChargeModal" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-md text-sm font-medium flex items-center justify-center gap-2">
                        <i class="fas fa-plus"></i>
                        Charge
                    </button>
                    <button type="button" id="openDiscountModal" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-md text-sm font-medium flex items-center justify-center gap-2">
                        <i class="fas fa-tag"></i>
                        Discount
                    </button>
                    <button type="button" id="openRefundModal" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium flex items-center justify-center gap-2">
                        <i class="fas fa-undo"></i>
                        Refund
                    </button>
                </div>

                <!-- EMPTY STATE -->
                <div id="emptyState" class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-lg p-8 text-center text-gray-500">
                    <i class="fas fa-inbox text-5xl text-gray-300 mb-4"></i>
                    <p class="text-sm">No items added to this invoice yet.</p>
                    <p class="text-sm text-gray-400 mt-1">Use the buttons above to add charges, discounts, or refunds.</p>
                </div>

                <!-- TABLE STATE -->
                <div id="itemsTable" class="overflow-x-auto hidden rounded-lg border border-gray-200">
                    <table class="min-w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Description</th>
                                <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700">Qty</th>
                                <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Unit Price</th>
                                <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Tax</th>
                                <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Total</th>
                                <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="invoiceItems" class="divide-y divide-gray-200">
                            <!-- rows will be added dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Invoice Notes -->
            <div class="bg-white rounded-lg border border-gray-200 p-5">
                <h3 class="text-lg font-semibold mb-4">Invoice Notes</h3>
                <textarea rows="4" name="invoice_notes" 
                          class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500" 
                          placeholder="Enter any additional notes for this invoice..."></textarea>
            </div>

            <!-- Invoice Summary -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold mb-4">Invoice Summary</h3>
                <div class="flex justify-between py-1 text-sm">
                    <span class="text-gray-700">Subtotal:</span>
                    <span class="font-medium text-gray-900" id="invoice-subtotal">$0.00</span>
                </div>
                <div class="flex justify-between py-1 text-sm">
                    <span class="text-gray-700">Sales Tax:</span>
                    <span class="text-green-600 font-medium" id="invoice-tax">$0.00</span>
                </div>
                <hr class="my-2 border-gray-300">
                <div class="flex justify-between pt-2 font-bold text-lg">
                    <span>Total:</span>
                    <span class="text-gray-900" id="invoice-total">$0.00</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Footer -->
    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
        <!-- <button type="reset" 
                class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 text-sm font-medium">
            Reset Form
        </button> -->
        <button type="submit" id="submitInvoiceBtn" disabled 
                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-sm font-medium flex items-center gap-2">
            <i class="fas fa-save"></i>
            <span>Create Invoice</span>
        </button>
    </div>
</form>

<!-- Include Partials -->
@include('tasks.invoices.partials._invoice_charge')
@include('tasks.invoices.partials._invoice_discount')
@include('tasks.invoices.partials._invoice_refund')

@push('scripts')
<script>
// Initialize sales tax rate
window.SALES_TAX_RATE = parseFloat(document.querySelector('[data-sales-tax-rate]').value) || 0;

// Global invoice data array
let invoice_data = [];

// Helper functions
function addInvoiceProduct(products, type) {
    products.forEach(p => {
        invoice_data.push({ ...p, type: type });
    });
}

function generateUniqueId(prefix = "item") {
    return prefix + "_" + Date.now() + "_" + Math.floor(Math.random() * 100000);
}

function parseCurrency(value) {
    return Number(value.replace(/[^0-9.-]+/g, "")) || 0;
}

const invoiceItemsTBody = document.getElementById("invoiceItems");

// Update invoice summary
function updateInvoiceSummary() {
    const subtotalElem = document.querySelector('#invoice-subtotal');
    const totalElem = document.querySelector('#invoice-total');
    const taxElem = document.querySelector('#invoice-tax');
    const submitBtn = document.querySelector('#submitInvoiceBtn');
    
    const subtotalInput = document.getElementById("invoiceSubtotalInput");
    const taxInput = document.getElementById("invoiceTaxInput");
    const totalInput = document.getElementById("invoiceTotalInput");
    const invoiceDataInput = document.getElementById("invoiceDataInput");
    
    let subtotal = 0;
    let totalTax = 0;
    
    const rows = invoiceItemsTBody.querySelectorAll("tr");
    
    rows.forEach(row => {
        const type = row.dataset.type;
        const priceCell = row.querySelector("td:nth-child(3)");
        const taxCell = row.querySelector("td:nth-child(4)");
        
        let price = parseCurrency(priceCell?.textContent || '0');
        let tax = parseCurrency(taxCell?.textContent || '0');
        
        if (type === "charge") {
            subtotal += price;
            totalTax += tax;
        } else if (type === "discount" || type === "refund") {
            subtotal -= price;
            totalTax -= tax;
        }
    });
    
    subtotal = Math.max(0, subtotal);
    totalTax = subtotal == 0 ? 0 : Math.max(0, totalTax);
    let finalTotal = Math.max(0, subtotal + totalTax);
    
    if (subtotalElem) subtotalElem.textContent = `$${subtotal.toFixed(2)}`;
    if (taxElem) taxElem.textContent = totalTax > 0 ? `$${totalTax.toFixed(2)}` : 'Tax Exempt';
    if (totalElem) totalElem.textContent = `$${finalTotal.toFixed(2)}`;
    
    if (submitBtn) submitBtn.disabled = rows.length === 0;
    
    if (subtotalInput) subtotalInput.value = subtotal.toFixed(2);
    if (taxInput) taxInput.value = totalTax.toFixed(2);
    if (totalInput) totalInput.value = finalTotal.toFixed(2);
    if (invoiceDataInput) invoiceDataInput.value = JSON.stringify(invoice_data);
}

window.updateInvoiceSummary = updateInvoiceSummary;

// Alpine.js component for customer info from service call
function invoiceCustomerInfo() {
    return {
        loading: false,
        customerData: null,
        customerEmail: '',
        customerDataJson: '',
        error: null,
        
        async init() {
            const orderId = '{{ $orderId }}';
            if (!orderId) {
                this.error = 'No service call order found for this task.';
                return;
            }
            
            this.loading = true;
            this.error = null;
            
            try {
                const response = await fetch(`/api/orders/search?q=${encodeURIComponent(orderId)}`);
                if (!response.ok) {
                    throw new Error('Failed to fetch order details');
                }
                
                const data = await response.json();
                
                if (data.orders && data.orders.length > 0) {
                    const order = data.orders.find(o => o.orderNumber === orderId) || data.orders[0];
                    // Combine customer data with addresses from order
                    this.customerData = {
                        ...order.customer,
                        billingAddress: order.billingAddress,
                        shippingAddress: order.shippingAddress
                    };
                    this.customerEmail = order.customer?.email || '';
                    this.customerDataJson = JSON.stringify(this.customerData);
                } else {
                    this.error = 'Customer not found in order';
                }
            } catch (err) {
                console.error('Error loading customer:', err);
                this.error = 'Failed to load customer details.';
            } finally {
                this.loading = false;
            }
        }
    };
}

window.invoiceCustomerInfo = invoiceCustomerInfo;

// Due date calculation
document.addEventListener('DOMContentLoaded', () => {
    const invoiceDateInput = document.getElementById('invoice_date');
    const dueDateInput = document.getElementById('due_date');
    const paymentTermsSelect = document.getElementById('paymentTerms');
    
    function calculateDueDate() {
        const invoiceDateValue = invoiceDateInput.value;
        if (!invoiceDateValue) return;
        
        const invoiceDate = new Date(invoiceDateValue);
        if (isNaN(invoiceDate)) return;
        
        const termDays = parseInt(paymentTermsSelect.value);
        
        if (!termDays || termDays === 0) {
            if (!dueDateInput.dataset.existing) dueDateInput.value = '';
        } else {
            invoiceDate.setDate(invoiceDate.getDate() + termDays);
            dueDateInput.value = invoiceDate.toISOString().split('T')[0];
        }
    }
    
    if (!dueDateInput.value) calculateDueDate();
    paymentTermsSelect.addEventListener('change', calculateDueDate);
    invoiceDateInput.addEventListener('change', calculateDueDate);
    
    // Delete row handler
    invoiceItemsTBody.addEventListener("click", (e) => {
        const btn = e.target.closest(".delete-btn");
        if (!btn) return;
        
        const row = btn.closest("tr");
        const itemId = row.dataset.id;
        row.remove();
        
        const index = invoice_data.findIndex(p => p.id === itemId);
        if (index !== -1) invoice_data.splice(index, 1);
        
        const itemsTable = document.getElementById("itemsTable");
        const emptyState = document.getElementById("emptyState");
        
        if (!invoiceItemsTBody.querySelector("tr")) {
            itemsTable.classList.add("hidden");
            if (emptyState) emptyState.classList.remove("hidden");
        }
        
        updateInvoiceSummary();
    });

    // Handle form submission
    const invoiceForm = document.getElementById('invoiceForm');
    if (invoiceForm) {
        invoiceForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitInvoiceBtn');
            const originalBtnContent = submitBtn.innerHTML;
            
            // Disable submit button and show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><span>Creating Invoice...</span>';
            
            try {
                const formData = new FormData(invoiceForm);
                
                const response = await fetch(invoiceForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Show success message
                    submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i><span>Invoice Created!</span>';
                    submitBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                    submitBtn.classList.add('bg-green-600');
                    
                    // Reload the page after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    throw new Error(data.message || 'Failed to create invoice');
                }
            } catch (error) {
                console.error('Error creating invoice:', error);
                
                // Show error message
                alert('Failed to create invoice: ' + error.message);
                
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnContent;
            }
        });
    }
});
</script>
@endpush
