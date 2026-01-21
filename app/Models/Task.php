<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'task_list_id',
        'title',
        'description',
        'priority',
        'task_type',
        'task_status',
        'assigned_to',
        'created_by',
        'start_date',
        'due_date',
        'estimated_hours',
        'actual_hours',
        'tags',
        'feedback',
        'equipment_id',
        'customer_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'tags' => 'array',
        'estimated_hours' => 'integer',
        'actual_hours' => 'integer',
        'equipment_id' => 'integer',
        'customer_id' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function taskList(): BelongsTo
    {
        return $this->belongsTo(TaskList::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function serviceCall(): HasOne
    {
        return $this->hasOne(ServiceCall::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
