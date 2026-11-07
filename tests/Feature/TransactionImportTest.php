<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class TransactionImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_import_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/transactions/import');

        $response->assertStatus(200);
        $response->assertSee('Smart Bank Statement Import');
        $response->assertSee('Select Destination Wallet');
    }

    public function test_user_can_download_sample_csv_template(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/transactions/import/template');

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename="finora_sample_mutasi.csv"');
    }

    public function test_user_can_parse_pasted_statement_text_with_auto_categorization(): void
    {
        $user = User::factory()->create();

        $catFood = Category::create([
            'user_id' => $user->id,
            'name' => 'Food & Dining',
            'type' => 'expense',
        ]);

        $catSalary = Category::create([
            'user_id' => $user->id,
            'name' => 'Salary / Inflow',
            'type' => 'income',
        ]);

        $pasteData = "Tanggal,Keterangan,Tipe,Nominal\n" .
                     "05/11/2026,QRIS INDOMARET TANGERANG,EXPENSE,65000\n" .
                     "01/11/2026,TRANSFER GAJI PAYROLL KANTOR,INCOME,8500000\n";

        $response = $this->actingAs($user)->post('/transactions/import/parse', [
            'paste_text' => $pasteData,
        ]);

        $response->assertStatus(200);
        $response->assertSee('Transaction Statement Preview');
        $response->assertSee('Indomaret');
        $response->assertSee('Payroll');
    }

    public function test_user_can_confirm_and_save_parsed_transactions(): void
    {
        $user = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'name' => 'Bank BCA',
            'type' => 'bank',
            'initial_balance' => 1000000,
            'color' => '#005baa',
            'is_primary' => true,
        ]);

        $cat = Category::create([
            'user_id' => $user->id,
            'name' => 'Food & Dining',
            'type' => 'expense',
        ]);

        $transactionsToSave = [
            0 => [
                'selected' => '1',
                'date' => '2026-11-05',
                'title' => 'Indomaret Belanja',
                'type' => 'expense',
                'category_id' => $cat->id,
                'amount' => 50000,
                'notes' => 'Imported via test',
            ],
            1 => [
                'selected' => '1',
                'date' => '2026-11-04',
                'title' => 'Kopi Starbucks',
                'type' => 'expense',
                'category_id' => $cat->id,
                'amount' => 55000,
                'notes' => 'Imported via test',
            ],
            2 => [
                'selected' => '0', // Unchecked row
                'date' => '2026-11-03',
                'title' => 'Should Not Save',
                'type' => 'expense',
                'amount' => 999999,
            ]
        ];

        $response = $this->actingAs($user)->post('/transactions/import/confirm', [
            'wallet_id' => $wallet->id,
            'transactions' => $transactionsToSave,
        ]);

        $response->assertRedirect('/transactions');
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'title' => 'Indomaret Belanja',
            'amount' => 50000,
            'type' => 'expense',
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'title' => 'Kopi Starbucks',
            'amount' => 55000,
            'type' => 'expense',
        ]);

        $this->assertDatabaseMissing('transactions', [
            'title' => 'Should Not Save',
        ]);

        // Verify wallet live balance: 1000000 - 50000 - 55000 = 895000
        $wallet->refresh();
        $this->assertEquals(895000, $wallet->current_balance);
    }
}
