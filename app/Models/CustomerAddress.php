<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    use HasFactory;

    protected $table = 'customer_addresses';

    protected $fillable = [
        'customer_id',
        'type',
        'full_name',
        'address',
        'city',
        'state_id',
        'zip_code',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Get the customer that owns the address
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the state
     */
    public function state()
    {
        return $this->belongsTo(\App\Models\State::class);
    }
}
