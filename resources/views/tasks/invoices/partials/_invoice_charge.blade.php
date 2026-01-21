<div id="chargeModalWrapper" style="display: none;" class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 px-4 py-10">
    <div class="modal-scrollable w-full mx-auto">
        <div class="bg-white rounded-lg shadow-xl w-full mx-auto max-w-lg space-y-5 border border-gray-200 overflow-hidden flex flex-col max-h-full">
            <div class="flex justify-between items-center px-6 pt-4">
                <div class="flex items-center gap-2">
                    <div class="text-red-600 rounded-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-plus w-5 h-5">
                            <path d="M5 12h14" />
                            <path d="M12 5v14" />
                        </svg>
                    </div>
                    <h2 class="text-lg font-medium text-gray-900">New Charge</h2>
                </div>
                <button id="closeChargeModalBtn" class="text-gray-400 hover:text-gray-700 dark:hover:text-white text-xl">&times;</button>
            </div>

            <div class="px-6 overflow-y-auto">
                <form class="space-y-8" id="chargeForm">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Charge Amount</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 h-[35px] pl-3 flex items-center text-gray-500">$</span>
                            <input id="amount" type="text" name="amount" placeholder="0.00" maxlength="8" required
                                   class="pl-7 pr-3 py-2 w-full border border-gray-300 rounded-md text-sm">
                        </div>

                        <div id="clueBox" class="hidden mt-3 bg-blue-50 border border-blue-200 rounded-md p-4 text-sm space-y-1">
                            <p class="font-semibold text-gray-700">Calculation Preview</p>
                            <div class="flex justify-between">
                                <span class="text-blue-700">Amount:</span>
                                <span id="amountPreview" class="text-blue-900">$0.00</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-700">Sales Tax:</span>
                                <span id="taxPreview" class="text-blue-900">$0.00</span>
                            </div>
                            <div class="flex justify-between font-semibold border-t border-blue-300 pt-1">
                                <span class="text-blue-700">Total Balance Change:</span>
                                <span class="text-blue-900" id="totalPreview">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Charge Reason</label>
                        <select name="reason" id="reason" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select charge reason</option>
                            <option value="New Rental">New Rental</option>
                            <option value="Rental Extension">Rental Extension</option>
                            <option value="Damages">Damages</option>
                            <option value="Fuel Charge">Fuel Charge</option>
                            <option value="Cleaning Charge">Cleaning Charge</option>
                            <option value="Missing Items">Missing Items</option>
                            <option value="Product Purchase">Product Purchase</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1 required">Person Responsible</label>
                        <select name="responsible_person" id="responsible_person" required class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700">
                            <option value="">Select person responsible</option>
                            @foreach($teamMembers as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reference (Optional)</label>
                        <input id="charge_reference" class="px-3 py-2 w-full border border-gray-300 rounded-md text-sm" type="text" name="reference" placeholder="Enter reference number or ID...">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700" name="notes" id="notes" rows="3" placeholder="Describe the reason for this charge..."></textarea>
                    </div>

                    <div class="flex gap-2 pb-4">
                        <button type="button" id="cancelChargeBtn" class="px-4 py-2 flex-1 text-sm rounded border border-gray-300 bg-white text-gray-700">
                            Cancel
                        </button>
                        <button type="submit" id="submitChargeBtn" class="relative flex-1 px-4 py-2 text-sm rounded bg-blue-600 text-white flex items-center justify-center gap-2">
                            <span id="chargeBtnText">Add Charge</span>
                            <svg id="chargeBtnSpinner" xmlns="http://www.w3.org/2000/svg" class="hidden animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const modalWrapper = document.getElementById('chargeModalWrapper');
    const openBtn = document.getElementById('openChargeModal');
    const closeBtn = document.getElementById('closeChargeModalBtn');
    const cancelBtn = document.getElementById('cancelChargeBtn');
    const form = document.getElementById('chargeForm');
    
    const emptyState = document.getElementById("emptyState");
    const itemsTable = document.getElementById("itemsTable");
    const invoiceItems = document.getElementById("invoiceItems");
    
    const amountInput = document.getElementById("amount");
    const clueBox = document.getElementById("clueBox");
    const amountPreview = document.getElementById("amountPreview");
    const taxPreview = document.getElementById("taxPreview");
    const totalPreview = document.getElementById("totalPreview");
    
    openBtn.addEventListener('click', () => {
        modalWrapper.style.display = 'flex';
        delete form.dataset.editingId;
        document.querySelector("#chargeModalWrapper h2").textContent = "New Charge";
        document.getElementById("chargeBtnText").textContent = "Add Charge";
        form.reset();
        amountPreview.textContent = "$0.00";
        taxPreview.textContent = "$0.00";
        totalPreview.textContent = "$0.00";
        clueBox.classList.add("hidden");
    });
    
    const closeModal = () => {
        modalWrapper.style.display = 'none';
        form.reset();
        if (amountInput.digits) amountInput.digits = "";
        amountInput.value = "";
        amountPreview.textContent = "$0.00";
        taxPreview.textContent = "$0.00";
        totalPreview.textContent = "$0.00";
        clueBox.classList.add("hidden");
    };
    
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    
    amountInput.addEventListener("input", function() {
        this.value = this.value.replace(/[^0-9.]/g, "");
        const amount = parseFloat(this.value) || 0;
        const taxRate = window.SALES_TAX_RATE;
        const tax = amount * taxRate;
        const total = amount + tax;
        amountPreview.textContent = `$${amount.toFixed(2)}`;
        taxPreview.textContent = `$${tax.toFixed(2)}`;
        totalPreview.textContent = `$${total.toFixed(2)}`;
        clueBox.classList.toggle("hidden", this.value.trim() === "");
    });
    
    invoiceItems.addEventListener("click", (e) => {
        const btn = e.target.closest(".edit-btn");
        if (!btn) return;
        
        const row = btn.closest("tr");
        const itemId = row.dataset.id;
        const item = invoice_data.find(p => p.id === itemId);
        if (!item || item.type !== 'charge') return;
        
        form.dataset.editingId = itemId;
        form.elements["amount"].value = parseFloat(item.unit).toFixed(2);
        form.elements["amount"].dispatchEvent(new Event('input', { bubbles: true }));
        form.elements["reason"].value = item.name;
        form.elements["responsible_person"].value = item.responsible_id;
        form.elements["reference"].value = item.reference;
        form.elements["notes"].value = item.notes || "";
        
        amountPreview.textContent = `$${item.unit}`;
        taxPreview.textContent = `$${item.tax}`;
        totalPreview.textContent = `$${item.total}`;
        clueBox.classList.remove("hidden");
        
        document.querySelector("#chargeModalWrapper h2").textContent = "Edit Charge";
        document.getElementById("chargeBtnText").textContent = "Update Charge";
        modalWrapper.style.display = "flex";
    });
    
    form.addEventListener("submit", e => {
        e.preventDefault();
        
        const fd = new FormData(form);
        const desc = fd.get("reason") || "New Charge";
        const notes = fd.get("notes")?.trim() || "";
        const price = parseFloat(amountInput.value) || 0;
        const qty = 1;
        const taxRate = window.SALES_TAX_RATE;
        const taxPrice = price * taxRate;
        const total = price + taxPrice;
        const responsibleId = fd.get("responsible_person") || "-";
        const reference = fd.get("reference") || "-";
        
        if (price <= 0) {
            alert("Enter valid amount");
            return;
        }
        
        const editingId = form.dataset.editingId;
        
        if (editingId) {
            const item = invoice_data.find(p => p.id === editingId);
            if (item) {
                Object.assign(item, {
                    name: desc, qty, unit: price, tax: taxPrice, total,
                    responsible_id: responsibleId, reference, notes
                });
            }
            
            const row = invoiceItems.querySelector(`tr[data-id="${editingId}"]`);
            if (row) {
                row.innerHTML = `
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="font-medium text-gray-900">${desc} <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-600 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus w-3 h-3"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                            <span class="ml-1 capitalize text-xs">charge</span>
                        </span></div>
                        ${notes ? `<div class="text-gray-500 text-sm">${notes}</div>` : ""}
                    </td>
                    <td class="px-4 py-3 text-center text-sm whitespace-nowrap">${qty}</td>
                    <td class="px-4 py-3 text-right text-sm whitespace-nowrap">$${price.toFixed(2)}</td>
                    <td class="px-4 py-3 text-right text-sm whitespace-nowrap">$${taxPrice.toFixed(2)}</td>
                    <td class="px-4 py-3 text-right font-semibold text-sm whitespace-nowrap">$${total.toFixed(2)}</td>
                    <td class="px-4 py-3 text-center h-full items-center justify-center gap-3 whitespace-nowrap">
                        <button type="button" class="text-blue-600 edit-btn mr-2"><i class="fas fa-edit"></i></button>
                        <button type="button" class="text-red-600 delete-btn"><i class="fas fa-trash"></i></button>
                    </td>
                `;
            }
        } else {
            const uniqueId = generateUniqueId("charge");
            addInvoiceProduct([{
                id: uniqueId, name: desc, qty, unit: price, tax: taxPrice, total,
                responsible_id: responsibleId, reference, notes
            }], 'charge');
            
            const row = document.createElement("tr");
            row.dataset.type = "charge";
            row.dataset.id = uniqueId;
            row.innerHTML = `
                <td class="px-4 py-3 whitespace-nowrap">
                    <div class="font-medium text-gray-900">${desc} <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-600 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-plus w-3 h-3"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>
                        <span class="ml-1 capitalize text-xs">charge</span>
                    </span></div>
                    ${notes ? `<div class="text-gray-500 text-sm">${notes}</div>` : ""}
                </td>
                <td class="px-4 py-3 text-center text-sm whitespace-nowrap">${qty}</td>
                <td class="px-4 py-3 text-right text-sm whitespace-nowrap">$${price.toFixed(2)}</td>
                <td class="px-4 py-3 text-right text-sm whitespace-nowrap">$${taxPrice.toFixed(2)}</td>
                <td class="px-4 py-3 text-right font-semibold text-sm whitespace-nowrap">$${total.toFixed(2)}</td>
                <td class="px-4 py-3 text-center h-full items-center justify-center gap-3 whitespace-nowrap">
                    <button type="button" class="text-blue-600 edit-btn mr-2"><i class="fas fa-edit"></i></button>
                    <button type="button" class="text-red-600 delete-btn"><i class="fas fa-trash"></i></button>
                </td>
            `;
            
            invoiceItems.appendChild(row);
            emptyState.classList.add("hidden");
            itemsTable.classList.remove("hidden");
        }
        
        closeModal();
        window.updateInvoiceSummary();
    });
});
</script>
@endpush
