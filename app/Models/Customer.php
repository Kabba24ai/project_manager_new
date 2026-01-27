<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'unique_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company_name',
        'tax_status',
        'status',
    ];

    protected $appends = ['full_name'];

    /**
     * Get the customer's full name
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
        }

    /**
     * Get all addresses for the customer
     */
    public function addresses()
    {
        return $this->hasMany(\App\Models\CustomerAddress::class);
    }

    /**
     * Get invoices for this customer (from project_manager)
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
