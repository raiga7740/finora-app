<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuickTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_record_expense_via_natural_language_quick_add(): void
    {
        $user = User::factory()->create(['currency' => 'IDR']);

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'Bank BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'initial_balance' => 1000000,
            'is_primary' => true,
        ]);

        $foodCat = Category::create([
            'user_id' => $user->id,
            'name' => 'Food & Dining',
            'type' => 'expense',
        ]);

        $response = $this->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('transactions.quick-add'), [
                'raw_input' => 'makan siang nasi padang 35rb bca',
            ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $foodCat->id,
            'title' => 'Makan Siang Nasi Padang',
            'amount' => 35000,
            'type' => 'expense',
        ]);
    }

    public function test_user_can_record_income_with_millions_shorthand_via_quick_add(): void
    {
        $user = User::factory()->create(['currency' => 'IDR']);

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'Bank Mandiri',
            'type' => 'bank',
            'currency' => 'IDR',
            'initial_balance' => 500000,
            'is_primary' => false,
        ]);

        $salaryCat = Category::create([
            'user_id' => $user->id,
            'name' => 'Salary / Inflow',
            'type' => 'income',
        ]);

        $response = $this->actingAs($user)
            ->from(route('transactions.index'))
            ->post(route('transactions.quick-add'), [
                'raw_input' => 'terima gaji bulanan 8.5jt mandiri kemarin',
            ]);

        $response->assertRedirect(route('transactions.index'));

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $salaryCat->id,
            'title' => 'Terima Gaji Bulanan',
            'amount' => 8500000,
            'type' => 'income',
        ]);

        $tx = $user->transactions()->latest('id')->first();
        $this->assertEquals(now()->subDay()->toDateString(), $tx->transaction_date->toDateString());
    }
}
