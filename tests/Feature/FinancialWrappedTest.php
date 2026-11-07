<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialWrappedTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_monthly_financial_wrapped(): void
    {
        $user = User::factory()->create(['locale' => 'en', 'currency' => 'IDR']);

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'Bank BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'initial_balance' => 5000000,
            'is_primary' => true,
        ]);

        $catIncome = Category::create([
            'user_id' => $user->id,
            'name' => 'Salary',
            'type' => 'income',
        ]);

        $catFood = Category::create([
            'user_id' => $user->id,
            'name' => 'Food & Dining',
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
            'category_id' => $catFood->id,
            'title' => 'Sushi Dinner',
            'amount' => 2000000,
            'base_amount' => 2000000,
            'currency' => 'IDR',
            'type' => 'expense',
            'transaction_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('wrapped.index'));

        $response->assertOk();
        $response->assertSee('Financial Wrapped & Infographic');
        $response->assertSee('The Wealth Architect');
        $response->assertSee('Food & Dining');
        $response->assertSee('Sushi Dinner');
    }
}

