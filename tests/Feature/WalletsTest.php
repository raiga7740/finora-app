<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_wallets_page_with_clean_empty_state(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/wallets');

        $response->assertStatus(200);
        $response->assertSee('Total Liquid Balance');
        $response->assertSee('Bank Accounts');
        $response->assertSee('No Wallets or Accounts Yet');

        // Check that no dummy wallets were auto-seeded
        $this->assertEquals(0, $user->wallets()->count());
    }

    public function test_user_can_create_new_wallet(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/wallets', [
            'name' => 'Bank BNI Tabungan',
            'type' => 'bank',
            'account_number' => '•••• 9988',
            'initial_balance' => 1500000,
            'color' => '#f36f21',
            'is_primary' => false,
            'notes' => 'Rekening belanja online',
        ]);

        $response->assertRedirect('/wallets');
        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'name' => 'Bank BNI Tabungan',
            'type' => 'bank',
            'initial_balance' => 1500000,
        ]);
    }

    public function test_user_can_transfer_balance_between_wallets(): void
    {
        $user = User::factory()->create();

        $source = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA Rekening',
            'type' => 'bank',
            'initial_balance' => 1000000,
            'color' => '#005baa',
            'is_primary' => true,
        ]);

        $dest = Wallet::create([
            'user_id' => $user->id,
            'name' => 'GoPay Digital',
            'type' => 'ewallet',
            'initial_balance' => 50000,
            'color' => '#00aed6',
            'is_primary' => false,
        ]);

        $response = $this->actingAs($user)->post('/wallets/transfer', [
            'source_wallet_id' => $source->id,
            'destination_wallet_id' => $dest->id,
            'amount' => 200000,
            'transaction_date' => now()->toDateString(),
            'notes' => 'Top up GoPay untuk makan siang',
        ]);

        $response->assertRedirect('/wallets');

        // Verify transaction is recorded
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'wallet_id' => $source->id,
            'destination_wallet_id' => $dest->id,
            'amount' => 200000,
            'type' => 'transfer',
        ]);

        // Verify live balance calculations
        $source->refresh();
        $dest->refresh();

        $this->assertEquals(800000, $source->current_balance);
        $this->assertEquals(250000, $dest->current_balance);
    }

    public function test_user_cannot_transfer_between_same_wallet(): void
    {
        $user = User::factory()->create();

        $source = Wallet::create([
            'user_id' => $user->id,
            'name' => 'BCA Rekening',
            'type' => 'bank',
            'initial_balance' => 1000000,
            'color' => '#005baa',
            'is_primary' => true,
        ]);

        $response = $this->actingAs($user)->post('/wallets/transfer', [
            'source_wallet_id' => $source->id,
            'destination_wallet_id' => $source->id,
            'amount' => 100000,
        ]);

        $response->assertSessionHasErrors(['destination_wallet_id']);
    }

    public function test_user_can_update_and_delete_wallet(): void
    {
        $user = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'Old Wallet',
            'type' => 'cash',
            'initial_balance' => 100000,
            'color' => '#00c078',
        ]);

        // Update
        $this->actingAs($user)->put("/wallets/{$wallet->id}", [
            'name' => 'Dompet Fisik Harian',
            'type' => 'cash',
            'initial_balance' => 200000,
            'color' => '#00c078',
        ])->assertRedirect('/wallets');

        $this->assertDatabaseHas('wallets', [
            'id' => $wallet->id,
            'name' => 'Dompet Fisik Harian',
            'initial_balance' => 200000,
        ]);

        // Delete
        $this->actingAs($user)->delete("/wallets/{$wallet->id}")
            ->assertRedirect('/wallets');

        $this->assertDatabaseMissing('wallets', [
            'id' => $wallet->id,
        ]);
    }

    public function test_international_users_can_seed_regional_wallets_via_model_helper(): void
    {
        // 1. USD User
        $usdUser = User::factory()->create(['currency' => 'USD', 'locale' => 'en']);
        Wallet::seedDefaultWalletsForUser($usdUser);
        $usdResponse = $this->actingAs($usdUser)->get('/wallets');
        $usdResponse->assertStatus(200);
        $usdResponse->assertSee('Chase Premier Checking');
        $usdResponse->assertSee('PayPal & Apple Cash');
        $this->assertEquals(4, $usdUser->wallets()->count());

        // 2. EUR User
        $eurUser = User::factory()->create(['currency' => 'EUR', 'locale' => 'en']);
        Wallet::seedDefaultWalletsForUser($eurUser);
        $eurResponse = $this->actingAs($eurUser)->get('/wallets');
        $eurResponse->assertStatus(200);
        $eurResponse->assertSee('Revolut Current Account');
        $eurResponse->assertSee('N26 Smart Savings');
        $this->assertEquals(4, $eurUser->wallets()->count());

        // 3. GBP User
        $gbpUser = User::factory()->create(['currency' => 'GBP', 'locale' => 'en']);
        Wallet::seedDefaultWalletsForUser($gbpUser);
        $gbpResponse = $this->actingAs($gbpUser)->get('/wallets');
        $gbpResponse->assertStatus(200);
        $gbpResponse->assertSee('Barclays Current Account');
        $gbpResponse->assertSee('Wise Multi-Currency');
        $this->assertEquals(4, $gbpUser->wallets()->count());
    }

    public function test_multi_currency_wallets_and_cross_currency_transfer(): void
    {
        \Illuminate\Support\Facades\Cache::put('finora_currency_rates_to_idr', \App\Services\CurrencyRateService::RATES_TO_IDR, now()->addHour());

        $user = User::factory()->create([
            'currency' => 'IDR',
            'locale' => 'en',
        ]);

        // 1. Create USD wallet under IDR user
        $response = $this->actingAs($user)->post('/wallets', [
            'name' => 'Wise USD Account',
            'type' => 'ewallet',
            'currency' => 'USD',
            'initial_balance' => 500.00,
            'color' => '#2ed06e',
        ]);
        $response->assertRedirect('/wallets');

        $wiseWallet = $user->wallets()->where('name', 'Wise USD Account')->first();
        $this->assertNotNull($wiseWallet);
        $this->assertEquals('USD', $wiseWallet->currency);
        $this->assertEquals(500.00, $wiseWallet->current_balance);

        // Converted balance to IDR (500 * 16,000 = 8,000,000)
        $this->assertEquals(8000000, $wiseWallet->converted_balance);

        // 2. Create local IDR bank account
        $bcaWallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'Bank BCA',
            'type' => 'bank',
            'currency' => 'IDR',
            'initial_balance' => 16000000, // Rp 16.000.000
            'color' => '#005baa',
        ]);

        // View page and verify native currency & converted badge display
        $viewResponse = $this->actingAs($user)->get('/wallets');
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('$500.00'); // native USD balance
        $viewResponse->assertSee('USD'); // currency badge

        // 3. Execute cross-currency transfer from BCA (IDR) to Wise (USD)
        // Transfer Rp 1.600.000 IDR -> Should become $100.00 USD at 16,000 rate
        $transferResponse = $this->actingAs($user)->post('/wallets/transfer', [
            'source_wallet_id' => $bcaWallet->id,
            'destination_wallet_id' => $wiseWallet->id,
            'amount' => 1600000,
            'destination_amount' => 100.00,
            'transaction_date' => now()->toDateString(),
            'notes' => 'Top up Wise balance from BCA',
        ]);

        $transferResponse->assertRedirect('/wallets');

        // Verify balances
        $bcaWallet->refresh();
        $wiseWallet->refresh();

        $this->assertEquals(14400000, $bcaWallet->current_balance); // 16,000,000 - 1,600,000
        $this->assertEquals(600.00, $wiseWallet->current_balance);   // 500 + 100

        // Verify transaction entry
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'wallet_id' => $bcaWallet->id,
            'destination_wallet_id' => $wiseWallet->id,
            'amount' => 1600000,
            'currency' => 'IDR',
            'destination_amount' => 100.00,
            'type' => 'transfer',
        ]);
    }
}
