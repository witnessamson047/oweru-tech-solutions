<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    /** Statuses. */
    public const STATUS_DRAFT = 0;
    public const STATUS_ISSUED = 1;
    public const STATUS_PART_PAID = 2;
    public const STATUS_PAID = 3;
    public const STATUS_CANCELLED = 4;

    public const STATUS_LABELS = [
        self::STATUS_DRAFT => 'draft',
        self::STATUS_ISSUED => 'issued',
        self::STATUS_PART_PAID => 'part_paid',
        self::STATUS_PAID => 'paid',
        self::STATUS_CANCELLED => 'cancelled',
    ];

    protected $fillable = [
        'number', 'enquiry_id', 'title', 'description', 'total', 'currency',
        'deposit_percent', 'deposit_due', 'status', 'issued_at', 'due_at',
        'receipt_generated_at', 'receipt_path', 'meta',
        'reminders_sent', 'last_reminder_sent_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'deposit_due' => 'decimal:2',
        'deposit_percent' => 'integer',
        'status' => 'integer',
        'issued_at' => 'datetime',
        'due_at' => 'datetime',
        'receipt_generated_at' => 'datetime',
        'last_reminder_sent_at' => 'datetime',
        'reminders_sent' => 'integer',
        'meta' => 'array',
    ];

    public function enquiry(): BelongsTo
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('paid_at')->orderBy('id');
    }

    public function completedPayments(): HasMany
    {
        return $this->payments()->where('status', 'completed');
    }

    // -----------------------------------------------------------------
    // Accessors / helpers
    // -----------------------------------------------------------------

    public function getAmountPaidAttribute(): float
    {
        return (float) $this->completedPayments()->sum('amount');
    }

    public function getBalanceDueAttribute(): float
    {
        return max(0, (float) $this->total - $this->amount_paid);
    }

    public function getDepositAmountAttribute(): float
    {
        return (float) $this->deposit_due;
    }

    public function getBalanceAmountAttribute(): float
    {
        return max(0, (float) $this->total - (float) $this->deposit_due);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? 'unknown';
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return $this->amount_paid >= (float) $this->total && (float) $this->total > 0;
    }

    public function getIsDepositPaidAttribute(): bool
    {
        return $this->amount_paid >= (float) $this->deposit_due;
    }

    /**
     * What the customer still has to pay next: deposit, then balance, then nothing.
     */
    public function getNextPaymentAmountAttribute(): ?float
    {
        if ($this->is_fully_paid || (float) $this->total <= 0) {
            return null;
        }

        if (! $this->is_deposit_paid) {
            return round((float) $this->deposit_due - $this->amount_paid, 2);
        }

        return $this->balance_due;
    }

    public function getNextPaymentKind(): int
    {
        if ($this->is_fully_paid) {
            return Payment::KIND_FULL;
        }

        return $this->is_deposit_paid ? Payment::KIND_BALANCE : Payment::KIND_DEPOSIT;
    }

    public function scopeNotCancelled($query)
    {
        return $query->where('status', '!=', self::STATUS_CANCELLED);
    }

    /**
     * Stable secret token that guards the public receipt download link.
     * Deterministic per invoice (HMAC of the invoice number + app key).
     */
    public function getReceiptTokenAttribute(): string
    {
        return hash_hmac('sha256', 'receipt:' . $this->number, (string) config('app.key'));
    }

    // -----------------------------------------------------------------
    // Overdue deposit helpers
    // -----------------------------------------------------------------

    /**
     * The deposit is overdue when its due date has passed and the deposit
     * hasn't been paid. (Balance payments have no separate due date.)
 */
    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return false;
        }

        if ($this->is_deposit_paid) {
            return false;
        }

        return $this->due_at !== null && $this->due_at->endOfDay()->isPast();
    }

    public function getDaysOverdueAttribute(): int
    {
        if (! $this->is_overdue || $this->due_at === null) {
            return 0;
        }

        // Whole calendar days past the due date (due date itself = day 0 →
        // due yesterday = 1 day overdue).
        return (int) $this->due_at->copy()->startOfDay()->diffInDays(now()->startOfDay());
    }
}
