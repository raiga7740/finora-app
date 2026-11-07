<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use App\Models\Wallet;
use App\Services\ReceiptScannerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ReceiptScannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_receipt_scanner(): void
    {
        $this->postJson('/receipts/scan', [])->assertStatus(401);
        $this->postJson('/receipts/store', [])->assertStatus(401);
    }

    public function test_heuristic_parser_accurately_extracts_receipt_data(): void
    {
        $user = User::factory()->create(['currency' => 'IDR']);
        $service = new ReceiptScannerService();

        $sampleOcr = <<<TEXT
STARBUCKS COFFEE INDONESIA
Grand Indonesia Mall, Jakarta
Date: 2026-11-07 14:22:10
Receipt No: 49102-12

1x Caffe Latte Grande 48.000
1x Cinnamon Roll 32.000

Subtotal: 80.000
PB1 10%: 8.000
TOTAL: Rp 88.000
BCA DEBIT : 88.000
TEXT;

        $parsed = $service->parseWithHeuristics($sampleOcr, $user);

        $this->assertEquals('Starbucks', $parsed['merchant_name']);
        $this->assertEquals('2026-11-07', $parsed['transaction_date']);
        $this->assertEquals(88000.0, $parsed['amount']);
        $this->assertEquals('IDR', $parsed['currency']);
        $this->assertEquals('Food & Dining', $parsed['category_hint']);
        $this->assertEquals('bank_bca', $parsed['payment_method']);
        $this->assertEquals(8000.0, $parsed['tax']);
        $this->assertNotEmpty($parsed['items']);
    }

    public function test_user_can_scan_receipt_and_receive_structured_json(): void
    {
        $user = User::factory()->create(['currency' => 'IDR']);
        Wallet::seedDefaultWalletsForUser($user);

        $category = Category::create([
            'user_id' => $user->id,
            'name'    => 'Food & Dining',
            'type'    => 'expense',
        ]);

        $receiptText = "INDOMARET POINT\nTanggal: 2026-11-06\nTOTAL BAYAR: Rp 54.500\nTUNAI";

        $response = $this->actingAs($user)->postJson('/receipts/scan', [
            'ocr_text' => $receiptText,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data'    => [
                'merchant_name'    => 'Indomaret',
                'amount'           => 54500,
                'transaction_date' => '2026-11-06',
            ],
        ]);
    }

    public function test_user_can_confirm_and_store_scanned_receipt_transaction(): void
    {
        $user = User::factory()->create(['currency' => 'IDR']);
        $wallet = Wallet::create([
            'user_id'         => $user->id,
            'name'            => 'Bank BCA',
            'type'            => 'bank',
            'initial_balance' => 1000000,
            'color'           => '#005baa',
            'is_primary'      => true,
        ]);

        $category = Category::create([
            'user_id' => $user->id,
            'name'    => 'Groceries',
            'type'    => 'expense',
            'color'   => '#00c078',
        ]);

        $response = $this->actingAs($user)->postJson('/receipts/store', [
            'title'            => 'Superindo Supermarket',
            'amount'           => 175000,
            'currency'         => 'IDR',
            'category_id'      => $category->id,
            'wallet_id'        => $wallet->id,
            'transaction_date' => '2026-11-07',
            'receipt_path'     => 'uploads/receipts/sample_receipt.jpg',
            'notes'            => 'Items: 1x Buah Apel, 2x Susu UHT | [Scanned by Finora AI]',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id'      => $user->id,
            'wallet_id'    => $wallet->id,
            'category_id'  => $category->id,
            'title'        => 'Superindo Supermarket',
            'amount'       => 175000,
            'type'         => 'expense',
            'receipt_path' => 'uploads/receipts/sample_receipt.jpg',
        ]);

        // Check wallet balance
        $wallet->refresh();
        $this->assertEquals(825000, $wallet->current_balance);
    }
}

