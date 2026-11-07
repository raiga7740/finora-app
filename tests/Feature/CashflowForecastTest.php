<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\RecurringBill;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\CashflowForecastService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashflowForecastTest extends TestCase
{
    use RefreshDatabase;

    private function seedUser(float $balance, float $dailyExpense): User
    {
        $user = User::factory()->create(['locale' => 'en', 'currency' => 'IDR']);

        Wallet::create([
            'user_id' => $user->id,
            'name' => 'Main',
            'type' => 'bank',
            'currency' => 'IDR',
            'initial_balance' => $balance,
            'is_primary' => true,
        ]);

        $cat = Category::create(['user_id' => $user->id, 'name' => 'Food', 'type' => 'expense']);

        for ($i = 1; $i <= 20; $i++) {
            Transaction::create([
                'user_id' => $user->id,
                'category_id' => $cat->id,
                'type' => 'expense',
                'amount' => $dailyExpense * 3,
                'transaction_date' => Carbon::today()->subDays($i * 3)->toDateString(),
                'title' => 'Meal '.$i,
            ]);
        }

        return $user;
    }

    public function test_empty_state_for_new_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/cashflow-forecast')
            ->assertOk()
            ->assertSee('Not enough data to forecast yet');
    }

    public function test_critical_forecast_detects_zero_date(): void
    {
        $user = $this->seedUser(1_000_000, 200_000);

        $forecast = app(CashflowForecastService::class)->forecast($user, 30);

        $this->assertTrue($forecast['has_data']);
        $this->assertSame('critical', $forecast['status']);
        $this->assertNotNull($forecast['zero_date']);
        $this->assertLessThan(10, $forecast['days_until_zero']);

        $this->actingAs($user)->get('/cashflow-forecast?days=30')
            ->assertOk()
            ->assertSee('predicted to run out');
    }

    public function test_safe_forecast_with_large_balance(): void
    {
        $user = $this->seedUser(500_000_000, 100_000);

        $forecast = app(CashflowForecastService::class)->forecast($user, 60);

        $this->assertSame('safe', $forecast['status']);
        $this->assertNull($forecast['zero_date']);
        $this->assertCount(61, $forecast['series']);
    }

    public function test_active_bill_is_projected_as_outflow(): void
    {
        $user = $this->seedUser(500_000_000, 100_000);

        RecurringBill::create([
            'user_id' => $user->id,
            'name' => 'Internet',
            'amount' => 450000,
            'billing_cycle' => 'weekly',
            'due_day' => 1,
            'status' => 'active',
        ]);

        $forecast = app(CashflowForecastService::class)->forecast($user, 30);

        $labels = array_column($forecast['events'], 'label');
        $this->assertContains('Internet', $labels);
        $this->assertGreaterThan(0, $forecast['fixed_outflow']);
    }

    public function test_invalid_horizon_falls_back_to_30(): void
    {
        $user = $this->seedUser(10_000_000, 50_000);

        $this->assertSame(30, app(CashflowForecastService::class)->forecast($user, 7)['horizon']);
    }
}

