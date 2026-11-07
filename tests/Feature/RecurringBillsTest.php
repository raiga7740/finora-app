<?php

namespace Tests\Feature;

use App\Http\Controllers\NotificationController;
use App\Models\Category;
use App\Models\RecurringBill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurringBillsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_recurring_bills_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/recurring-bills');

        $response->assertStatus(200);
        $response->assertSee('Fixed Commitments');
        $response->assertSee('Bills & Subscriptions List');
        $response->assertSee('Total Monthly Commitment');
    }

    public function test_user_can_create_recurring_bill(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Bills & Utilities',
            'type' => 'expense',
        ]);

        $response = $this->actingAs($user)->post('/recurring-bills', [
            'name' => 'WiFi Indihome Fiber',
            'amount' => 350000,
            'billing_cycle' => 'monthly',
            'due_day' => 15,
            'category_id' => $category->id,
            'payment_method' => 'bca',
            'notes' => 'Nomor Pelanggan 12345678',
        ]);

        $response->assertRedirect('/recurring-bills');
        $this->assertDatabaseHas('recurring_bills', [
            'user_id' => $user->id,
            'name' => 'WiFi Indihome Fiber',
            'amount' => 350000,
            'billing_cycle' => 'monthly',
            'due_day' => 15,
            'status' => 'active',
        ]);
    }

    public function test_user_can_pay_bill_with_one_click(): void
    {
        $user = User::factory()->create();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Streaming',
            'type' => 'expense',
        ]);

        $bill = RecurringBill::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'name' => 'Netflix Premium 4K',
            'amount' => 186000,
            'billing_cycle' => 'monthly',
            'due_day' => 20,
            'status' => 'active',
            'payment_method' => 'gopay',
        ]);

        $response = $this->actingAs($user)->post("/recurring-bills/{$bill->id}/pay", [
            'payment_method' => 'gopay',
        ]);

        $response->assertRedirect('/recurring-bills');

        // Verify bill is marked paid
        $bill->refresh();
        $this->assertNotNull($bill->last_paid_at);
        $this->assertTrue($bill->isPaidForCurrentCycle());

        // Verify transaction is recorded automatically
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 186000,
            'type' => 'expense',
            'title' => 'Pembayaran: Netflix Premium 4K',
            'payment_method' => 'gopay',
        ]);
    }

    public function test_upcoming_bills_appear_in_notification_center(): void
    {
        $user = User::factory()->create();

        // Create a bill due today
        RecurringBill::create([
            'user_id' => $user->id,
            'name' => 'Tagihan Listrik PLN',
            'amount' => 500000,
            'billing_cycle' => 'monthly',
            'due_day' => Carbon::now()->day,
            'status' => 'active',
            'payment_method' => 'bca',
        ]);

        $notifs = NotificationController::getForUser($user);

        $this->assertNotEmpty($notifs['items']);
        $hasBillNotif = collect($notifs['items'])->contains(function ($item) {
            return str_contains($item['title'], 'Tagihan Listrik PLN');
        });

        $this->assertTrue($hasBillNotif, 'Upcoming bill notification was not generated');
    }

    public function test_user_can_toggle_bill_status(): void
    {
        $user = User::factory()->create();
        $bill = RecurringBill::create([
            'user_id' => $user->id,
            'name' => 'Gym Membership',
            'amount' => 450000,
            'billing_cycle' => 'monthly',
            'due_day' => 5,
            'status' => 'active',
            'payment_method' => 'bca',
        ]);

        $response = $this->actingAs($user)->post("/recurring-bills/{$bill->id}/toggle");
        $response->assertRedirect('/recurring-bills');

        $bill->refresh();
        $this->assertEquals('paused', $bill->status);
    }
}

