<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'wallet_id',
        'destination_wallet_id',
        'category_id',
        'amount',
        'currency',
        'destination_amount',
        'exchange_rate',
        'base_amount',
        'type',
        'payment_method',
        'transaction_date',
        'title',
        'notes',
        'receipt_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'destination_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'base_amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    /**
     * Format transaction amount in its native currency.
     */
    public function formatAmount(bool $withSign = false): string
    {
        $curr = strtoupper($this->currency ?? $this->user?->currency ?? 'IDR');
        return (new User(['currency' => $curr]))->formatCurrency($this->amount, $withSign);
    }

    /**
     * User that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Source wallet of the transaction.
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    /**
     * Destination wallet for transfer transaction.
     */
    public function destinationWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'destination_wallet_id');
    }

    /**
     * Category of the transaction.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
