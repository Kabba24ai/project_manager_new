<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'company_name',
        'email',
        'phone',
        'company_phone',
        'status'
    ];

    /**
     * Get the full name of the customer
     */
    public function getFullNameAttribute()
    {
        if ($this->company_name) {
            return $this->company_name;
        }
        return trim("{$this->first_name} {$this->last_name}");
    }

    /**
     * Get the display name (prioritize company name, then full name)
     */
    public function getNameAttribute()
    {
        return $this->full_name;
    }
}
