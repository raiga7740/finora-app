<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'amount',
        'billing_cycle',
        'due_day',
        'start_date',
        'status',
        'payment_method',
        'notes',
        'last_paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_day' => 'integer',
        'start_date' => 'date',
        'last_paid_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope active bills.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Check if the bill has been paid for the current monthly cycle.
     */
    public function isPaidForCurrentCycle(): bool
    {
        if (!$this->last_paid_at) {
            return false;
        }

        $now = Carbon::now();

        if ($this->billing_cycle === 'monthly') {
            return $this->last_paid_at->year === $now->year && $this->last_paid_at->month === $now->month;
        } elseif ($this->billing_cycle === 'weekly') {
            return $this->last_paid_at->diffInDays($now) < 7;
        } elseif ($this->billing_cycle === 'yearly') {
            return $this->last_paid_at->year === $now->year;
        }

        return false;
    }

    /**
     * Calculate the next due date based on billing cycle and due_day.
     */
    public function getNextDueDateAttribute(): Carbon
    {
        $now = Carbon::now();
        $dueDay = min(max(1, $this->due_day ?? 1), 28); // Cap at 28 to safely cover all months including Feb

        if ($this->billing_cycle === 'monthly') {
            $dueDateThisMonth = Carbon::create($now->year, $now->month, $dueDay)->startOfDay();

            if ($this->isPaidForCurrentCycle()) {
                // If already paid this month, next due date is next month
                return $dueDateThisMonth->copy()->addMonth();
            }

            // If not paid, if today is past the due date, it's overdue (still due this month)
            return $dueDateThisMonth;
        } elseif ($this->billing_cycle === 'yearly') {
            $dueDateThisYear = Carbon::create($now->year, $this->start_date ? $this->start_date->month : 1, $dueDay)->startOfDay();
            if ($this->isPaidForCurrentCycle() || $dueDateThisYear->isPast()) {
                return $dueDateThisYear->copy()->addYear();
            }
            return $dueDateThisYear;
        }

        // Default weekly:
        return $now->copy()->addDays(max(1, 7 - ($now->dayOfWeek ?: 7)));
    }

    /**
     * Number of days until due date (negative means overdue).
     */
    public function getDaysUntilDueAttribute(): int
    {
        $now = Carbon::now()->startOfDay();
        $dueDate = $this->next_due_date->startOfDay();

        return (int) $now->diffInDays($dueDate, false);
    }

    /**
     * Check if bill is overdue for the current period.
     */
    public function isOverdue(): bool
    {
        if ($this->isPaidForCurrentCycle()) {
            return false;
        }

        return $this->days_until_due < 0;
    }
}

