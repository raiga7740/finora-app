<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Category;
use App\Models\RecurringBill;
use App\Models\SavingsContribution;
use App\Models\SavingsGoal;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\CurrencyRateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyConversionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Cache::put('finora_currency_rates_to_idr', CurrencyRateService::RATES_TO_IDR, now()->addHour());
    }

    public function test_currency_rate_service_calculation(): void
    {
        $service = new CurrencyRateService();

        // 1,000,000 IDR to USD (16,000)
        $usd = $service->convert(1000000, 'IDR', 'USD');
        $this->assertEquals(62.50, $usd);

        // 62.50 USD back to IDR
        $idr = $service->convert(62.50, 'USD', 'IDR');
        $this->assertEquals(1000000, $idr);

        // 1,000,000 IDR to SGD (12,000)
        $sgd = $service->convert(1000000, 'IDR', 'SGD');
        $this->assertEquals(83.33, $sgd);

        // JPY rounding
        $jpy = $service->convert(1000000, 'IDR', 'JPY');
        $this->assertEquals(9524, $jpy);
    }

    public function test_switching_currency_converts_user_entire_ledger(): void
    {
        $user = User::factory()->create([
            'currency' => 'IDR',
        ]);

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'initial_balance' => 1600000, // Rp 1.600.000 -> Should be $100.00
            'is_primary' => true,
        ]);

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Gaji',
            'type' => 'income',
        ]);

        $tx = Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => 'income',
            'amount' => 800000, // Rp 800.000 -> Should be $50.00
            'transaction_date' => now(),
            'title' => 'Project Freelance',
        ]);

        $budget = Budget::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Monthly Budget',
            'amount' => 3200000, // Rp 3.200.000 -> Should be $200.00
            'period' => 'monthly',
            'start_date' => now()->startOfMonth(),
        ]);

        $goal = SavingsGoal::create([
            'user_id' => $user->id,
            'name' => 'Macbook',
            'target_amount' => 16000000, // Rp 16.000.000 -> Should be $1000.00
            'current_amount' => 1600000, // Rp 1.600.000 -> Should be $100.00
        ]);

        $bill = RecurringBill::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Internet',
            'amount' => 480000, // Rp 480.000 -> Should be $30.00
            'billing_cycle' => 'monthly',
            'due_date' => 15,
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile/preferences', [
                'currency'    => 'USD',
                'date_format' => 'd M Y',
                'ledger_mode' => 'strict_realtime',
                'locale'      => 'en',
                'timezone'    => 'America/New_York',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile')
            ->assertSessionHas('status', 'preferences-updated');

        $user->refresh();
        $this->assertSame('USD', $user->currency);

        $this->assertEquals(100.00, (float) $wallet->fresh()->initial_balance);
        $this->assertEquals(50.00, (float) $tx->fresh()->amount);
        $this->assertEquals(200.00, (float) $budget->fresh()->amount);
        $this->assertEquals(1000.00, (float) $goal->fresh()->target_amount);
        $this->assertEquals(100.00, (float) $goal->fresh()->current_amount);
        $this->assertEquals(30.00, (float) $bill->fresh()->amount);
    }
}
