<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'unique_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'customer_id',
        'task_id',
        'invoice_created_by',
        'subtotal',
        'sales_tax',
        'total',
        'invoice_notes',
        'invoice_status',
        'is_email_send',
        'payment_method',
        'mail_send_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'mail_send_at' => 'datetime',
        'is_email_send' => 'boolean',
        'subtotal' => 'decimal:2',
        'sales_tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Boot method to generate unique ID on creation
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->unique_id)) {
                $model->unique_id = 'INV-' . strtoupper(uniqid());
            }
        });
    }

    /**
     * Get the customer that owns the invoice
     */
    public function customer()
    {
        // Using Kaaba2's Customer model via full namespace
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }

    /**
     * Get the invoice items (only for project_manager - invoice_type = 1)
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->where('invoice_type', 1);
    }

    /**
     * Get all invoice items (without filter)
     */
    public function allItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the user who created the invoice
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'invoice_created_by');
    }

    /**
     * Get the task associated with this invoice
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Scope to get only project_manager invoices
     */
    public function scopeProjectManager($query)
    {
        return $query->whereHas('items', function ($q) {
            $q->where('invoice_type', 1);
        });
    }
}
