<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'invoice_type',
        'type',
        'item_name',
        'item_id',
        'qty',
        'sku',
        'unit',
        'tax',
        'total',
        'extras',
        'notes',
        'reference',
        'responsible_person_id',
    ];

    protected $casts = [
        'extras' => 'array',
        'invoice_type' => 'integer',
        'qty' => 'integer',
        'unit' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Boot method to set default invoice_type for project_manager
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (is_null($model->invoice_type)) {
                $model->invoice_type = 1; // Project Manager invoices
            }
        });
    }

    /**
     * Get the invoice that owns the item
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the responsible person
     */
    public function responsiblePerson()
    {
        return $this->belongsTo(User::class, 'responsible_person_id');
    }

    /**
     * Scope to get only project_manager invoice items
     */
    public function scopeProjectManager($query)
    {
        return $query->where('invoice_type', 1);
    }

    /**
     * Scope to get only kaaba2 invoice items
     */
    public function scopeKaaba2($query)
    {
        return $query->where('invoice_type', 0);
    }
}
