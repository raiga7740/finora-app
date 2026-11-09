<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('calculator.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_calculator_page(): void
    {
        $user = User::factory()->create();

        Wallet::create([
            'user_id' => $user->id,
            'name' => 'Dompet Utama',
            'type' => 'bank',
            'currency' => 'IDR',
            'initial_balance' => 2000000,
            'is_primary' => true,
        ]);

        \App\Models\Debt::create([
            'user_id' => $user->id,
            'type' => 'payable',
            'person_name' => 'Bank Mandiri',
            'title' => 'Pinjaman Modal Usaha',
            'total_amount' => 10000000,
            'paid_amount' => 2000000,
            'currency' => 'IDR',
            'status' => 'active',
            'due_date' => now()->addMonths(6),
        ]);

        $response = $this->actingAs($user)->get(route('calculator.index'));

        $response->assertStatus(200);
        $response->assertSee('Smart Calculator');
        $response->assertSee('Smart Scratchpad');
        $response->assertSee('Jam Kerja');
        $response->assertSee('Simulasi Cicilan');
        $response->assertSee('Efek Kopi (Compounding)');
    }

    public function test_can_record_quick_transaction_from_calculator(): void
    {
        $user = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'initial_balance' => 5000000,
            'is_primary' => true,
        ]);

        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Makanan & Minuman',
            'type' => 'expense',
        ]);

        $response = $this->actingAs($user)->postJson(route('calculator.transaction'), [
            'title' => 'Makan Siang Resto',
            'amount' => 125000,
            'type' => 'expense',
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'transaction_date' => now()->toDateString(),
            'notes' => 'Hasil hitungan Smart Scratchpad',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'title' => 'Makan Siang Resto',
            'amount' => 125000,
            'type' => 'expense',
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
        ]);
    }
}
