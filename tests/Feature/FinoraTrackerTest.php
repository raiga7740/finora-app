<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Budget;
use App\Models\SavingsGoal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinoraTrackerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_dashboard_with_authenticated_session(): void
    {
        $user = User::factory()->create(['locale' => 'en']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('FINANCIAL COMMAND CENTER');
        $response->assertSee('Total Net Balance');
    }

    public function test_user_can_create_and_view_categories(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/categories', [
            'name' => 'Food & Dining',
            'type' => 'expense',
            'description' => 'Groceries and daily dining',
        ]);

        $response->assertRedirect('/categories');
        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Food & Dining',
            'type' => 'expense',
        ]);
    }

    public function test_user_can_create_transaction_and_calculate_totals(): void
    {
        $user = User::factory()->create();
        $cat = Category::create([
            'user_id' => $user->id,
            'name' => 'Salary',
            'type' => 'income',
        ]);

        $response = $this->actingAs($user)->post('/transactions', [
            'title' => 'Monthly Salary',
            'amount' => 5000000,
            'type' => 'income',
            'payment_method' => 'Bank BCA',
            'category_id' => $cat->id,
            'transaction_date' => now()->toDateString(),
            'notes' => 'PT Tech Indonesia',
        ]);

        $response->assertRedirect('/transactions');
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'title' => 'Monthly Salary',
            'amount' => 5000000,
            'payment_method' => 'Bank BCA',
        ]);
    }

    public function test_user_cannot_access_or_modify_other_user_transactions(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $cat = Category::create([
            'user_id' => $user1->id,
            'name' => 'Secret Inflow',
            'type' => 'income',
        ]);

        $tx = Transaction::create([
            'user_id' => $user1->id,
            'category_id' => $cat->id,
            'amount' => 1000000,
            'type' => 'income',
            'transaction_date' => now()->toDateString(),
            'title' => 'User1 Transaction',
        ]);

        // User2 attempts to edit User1's transaction
        $response = $this->actingAs($user2)->get("/transactions/{$tx->id}/edit");
        $response->assertStatus(403);
    }

    public function test_user_can_create_budget_and_monitor_progress(): void
    {
        $user = User::factory()->create();
        $cat = Category::create([
            'user_id' => $user->id,
            'name' => 'Transportation',
            'type' => 'expense',
        ]);

        $response = $this->actingAs($user)->post('/budgets', [
            'name' => 'Monthly Transport Cap',
            'amount' => 500000,
            'period' => 'monthly',
            'category_id' => $cat->id,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
        ]);

        $response->assertRedirect('/budgets');
        $this->assertDatabaseHas('budgets', [
            'user_id' => $user->id,
            'name' => 'Monthly Transport Cap',
            'amount' => 500000,
        ]);
    }

    public function test_user_can_create_savings_goal_and_add_contributions(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/savings', [
            'name' => 'MacBook Pro M3 Fund',
            'target_amount' => 25000000,
            'target_date' => now()->addMonths(6)->toDateString(),
            'description' => 'High Priority • Tech Equipment',
        ]);

        $response->assertRedirect('/savings');
        $goal = SavingsGoal::where('name', 'MacBook Pro M3 Fund')->first();
        $this->assertNotNull($goal);

        // Add funds to goal
        $fundResponse = $this->actingAs($user)->post("/savings/{$goal->id}/add-funds", [
            'amount' => 5000000,
            'contributed_at' => now()->toDateString(),
            'notes' => 'Initial deposit',
        ]);

        $fundResponse->assertRedirect('/savings');
        $goal->refresh();
        $this->assertEquals(5000000, $goal->current_amount);
    }

    public function test_user_can_export_csv_and_pdf_reports(): void
    {
        $user = User::factory()->create();

        $csvResponse = $this->actingAs($user)->get('/reports/export/csv');
        $csvResponse->assertStatus(200);

        $pdfResponse = $this->actingAs($user)->get('/reports/export/pdf');
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertSee('Financial Statement');
    }

    public function test_dashboard_supports_period_filters_and_custom_ranges(): void
    {
        $user = User::factory()->create();

        // 1. Default (This Month)
        $r1 = $this->actingAs($user)->get('/dashboard?period=this_month');
        $r1->assertStatus(200);
        $r1->assertSee('This Month');

        // 2. Last 30 Days
        $r2 = $this->actingAs($user)->get('/dashboard?period=last_30_days');
        $r2->assertStatus(200);
        $r2->assertSee('Last 30 Days');

        // 3. Quarter
        $r3 = $this->actingAs($user)->get('/dashboard?period=quarter');
        $r3->assertStatus(200);

        // 4. Custom Month & Year
        $r4 = $this->actingAs($user)->get('/dashboard?month=10&year=2026');
        $r4->assertStatus(200);
        $r4->assertSee('October 2026');
    }

    public function test_ai_assistant_chat_endpoint_works_with_local_and_cloud_api(): void
    {
        $user = User::factory()->create(['name' => 'Budi Santoso']);

        // 1. Test without API key (Smart Local Analysis fallback)
        config(['services.ai.key' => '']);
        $response = $this->actingAs($user)->postJson('/ai/chat', [
            'message' => 'Analisis kesehatan keuangan saya bulan ini',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['reply', 'mode'])
            ->assertJsonPath('mode', 'local_intelligence');

        // 2. Test with Groq / Grok API key using Http::fake
        \Illuminate\Support\Facades\Http::fake([
            '*/chat/completions' => \Illuminate\Support\Facades\Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Halo Budi, pengeluaran Anda bulan ini sangat terkendali.',
                        ],
                    ],
                ],
            ], 200),
        ]);

        config(['services.ai.key' => 'gsk_test_dummy_key']);
        $cloudResponse = $this->actingAs($user)->postJson('/ai/chat', [
            'message' => 'Apakah pengeluaran saya terkendali?',
        ]);

        $cloudResponse->assertStatus(200)
            ->assertJsonPath('mode', 'cloud_ai')
            ->assertJsonPath('reply', 'Halo Budi, pengeluaran Anda bulan ini sangat terkendali.');

        // 3. Test "Bagaimana cara mengelola keuangan dengan baik"
        config(['services.ai.key' => '']);
        $adviceResponse = $this->actingAs($user)->postJson('/ai/chat', [
            'message' => 'Bagaimana cara mengelola keuangan dengan baik',
        ]);

        $adviceResponse->assertStatus(200)
            ->assertJsonPath('mode', 'local_intelligence');
        $this->assertStringContainsString('50/30/20', $adviceResponse->json('reply'));

        // 4. Test Out-of-Context Guardrail ("Tolong buatkan kode html" & "bagaimana cara buat kopi yang enak")
        $htmlResponse = $this->actingAs($user)->postJson('/ai/chat', [
            'message' => 'Tolong buatkan kode html',
        ]);
        $htmlResponse->assertStatus(200)
            ->assertJsonPath('mode', 'guardrail_rejected');
        $this->assertStringContainsString('tidak dapat merespons pertanyaan di luar konteks keuangan', $htmlResponse->json('reply'));

        $coffeeResponse = $this->actingAs($user)->postJson('/ai/chat', [
            'message' => 'bagaimana cara buat kopi yang enak',
        ]);
        $coffeeResponse->assertStatus(200)
            ->assertJsonPath('mode', 'guardrail_rejected');
        $this->assertStringContainsString('tidak dapat merespons pertanyaan di luar konteks keuangan', $coffeeResponse->json('reply'));
    }

    public function test_interactive_notification_center_displays_alerts_and_marks_as_read(): void
    {
        $user = User::factory()->create();
        $cat = Category::create([
            'user_id' => $user->id,
            'name' => 'Makan & Minum',
            'type' => 'expense',
        ]);

        Budget::create([
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'name' => 'Budget Makan',
            'amount' => 1000000,
            'period' => 'monthly',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'category_id' => $cat->id,
            'title' => 'Restoran Keluarga',
            'amount' => 900000,
            'type' => 'expense',
            'payment_method' => 'QRIS',
            'transaction_date' => now(),
        ]);

        $dashboard = $this->actingAs($user)->get('/dashboard');
        $dashboard->assertStatus(200);
        $dashboard->assertSee('Notification Center');
        $dashboard->assertSee('Budget Near Limit: Budget Makan');

        $readResponse = $this->actingAs($user)->postJson('/notifications/read');
        $readResponse->assertStatus(200)
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('unread_count', 0);
    }
}

