<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Debt;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_financial_health_score_and_audit_radar(): void
    {
        $user = User::factory()->create(['locale' => 'en', 'currency' => 'IDR']);

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA Utama',
            'type' => 'bank',
            'currency' => 'IDR',
            'initial_balance' => 15000000,
            'is_primary' => true,
        ]);

        $catIncome = Category::create([
            'user_id' => $user->id,
            'name' => 'Salary',
            'type' => 'income',
        ]);

        $catExpense = Category::create([
            'user_id' => $user->id,
            'name' => 'Dining',
            'type' => 'expense',
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $catIncome->id,
            'title' => 'Monthly Salary',
            'amount' => 10000000,
            'base_amount' => 10000000,
            'currency' => 'IDR',
            'type' => 'income',
            'transaction_date' => now()->toDateString(),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $catExpense->id,
            'title' => 'Groceries & Food',
            'amount' => 3000000,
            'base_amount' => 3000000,
            'currency' => 'IDR',
            'type' => 'expense',
            'transaction_date' => now()->toDateString(),
        ]);

        Budget::create([
            'user_id' => $user->id,
            'category_id' => $catExpense->id,
            'name' => 'Food Budget',
            'amount' => 4000000,
            'period' => 'monthly',
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
        ]);

        Debt::create([
            'user_id' => $user->id,
            'type' => 'payable',
            'person_name' => 'Bank Cicilan',
            'title' => 'Cicilan Ringan',
            'total_amount' => 1000000,
            'paid_amount' => 500000,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('financial-health.index'));

        $response->assertOk();
        $response->assertSee('Financial Health Score & Audit Radar');
        $response->assertSee('5-AXIS FINANCIAL AUDIT RADAR');
        $response->assertSee('Savings Rate');
        $response->assertSee('Emergency Runway');
        $response->assertSee('Debt Immunity');
    }
}

