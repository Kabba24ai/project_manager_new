<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'service_type',
        'order_id',
        'notes',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
