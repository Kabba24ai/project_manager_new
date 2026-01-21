<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Invoice;
use App\Models\Task;

class InvoiceEmailEvent
{
    use Dispatchable, SerializesModels;

    public $invoice;
    public $task;

    public function __construct(Invoice $invoice, Task $task)
    {
        $this->invoice = $invoice;
        $this->task = $task;
    }
}
