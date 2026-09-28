<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    /** What the payment settles: a direct purchase or part of an invoice. */
    public const KIND_DIRECT = 0;
    public const KIND_DEPOSIT = 1;
    public const KIND_BALANCE = 2;
    public const KIND_FULL = 3;

    public const KIND_LABELS = [
        self::KIND_DIRECT => 'Direct payment',
        self::KIND_DEPOSIT => 'Deposit (50%)',
        self::KIND_BALANCE => 'Balance payment',
        self::KIND_FULL => 'Full settlement',
    ];

    protected $fillable = [
        'paymentable_type',
        'paymentable_id',
        'enquiry_id',
        'invoice_id',
        'kind',
        'order_tracking_id',
        'merchant_reference',
        'amount',
        'currency',
        'description',
        'status',
        'pesapal_status',
        'payment_method',
        'paid_at',
        'settled_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'kind' => 'integer',
        'paid_at' => 'datetime',
        'settled_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Polymorphic: links to ServicePackage, CarePlan, or Scan.
     */
    public function paymentable(): BelongsTo
    {
        return $this->belongsTo($this->paymentable_type, $this->paymentable_id);
    }

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getKindLabelAttribute(): string
    {
        return self::KIND_LABELS[$this->kind] ?? 'Payment';
    }

    /**
     * Scope: pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
