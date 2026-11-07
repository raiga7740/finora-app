<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsContribution extends Model
{
    protected $fillable = ['savings_goal_id','user_id','amount','notes','contributed_at'];
    protected $casts = ['amount' => 'decimal:2', 'contributed_at' => 'date'];
    public function savingsGoal(): BelongsTo { return $this->belongsTo(SavingsGoal::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
