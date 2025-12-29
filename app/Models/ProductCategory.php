<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = ['title', 'slug', 'short_content', 'content'];

    /**
     * Get the equipment items for this category
     */
    public function equipment()
    {
        return $this->hasMany(Equipment::class, 'product_category_id');
    }
}
