<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Debt extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'person_name',
        'person_contact',
        'title',
        'total_amount',
        'paid_amount',
        'currency',
        'start_date',
        'due_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'start_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class)->orderByDesc('payment_date')->orderByDesc('id');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0.0, (float) $this->total_amount - (float) $this->paid_amount);
    }

    public function getProgressPercentageAttribute(): float
    {
        if ((float) $this->total_amount <= 0) {
            return 100.0;
        }

        return min(100.0, round(((float) $this->paid_amount / (float) $this->total_amount) * 100, 1));
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status !== 'active' || ! $this->due_date) {
            return false;
        }

        return $this->due_date->isPast() && ! $this->due_date->isToday();
    }

    public function getWhatsappReminderUrlAttribute(): ?string
    {
        if (! $this->person_contact) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $this->person_contact);
        if (! $digits) {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }

        $symbol = User::currencySymbol($this->currency);
        $formattedRemaining = $symbol . number_format($this->remaining_amount, in_array($this->currency, ['IDR', 'JPY']) ? 0 : 2, ',', '.');
        $dueText = $this->due_date ? ' (Jatuh tempo: ' . $this->due_date->format('d M Y') . ')' : '';
        $notesText = $this->notes ? "\nDetail: {$this->notes}." : '';

        if ($this->type === 'receivable') {
            $message = "Halo {$this->person_name}, sekadar mengingatkan mengenai tagihan \"{$this->title}\" dengan nominal sebesar {$formattedRemaining}{$dueText}.{$notesText} Terima kasih banyak!";
        } else {
            $message = "Halo {$this->person_name}, berikut konfirmasi catatan pinjaman saya \"{$this->title}\" dengan sisa pembayaran sebesar {$formattedRemaining}{$dueText}.{$notesText} Terima kasih!";
        }

        return 'https://wa.me/' . $digits . '?text=' . rawurlencode($message);
    }
}

