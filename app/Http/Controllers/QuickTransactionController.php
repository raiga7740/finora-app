<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CurrencyRateService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuickTransactionController extends Controller
{
    /**
     * Parse natural language transaction text and store the transaction immediately.
     */
    public function store(Request $request, CurrencyRateService $currencyService): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'raw_input' => ['required', 'string', 'max:500'],
            'override_type' => ['nullable', 'in:income,expense'],
            'override_amount' => ['nullable', 'numeric', 'min:0.01'],
            'override_category_id' => ['nullable', 'exists:categories,id'],
            'override_wallet_id' => ['nullable', 'exists:wallets,id'],
            'override_date' => ['nullable', 'date'],
            'override_title' => ['nullable', 'string', 'max:255'],
        ]);

        $parsed = $this->parseNaturalText($user, $validated['raw_input']);

        $amount = ! empty($validated['override_amount'])
            ? (float) $validated['override_amount']
            : $parsed['amount'];

        if ($amount <= 0) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => __('Could not detect a valid transaction amount. Example: "makan siang 35rb bca"'),
                ], 422);
            }

            return back()->withErrors([
                'raw_input' => __('Could not detect a valid transaction amount. Example: "makan siang 35rb bca"'),
            ])->withInput();
        }

        $type = $validated['override_type'] ?? $parsed['type'];
        $categoryId = $validated['override_category_id'] ?? $parsed['category_id'];
        $walletId = $validated['override_wallet_id'] ?? $parsed['wallet_id'];
        $txDate = $validated['override_date'] ?? $parsed['transaction_date'];
        $title = ! empty($validated['override_title']) ? $validated['override_title'] : $parsed['title'];

        $userCurrency = strtoupper($user->currency ?? 'IDR');
        $txCurrency = $userCurrency;
        $paymentMethod = $parsed['payment_method'];

        if ($walletId) {
            $wallet = $user->wallets()->where('id', $walletId)->first();
            if ($wallet) {
                $txCurrency = strtoupper($wallet->currency ?? $userCurrency);
                $paymentMethod = $wallet->name;
            }
        }

        $rateToBase = $currencyService->getExchangeRate($txCurrency, $userCurrency);
        $baseAmount = round($amount * $rateToBase, 2);

        $transaction = $user->transactions()->create([
            'wallet_id' => $walletId,
            'category_id' => $categoryId,
            'title' => $title,
            'amount' => $amount,
            'currency' => $txCurrency,
            'exchange_rate' => $rateToBase,
            'base_amount' => $baseAmount,
            'type' => $type,
            'payment_method' => $paymentMethod,
            'transaction_date' => $txDate,
            'notes' => __('Recorded via Smart Quick-Add') . ': "' . trim($validated['raw_input']) . '"',
        ]);

        $formattedAmount = $user->formatCurrency($baseAmount);
        $statusMsg = __('Quick Transaction saved: :title (:amount)', [
            'title' => $transaction->title,
            'amount' => $formattedAmount,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'ok',
                'message' => $statusMsg,
                'transaction' => $transaction,
            ]);
        }

        return back()->with('status', $statusMsg);
    }

    /**
     * Parse natural language input into structured transaction fields.
     *
     * @return array{
     *   title: string,
     *   amount: float,
     *   type: string,
     *   category_id: int|null,
     *   wallet_id: int|null,
     *   payment_method: string,
     *   transaction_date: string
     * }
     */
    public function parseNaturalText(User $user, string $input): array
    {
        $text = trim($input);
        $working = $text;

        // 1. Extract Date Keywords
        $txDate = Carbon::now()->toDateString();
        if (preg_match('/\b(kemarin|yesterday)\b/i', $working, $m)) {
            $txDate = Carbon::now()->subDay()->toDateString();
            $working = preg_replace('/\b' . preg_quote($m[0], '/') . '\b/i', '', $working, 1);
        } elseif (preg_match('/\b(hari\s+ini|today)\b/i', $working, $m)) {
            $txDate = Carbon::now()->toDateString();
            $working = preg_replace('/\b' . preg_quote($m[0], '/') . '\b/i', '', $working, 1);
        } elseif (preg_match('/\b(\d+)\s+hari\s+lalu\b/i', $working, $m)) {
            $daysAgo = min(365, max(1, (int) $m[1]));
            $txDate = Carbon::now()->subDays($daysAgo)->toDateString();
            $working = preg_replace('/\b' . preg_quote($m[0], '/') . '\b/i', '', $working, 1);
        }

        // 2. Extract Amount with shorthand multipliers (jt, juta, rb, ribu, k, m) or plain numbers
        $amount = 0.0;
        if (preg_match('/(?:rp\.?\s*)?(\d+(?:[.,]\d+)?)\s*(jt|juta|m|rb|ribu|k)\b/i', $working, $m)) {
            $numStr = str_replace(',', '.', $m[1]);
            $val = (float) $numStr;
            $unit = strtolower($m[2]);
            if (in_array($unit, ['jt', 'juta', 'm'])) {
                $amount = round($val * 1000000, 2);
            } else {
                $amount = round($val * 1000, 2);
            }
            $working = str_replace($m[0], ' ', $working);
        } elseif (preg_match('/(?:rp\.?\s*)?(\d{1,3}(?:\.\d{3})+|\d{1,3}(?:,\d{3})+|\d+(?:\.\d+)?)\b/i', $working, $m)) {
            $rawNum = $m[1];
            if (preg_match('/^\d{1,3}(\.\d{3})+$/', $rawNum)) {
                $cleanNum = str_replace('.', '', $rawNum);
            } elseif (preg_match('/^\d{1,3}(,\d{3})+$/', $rawNum)) {
                $cleanNum = str_replace(',', '', $rawNum);
            } else {
                $cleanNum = str_replace(',', '.', $rawNum);
            }
            $amount = (float) $cleanNum;
            $working = preg_replace('/' . preg_quote($m[0], '/') . '/i', ' ', $working, 1);
        }

        // 3. Detect Wallet / Payment Method
        $wallets = $user->wallets()->orderByDesc('is_primary')->get();
        $matchedWallet = null;
        $paymentMethod = 'Cash / Transfer';

        foreach ($wallets as $wallet) {
            $wName = strtolower($wallet->name);
            $tokens = array_filter(explode(' ', preg_replace('/[^a-z0-9\s]/i', ' ', $wName)), function ($t) {
                return strlen($t) >= 3 && ! in_array($t, ['bank', 'dompet', 'wallet', 'account', 'rekening', 'utama']);
            });

            foreach ($tokens as $tok) {
                if (preg_match('/\b' . preg_quote($tok, '/') . '\b/i', $working, $wm)) {
                    $matchedWallet = $wallet;
                    $paymentMethod = $wallet->name;
                    $working = preg_replace('/\b' . preg_quote($wm[0], '/') . '\b/i', ' ', $working, 1);
                    break 2;
                }
            }
        }

        if (! $matchedWallet) {
            $knownMethods = [
                'bca' => 'Bank BCA',
                'mandiri' => 'Bank Mandiri',
                'bri' => 'Bank BRI',
                'bni' => 'Bank BNI',
                'jago' => 'Bank Jago',
                'seabank' => 'SeaBank',
                'gopay' => 'GoPay',
                'ovo' => 'OVO',
                'dana' => 'DANA',
                'shopeepay' => 'ShopeePay',
                'qris' => 'QRIS',
                'tunai' => 'Cash',
                'cash' => 'Cash',
                'paypal' => 'PayPal',
            ];
            foreach ($knownMethods as $kw => $label) {
                if (preg_match('/\b' . $kw . '\b/i', $working, $km)) {
                    $paymentMethod = $label;
                    $working = preg_replace('/\b' . preg_quote($km[0], '/') . '\b/i', ' ', $working, 1);
                    break;
                }
            }
            $matchedWallet = $wallets->first();
        }

        // 4. Detect Income vs Expense
        $incomeKeywords = '/\b(gaji|salary|bonus|thr|terima|masuk|inflow|freelance|proyek|project|komisi|dividen|refund|cashback|jual|penjualan|pendapatan|pemasukan)\b/i';
        $type = preg_match($incomeKeywords, $text) ? 'income' : 'expense';

        // 5. Match Category
        $categories = $user->categories()->get();
        $matchedCategory = null;

        $categoryKeywordMap = [
            'food' => ['makan', 'minum', 'kopi', 'nasi', 'sarapan', 'siang', 'malam', 'snack', 'jajan', 'resto', 'warung', 'gofood', 'grabfood', 'shopeefood', 'coffee', 'lunch', 'dinner', 'breakfast', 'bakso', 'mie', 'ayam', 'sate', 'padang', 'ramen', 'sushi', 'martabak'],
            'transport' => ['bensin', 'pertamax', 'pertalite', 'shell', 'parkir', 'tol', 'gojek', 'goride', 'gocar', 'grab', 'maxim', 'mrt', 'krl', 'kereta', 'bus', 'transjakarta', 'tiket', 'servis', 'oli', 'transport', 'fuel', 'uber', 'taxi'],
            'bills' => ['listrik', 'pln', 'token', 'air', 'pdam', 'wifi', 'internet', 'indihome', 'biznet', 'pulsa', 'kuota', 'paket data', 'telkomsel', 'xl', 'indosat', 'bpjs', 'asuransi', 'sewa', 'kos', 'kontrakan', 'ipl'],
            'entertainment' => ['nonton', 'bioskop', 'xxi', 'cgv', 'netflix', 'spotify', 'youtube', 'disney', 'game', 'steam', 'topup', 'konser', 'karaoke', 'liburan', 'hotel', 'staycation', 'hiburan'],
            'shopping' => ['belanja', 'shopee', 'tokopedia', 'lazada', 'tiktok', 'indomaret', 'alfamart', 'superindo', 'hypermart', 'baju', 'celana', 'sepatu', 'skincare', 'sabun', 'shampoo', 'elektronik', 'gadget'],
            'salary' => ['gaji', 'salary', 'thr', 'upah', 'paycheck', 'kantor'],
            'freelance' => ['freelance', 'proyek', 'project', 'klien', 'client', 'komisi', 'desain', 'web'],
        ];

        $lowerText = strtolower($text);
        $detectedGroup = null;
        foreach ($categoryKeywordMap as $group => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lowerText, $kw)) {
                    $detectedGroup = $group;
                    break 2;
                }
            }
        }

        if ($detectedGroup) {
            $matchedCategory = $categories->first(function ($cat) use ($detectedGroup) {
                $cName = strtolower($cat->name);
                return match ($detectedGroup) {
                    'food' => str_contains($cName, 'food') || str_contains($cName, 'makan') || str_contains($cName, 'dining'),
                    'transport' => str_contains($cName, 'transport') || str_contains($cName, 'perjalanan'),
                    'bills' => str_contains($cName, 'bill') || str_contains($cName, 'tagihan') || str_contains($cName, 'utilit'),
                    'entertainment' => str_contains($cName, 'entertain') || str_contains($cName, 'hiburan'),
                    'shopping' => str_contains($cName, 'shop') || str_contains($cName, 'belanja') || str_contains($cName, 'lifestyle'),
                    'salary' => str_contains($cName, 'salary') || str_contains($cName, 'gaji') || str_contains($cName, 'inflow'),
                    'freelance' => str_contains($cName, 'freelance') || str_contains($cName, 'project'),
                    default => false,
                };
            });
        }

        if (! $matchedCategory) {
            $matchedCategory = $categories->where('type', $type)->first() ?? $categories->first();
        }

        // 6. Clean up Title
        $working = preg_replace('/\b(via|pakai|dengan|ke|dari|di|pake|using)\b$/i', '', trim($working));
        $working = trim(preg_replace('/\s+/', ' ', $working));

        if ($working === '') {
            $title = $type === 'income' ? __('Quick Income') : __('Quick Expense');
        } else {
            $title = ucwords($working);
        }

        return [
            'title' => $title,
            'amount' => $amount,
            'type' => $type,
            'category_id' => $matchedCategory?->id,
            'wallet_id' => $matchedWallet?->id,
            'payment_method' => $paymentMethod,
            'transaction_date' => $txDate,
        ];
    }
}

