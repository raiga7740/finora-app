<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_cash_flow_calendar_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/calendar');

        $response->assertStatus(200);
        $response->assertSee('Cash Flow Calendar');
        $response->assertSee('Cash Flow Radar');
        $response->assertSee('No-Spend Days');
    }

    public function test_calendar_accurately_aggregates_daily_cash_flow(): void
    {
        $user = User::factory()->create();
        $cat = Category::create([
            'user_id' => $user->id,
            'name' => 'Food & Dining',
            'type' => 'expense',
        ]);

        $targetDate = Carbon::now()->startOfMonth()->addDays(4); // 5th of this month

        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'amount' => 125000,
            'type' => 'expense',
            'title' => 'Makan Siang Resto',
            'transaction_date' => $targetDate->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/calendar');

        $response->assertStatus(200);
        $response->assertSee('-Rp125.000', false);
    }

    public function test_user_can_navigate_to_past_months(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/calendar?month=10&year=2026');

        $response->assertStatus(200);
        $response->assertSee('October 2026');
    }
}

