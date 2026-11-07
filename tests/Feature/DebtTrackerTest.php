<?php

namespace Tests\Feature;

use App\Models\Debt;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebtTrackerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_debts_index(): void
    {
        $user = User::factory()->create(['locale' => 'en', 'currency' => 'IDR']);

        $response = $this->actingAs($user)->get(route('debts.index'));

        $response->assertOk();
        $response->assertSee('Debts & Loans Tracker');
    }

    public function test_user_can_create_payable_and_receivable_debts(): void
    {
        $user = User::factory()->create(['currency' => 'IDR']);

        $response = $this->actingAs($user)->post(route('debts.store'), [
            'type' => 'payable',
            'person_name' => 'Budi Santoso',
            'person_contact' => '081234567890',
            'title' => 'Pinjaman Modal Usaha',
            'total_amount' => 1500000,
            'paid_amount' => 500000,
            'start_date' => '2026-11-01',
            'due_date' => '2026-12-01',
            'notes' => 'Transfer BCA',
        ]);

        $response->assertRedirect(route('debts.index'));

        $this->assertDatabaseHas('debts', [
            'user_id' => $user->id,
            'type' => 'payable',
            'person_name' => 'Budi Santoso',
            'title' => 'Pinjaman Modal Usaha',
            'total_amount' => 1500000,
            'paid_amount' => 500000,
            'status' => 'active',
        ]);
    }

    public function test_user_can_record_partial_and_full_installment_payments_with_wallet_sync(): void
    {
        $user = User::factory()->create(['currency' => 'IDR']);

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA Utama',
            'type' => 'bank',
            'currency' => 'IDR',
            'initial_balance' => 5000000,
            'is_primary' => true,
        ]);

        $debt = Debt::create([
            'user_id' => $user->id,
            'type' => 'payable',
            'person_name' => 'Andi Wijaya',
            'person_contact' => '081299998888',
            'title' => 'Cicilan Kamera',
            'total_amount' => 1000000,
            'paid_amount' => 0,
            'currency' => 'IDR',
            'start_date' => '2026-11-01',
            'status' => 'active',
        ]);

        // First partial installment (400,000)
        $this->actingAs($user)->post(route('debts.pay', $debt), [
            'amount' => 400000,
            'payment_date' => '2026-11-05',
            'wallet_id' => $wallet->id,
            'notes' => 'Cicilan 1',
        ])->assertRedirect(route('debts.index'));

        $debt->refresh();
        $this->assertEquals(400000, (float) $debt->paid_amount);
        $this->assertEquals('active', $debt->status);
        $this->assertEquals(600000, $debt->remaining_amount);

        // Verify expense transaction was recorded for wallet
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => 400000,
            'type' => 'expense',
        ]);

        // Second full settlement installment (600,000)
        $this->actingAs($user)->post(route('debts.pay', $debt), [
            'amount' => 600000,
            'payment_date' => '2026-11-07',
            'wallet_id' => $wallet->id,
            'notes' => 'Pelunasan',
        ])->assertRedirect(route('debts.index'));

        $debt->refresh();
        $this->assertEquals(1000000, (float) $debt->paid_amount);
        $this->assertEquals('paid', $debt->status);
        $this->assertEquals(0, $debt->remaining_amount);
        $this->assertCount(2, $debt->payments);
    }

    public function test_user_cannot_modify_another_users_debt(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $debt = Debt::create([
            'user_id' => $owner->id,
            'type' => 'receivable',
            'person_name' => 'Siti',
            'title' => 'Pinjaman Pribadi',
            'total_amount' => 500000,
            'paid_amount' => 0,
            'currency' => 'IDR',
            'status' => 'active',
        ]);

        $this->actingAs($intruder)
            ->delete(route('debts.destroy', $debt))
            ->assertForbidden();
    }

    public function test_user_can_save_split_bill_participants_into_receivables(): void
    {
        $user = User::factory()->create(['currency' => 'IDR']);

        $response = $this->actingAs($user)->post(route('debts.split-bill'), [
            'title' => 'Makan Malam Sushi Tei',
            'due_date' => '2026-11-15',
            'payment_info' => 'BCA 8821009912 a/n Gayuh',
            'participants' => [
                [
                    'name' => 'Raka',
                    'contact' => '081211112222',
                    'amount' => 115000,
                    'items_note' => 'Salmon Mentai + Ocha',
                ],
                [
                    'name' => 'Dina',
                    'contact' => '081233334444',
                    'amount' => 92000,
                    'items_note' => 'Ramen + Gyoza',
                ],
            ],
        ]);

        $response->assertRedirect(route('debts.index'));

        $this->assertDatabaseHas('debts', [
            'user_id' => $user->id,
            'type' => 'receivable',
            'person_name' => 'Raka',
            'title' => 'Split Bill: Makan Malam Sushi Tei',
            'total_amount' => 115000,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('debts', [
            'user_id' => $user->id,
            'type' => 'receivable',
            'person_name' => 'Dina',
            'title' => 'Split Bill: Makan Malam Sushi Tei',
            'total_amount' => 92000,
            'status' => 'active',
        ]);
    }
}

