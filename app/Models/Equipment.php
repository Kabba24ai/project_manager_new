<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;
    
    protected $table = 'equipment';
    
    protected $fillable = [
        'equipment_name', 
        'product_category_id', 
        'equipment_id',
        'brand',
        'model',
        'current_status',
        'equipment_notes'
    ];

    protected $casts = [
        'not_for_rent' => 'boolean',
    ];

    /**
     * Get the category that owns the equipment
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }
    
    /**
     * Check if equipment is available
     */
    public function getAvailableAttribute()
    {
        return $this->current_status === 'available';
    }
}
