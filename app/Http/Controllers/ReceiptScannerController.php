<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\CurrencyRateService;
use App\Services\ReceiptScannerService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReceiptScannerController extends Controller
{
    public function __construct(
        protected ReceiptScannerService $scannerService,
        protected CurrencyRateService $currencyService
    ) {}

    /**
     * Upload receipt image and scan OCR text with Finora AI.
     */
    public function scan(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            $request->validate([
                'image'    => ['nullable', 'file', 'image', 'max:10240'], // 10MB max
                'ocr_text' => ['nullable', 'string', 'max:50000'],
            ]);

            $receiptPath = null;
            $receiptUrl = null;

            // Save receipt image to public/uploads/receipts
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = 'receipt_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/receipts'), $filename);
                $receiptPath = 'uploads/receipts/' . $filename;
                $receiptUrl = asset($receiptPath);
            }

            $ocrText = (string) $request->input('ocr_text', '');

            // If no OCR text was passed but an image was provided
            if (empty($ocrText) && $receiptPath) {
                $ocrText = "Receipt image uploaded: {$receiptPath}";
            }

            $scanned = $this->scannerService->scan($ocrText, $user, $receiptPath);

            // Fetch user categories and wallets for dropdown in UI
            $categories = $user->categories()->where('type', 'expense')->orderBy('name')->get(['id', 'name']);
            
            $wallets = $user->wallets()->orderBy('is_primary', 'desc')->get(['id', 'name', 'type', 'currency']);
            if ($wallets->isEmpty()) {
                Wallet::seedDefaultWalletsForUser($user);
                $wallets = $user->wallets()->orderBy('is_primary', 'desc')->get(['id', 'name', 'type', 'currency']);
            }

            return response()->json([
                'success'      => true,
                'receipt_path' => $receiptPath,
                'receipt_url'  => $receiptUrl,
                'data'         => $scanned,
                'categories'   => $categories,
                'wallets'      => $wallets,
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => collect($ve->errors())->flatten()->first() ?? 'Invalid image or data submitted.',
                'errors'  => $ve->errors(),
            ], 422);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('ReceiptScannerController@scan failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses struk: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store the confirmed scanned transaction into ledger.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'currency'         => ['nullable', 'string', 'in:IDR,USD,EUR,GBP,SGD,AUD,JPY'],
            'category_id'      => ['nullable', 'exists:categories,id'],
            'wallet_id'        => ['nullable', 'exists:wallets,id'],
            'transaction_date' => ['required', 'date'],
            'receipt_path'     => ['nullable', 'string', 'max:255'],
            'notes'            => ['nullable', 'string', 'max:1000'],
        ]);

        // Default wallet if omitted
        if (empty($validated['wallet_id'])) {
            $primary = $user->wallets()->where('is_primary', true)->first() ?? $user->wallets()->first();
            $validated['wallet_id'] = $primary?->id;
        }

        $wallet = $validated['wallet_id'] ? $user->wallets()->find($validated['wallet_id']) : null;
        $curr = strtoupper($validated['currency'] ?? $wallet?->currency ?? $user->currency ?? 'IDR');

        $baseAmount = $this->currencyService->convert(
            $validated['amount'],
            $curr,
            $user->currency ?? 'IDR'
        );

        $tx = $user->transactions()->create([
            'user_id'          => $user->id,
            'wallet_id'        => $wallet?->id,
            'category_id'      => $validated['category_id'] ?? null,
            'amount'           => $validated['amount'],
            'currency'         => $curr,
            'base_amount'      => $baseAmount,
            'type'             => 'expense',
            'payment_method'   => $wallet?->name ?? 'cash',
            'transaction_date' => Carbon::parse($validated['transaction_date'])->toDateString(),
            'title'            => $validated['title'],
            'notes'            => $validated['notes'] ?? null,
            'receipt_path'     => $validated['receipt_path'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success'     => true,
                'message'     => 'Struk belanja berhasil dipindai dan dicatat ke pembukuan!',
                'transaction' => $tx,
            ]);
        }

        return redirect()->route('transactions.index')
            ->with('status', 'Struk belanja berhasil dipindai dan disimpan!');
    }
}

