<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'filename',
        'original_filename',
        'path',
        'thumbnail_path',
        'mime_type',
        'size',
        'uploaded_by',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isImage(): bool
    {
        return in_array($this->mime_type, [
            'image/jpeg', 
            'image/jpg', 
            'image/png', 
            'image/gif', 
            'image/webp'
        ]);
    }

    public function isVideo(): bool
    {
        return in_array($this->mime_type, [
            'video/mp4', 
            'video/quicktime', 
            'video/x-msvideo', 
            'video/webm'
        ]);
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }
}
