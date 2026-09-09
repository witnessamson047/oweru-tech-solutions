<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'enquiry_id',
        'type', // 'new_enquiry', 'stage_reminder', 'custom'
        'recipient_id',
        'recipient_email',
        'channel', // 'email', 'whatsapp', 'both'
        'subject',
        'message',
        'status', // 'pending', 'sent', 'failed'
        'sent_at',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
