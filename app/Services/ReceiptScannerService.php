<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReceiptScannerService
{
    /**
     * Parse receipt OCR text into structured transaction data.
     *
     * @return array{
     *     merchant_name: string,
     *     transaction_date: string,
     *     amount: float,
     *     currency: string,
     *     category_id: int|null,
     *     category_name: string|null,
     *     wallet_id: int|null,
     *     wallet_name: string|null,
     *     payment_method: string|null,
     *     tax: float|null,
     *     items: array<array{name: string, qty: int|float, price: float}>,
     *     notes: string,
     *     confidence: int,
     *     engine: string
     * }
     */
    public function scan(string $ocrText, User $user, ?string $imagePath = null): array
    {
        $rawText = trim($ocrText);

        // 1. Try Cloud AI Parsing if text is non-empty and AI key is set
        if (!empty($rawText) && !empty(config('services.ai.key'))) {
            $aiResult = $this->tryAiParsing($rawText, $user);
            if ($aiResult !== null) {
                return $this->enrichWithUserData($aiResult, $user, 'cloud_ai');
            }
        }

        // 2. Heuristic Rule-Based Parsing Engine (offline / fallback)
        $heuristicResult = $this->parseWithHeuristics($rawText, $user);
        return $this->enrichWithUserData($heuristicResult, $user, 'heuristic');
    }

    /**
     * Send OCR text to AI model to extract structured metadata.
     */
    protected function tryAiParsing(string $text, User $user): ?array
    {
        try {
            $apiKey = config('services.ai.key');
            $baseUrl = rtrim((string) config('services.ai.base_url', 'https://api.groq.com/openai/v1'), '/');
            $model = config('services.ai.model', 'llama-3.1-8b-instant');

            $systemPrompt = <<<PROMPT
You are a high-precision AI Financial Receipt Scanner.
Extract key receipt details from the provided OCR text into structured JSON.
Return ONLY a valid JSON object without markdown formatting, backticks, or preamble:
{
  "merchant_name": "Merchant or store name (string)",
  "transaction_date": "YYYY-MM-DD or null if not detected",
  "amount": 0.00,
  "currency": "IDR | USD | EUR | GBP | SGD | AUD | JPY",
  "category": "Food & Dining | Groceries | Shopping | Transportation | Utilities | Healthcare | Entertainment | Other",
  "payment_method": "Cash | Debit Card | Credit Card | QRIS | Transfer | null",
  "tax": 0.00,
  "items": [
    { "name": "Item description", "qty": 1, "price": 0.00 }
  ],
  "confidence_score": 90
}
PROMPT;

            $response = Http::withToken($apiKey)
                ->withoutVerifying()
                ->timeout(8)
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => "User Primary Currency: {$user->currency}\nReceipt OCR text:\n" . substr($text, 0, 3000)],
                    ],
                    'temperature' => 0.1,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                if (!empty($content)) {
                    $json = json_decode($content, true);
                    if (is_array($json) && !empty($json['amount']) && (float) $json['amount'] > 0) {
                        return [
                            'merchant_name'    => (string) ($json['merchant_name'] ?? 'Receipt Purchase'),
                            'transaction_date' => !empty($json['transaction_date']) ? (string) $json['transaction_date'] : now()->toDateString(),
                            'amount'           => (float) $json['amount'],
                            'currency'         => strtoupper((string) ($json['currency'] ?? $user->currency ?? 'IDR')),
                            'category_hint'    => (string) ($json['category'] ?? 'Shopping'),
                            'payment_method'   => (string) ($json['payment_method'] ?? 'cash'),
                            'tax'              => isset($json['tax']) ? (float) $json['tax'] : null,
                            'items'            => is_array($json['items'] ?? null) ? $json['items'] : [],
                            'confidence'       => (int) ($json['confidence_score'] ?? 92),
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::info('ReceiptScannerService: AI parsing skipped or failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Deterministic heuristic parser for OCR text lines.
     */
    public function parseWithHeuristics(string $text, User $user): array
    {
        $lines = preg_split('/[\r\n]+/', $text);
        $cleanLines = array_values(array_filter(array_map('trim', $lines)));

        // 1. Detect Merchant Name
        $merchant = $this->detectMerchant($cleanLines);

        // 2. Detect Date
        $date = $this->detectDate($cleanLines);

        // 3. Detect Amount
        $amount = $this->detectAmount($cleanLines);

        // 4. Detect Currency
        $currency = $this->detectCurrency($cleanLines, $user);

        // 5. Detect Tax
        $tax = $this->detectTax($cleanLines);

        // 6. Detect Payment Method
        $paymentMethod = $this->detectPaymentMethod($cleanLines);

        // 7. Detect Category Hint
        $categoryHint = $this->guessCategoryHint($merchant, $cleanLines);

        // 8. Extract item lines
        $items = $this->extractItems($cleanLines);

        return [
            'merchant_name'    => $merchant,
            'transaction_date' => $date ?: now()->toDateString(),
            'amount'           => $amount > 0 ? $amount : 0.00,
            'currency'         => $currency,
            'category_hint'    => $categoryHint,
            'payment_method'   => $paymentMethod,
            'tax'              => $tax,
            'items'            => $items,
            'confidence'       => $amount > 0 ? 85 : 50,
        ];
    }

    /**
     * Match detected details against user's actual database categories and wallets.
     */
    protected function enrichWithUserData(array $data, User $user, string $engine): array
    {
        // 1. Match Category
        $categoryId = null;
        $categoryName = null;
        $hint = strtolower($data['category_hint'] ?? '');

        $categories = $user->categories()->get();

        foreach ($categories as $cat) {
            $catLower = strtolower($cat->name);
            if (
                str_contains($catLower, $hint) ||
                str_contains($hint, $catLower) ||
                ($hint === 'groceries' && (str_contains($catLower, 'belanja') || str_contains($catLower, 'grocer'))) ||
                ($hint === 'food & dining' && (str_contains($catLower, 'makan') || str_contains($catLower, 'food') || str_contains($catLower, 'restoran'))) ||
                ($hint === 'transportation' && (str_contains($catLower, 'trans') || str_contains($catLower, 'bensin') || str_contains($catLower, 'fuel'))) ||
                ($hint === 'utilities' && (str_contains($catLower, 'tagihan') || str_contains($catLower, 'util')))
            ) {
                $categoryId = $cat->id;
                $categoryName = $cat->name;
                break;
            }
        }

        if (!$categoryId && $categories->isNotEmpty()) {
            $firstExpense = $categories->firstWhere('type', 'expense');
            if ($firstExpense) {
                $categoryId = $firstExpense->id;
                $categoryName = $firstExpense->name;
            }
        }

        // 2. Match Wallet
        $walletId = null;
        $walletName = null;
        $pmHint = strtolower($data['payment_method'] ?? '');
        $wallets = $user->wallets()->get();

        foreach ($wallets as $w) {
            $wLower = strtolower($w->name);
            if (
                (!empty($pmHint) && (str_contains($wLower, $pmHint) || str_contains($pmHint, $wLower))) ||
                (str_contains($pmHint, 'bca') && str_contains($wLower, 'bca')) ||
                (str_contains($pmHint, 'mandiri') && str_contains($wLower, 'mandiri')) ||
                (str_contains($pmHint, 'gopay') && str_contains($wLower, 'gopay')) ||
                (str_contains($pmHint, 'chase') && str_contains($wLower, 'chase')) ||
                (str_contains($pmHint, 'revolut') && str_contains($wLower, 'revolut')) ||
                (str_contains($pmHint, 'cash') && $w->type === 'cash')
            ) {
                $walletId = $w->id;
                $walletName = $w->name;
                break;
            }
        }

        if (!$walletId && $wallets->isNotEmpty()) {
            $primary = $wallets->firstWhere('is_primary', true) ?? $wallets->first();
            if ($primary) {
                $walletId = $primary->id;
                $walletName = $primary->name;
            }
        }

        // 3. Compose notes summary with item breakdown
        $notesParts = [];
        if (!empty($data['items'])) {
            $itemList = [];
            foreach (array_slice($data['items'], 0, 5) as $it) {
                $qty = !empty($it['qty']) ? "{$it['qty']}x " : '';
                $price = !empty($it['price']) ? " (" . number_format($it['price'], 0, ',', '.') . ")" : '';
                $itemList[] = "{$qty}{$it['name']}{$price}";
            }
            $notesParts[] = "Items: " . implode(', ', $itemList);
        }

        if (!empty($data['tax']) && $data['tax'] > 0) {
            $notesParts[] = "Tax/PPN: " . number_format($data['tax'], 0, ',', '.');
        }

        $notesParts[] = "[Scanned by Finora AI]";
        $notes = implode(' | ', $notesParts);

        return [
            'merchant_name'    => $data['merchant_name'],
            'transaction_date' => $data['transaction_date'],
            'amount'           => $data['amount'],
            'currency'         => $data['currency'] ?? $user->currency ?? 'IDR',
            'category_id'      => $categoryId,
            'category_name'    => $categoryName,
            'wallet_id'        => $walletId,
            'wallet_name'      => $walletName,
            'payment_method'   => $data['payment_method'] ?? 'cash',
            'tax'              => $data['tax'] ?? null,
            'items'            => $data['items'] ?? [],
            'notes'            => $notes,
            'confidence'       => $data['confidence'] ?? 80,
            'engine'           => $engine,
        ];
    }

    /**
     * Detect merchant name from text.
     */
    protected function detectMerchant(array $lines): string
    {
        $knownMerchants = [
            'Starbucks', 'McDonald\'s', 'KFC', 'Burger King', 'Pizza Hut', 'Subway', 'HokBen',
            'J.CO Donuts', 'Chatime', 'Fore Coffee', 'Kopi Kenangan', 'Tomoro Coffee', 'Point Coffee',
            'Indomaret', 'Alfamart', 'Alfamidi', 'Superindo', 'Hypermart', 'Hero Supermarket',
            'Transmart', 'Carrefour', 'Lawson', 'FamilyMart', 'Circle K', '7-Eleven', 'Trader Joe\'s',
            'Whole Foods', 'Target', 'Walmart', 'Best Buy', 'Costco', 'IKEA', 'Ace Hardware',
            'Pertamina', 'Shell', 'BP Gas', 'Total Energies', 'Grab', 'Gojek', 'Uber', 'Blue Bird',
            'Apple Store', 'iBox', 'Digimap', 'Erafone', 'Samsung', 'Xiaomi', 'Tokopedia', 'Shopee',
            'Guardian', 'Watsons', 'Kimia Farma', 'Century Healthcare', 'XXI Cinema', 'CGV',
            'Netflix', 'Spotify', 'Amazon', 'Uniqlo', 'H&M', 'Zara'
        ];

        // Search known merchants in top 10 lines
        $joined = implode(' ', array_slice($lines, 0, 10));
        foreach ($knownMerchants as $m) {
            if (stripos($joined, $m) !== false) {
                return $m;
            }
        }

        // Otherwise pick the first non-numeric, non-header line with letters
        foreach ($lines as $line) {
            $l = trim($line);
            if (strlen($l) >= 3 && !preg_match('/^(no|nota|struk|receipt|tanggal|date|telp|phone|\d+)/i', $l) && preg_match('/[a-zA-Z]{3,}/', $l)) {
                return ucwords(strtolower(substr($l, 0, 40)));
            }
        }

        return 'Receipt Purchase';
    }

    /**
     * Detect receipt date.
     */
    protected function detectDate(array $lines): ?string
    {
        $text = implode("\n", $lines);

        // Pattern 1: YYYY-MM-DD or YYYY/MM/DD
        if (preg_match('/\b(202\d)[-\/.](0?[1-9]|1[0-2])[-\/.](0?[1-9]|[12]\d|3[01])\b/', $text, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }

        // Pattern 2: DD-MM-YYYY or DD/MM/YYYY
        if (preg_match('/\b(0?[1-9]|[12]\d|3[01])[-\/.](0?[1-9]|1[0-2])[-\/.](202\d)\b/', $text, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        // Pattern 3: DD Mon YYYY (e.g. 07 Nov 2026)
        if (preg_match('/\b(0?[1-9]|[12]\d|3[01])\s+([A-Za-z]{3,9})\s+(202\d)\b/', $text, $m)) {
            try {
                return Carbon::parse("{$m[1]} {$m[2]} {$m[3]}")->toDateString();
            } catch (\Throwable $e) {}
        }

        return null;
    }

    /**
     * Detect total amount paid.
     */
    protected function detectAmount(array $lines): float
    {
        $totalPatterns = [
            '/(?<!sub)(?:\bgrand\s+total\b|\btotal\s+bayar\b|\btotal\s+harga\b|\btotal\s+amount\b|\bnet\s+total\b|\btagihan\b|\bbalance\s+due\b|\bamount\s+due\b|\btotal\b)\s*[:=]?\s*(?:Rp\.?|\$|€|£)?\s*([0-9.,]+)/i',
            '/(?:\bsubtotal\b|\bsub\s+total\b|\bjumlah\b)\s*[:=]?\s*(?:Rp\.?|\$|€|£)?\s*([0-9.,]+)/i',
        ];

        // Search lines in reverse (totals are located towards the bottom)
        $reversedLines = array_reverse($lines);

        foreach ($totalPatterns as $pattern) {
            foreach ($reversedLines as $line) {
                if (preg_match($pattern, $line, $matches)) {
                    $val = $this->cleanNumber($matches[1]);
                    if ($val > 0) {
                        return $val;
                    }
                }
            }
        }

        // Fallback: look for lines containing "Total" with number anywhere
        foreach ($lines as $line) {
            if (preg_match('/total/i', $line)) {
                if (preg_match_all('/([0-9]{1,3}(?:[.,][0-9]{3})*(?:[.,][0-9]{2})?|[0-9]+)/', $line, $m)) {
                    foreach (array_reverse($m[0]) as $cand) {
                        $val = $this->cleanNumber($cand);
                        if ($val > 0) {
                            return $val;
                        }
                    }
                }
            }
        }

        // Fallback 2: largest number found before payment/change lines
        $maxNum = 0.0;
        foreach ($lines as $line) {
            if (preg_match('/(kembali|change|kembalian|cash|tunai|debit|card)/i', $line)) {
                continue;
            }
            if (preg_match_all('/([0-9]{1,3}(?:[.,][0-9]{3})+(?:[.,][0-9]{2})?|[0-9]{3,})/', $line, $m)) {
                foreach ($m[0] as $cand) {
                    $val = $this->cleanNumber($cand);
                    if ($val > $maxNum && $val < 100000000) {
                        $maxNum = $val;
                    }
                }
            }
        }

        return $maxNum;
    }

    /**
     * Detect currency used on the receipt.
     */
    protected function detectCurrency(array $lines, User $user): string
    {
        $text = implode(' ', $lines);

        if (preg_match('/\b(USD|\$)\b/', $text)) return 'USD';
        if (preg_match('/\b(EUR|€)\b/', $text)) return 'EUR';
        if (preg_match('/\b(GBP|£)\b/', $text)) return 'GBP';
        if (preg_match('/\b(SGD|S\$)\b/', $text)) return 'SGD';
        if (preg_match('/\b(AUD|A\$)\b/', $text)) return 'AUD';
        if (preg_match('/\b(JPY|¥)\b/', $text)) return 'JPY';
        if (preg_match('/\b(IDR|Rp)\b/i', $text)) return 'IDR';

        return strtoupper($user->currency ?? 'IDR');
    }

    /**
     * Detect tax / PPN amount.
     */
    protected function detectTax(array $lines): ?float
    {
        foreach ($lines as $line) {
            if (preg_match('/(?:ppn|pb1|tax|vat)(?:\s*[0-9]{1,2}\s*%)?\s*[:=]?\s*(?:Rp\.?|\$)?\s*([0-9.,]+)/i', $line, $m)) {
                $val = $this->cleanNumber($m[1]);
                if ($val > 0) {
                    return $val;
                }
            }
        }
        return null;
    }

    /**
     * Detect payment method (Cash, Debit, QRIS, etc.).
     */
    protected function detectPaymentMethod(array $lines): string
    {
        $text = implode(' ', $lines);

        if (stripos($text, 'qris') !== false) return 'qris';
        if (stripos($text, 'bca') !== false) return 'bank_bca';
        if (stripos($text, 'mandiri') !== false) return 'bank_mandiri';
        if (stripos($text, 'gopay') !== false) return 'gopay';
        if (stripos($text, 'ovo') !== false) return 'ovo';
        if (stripos($text, 'dana') !== false) return 'dana';
        if (stripos($text, 'debit') !== false) return 'debit';
        if (stripos($text, 'credit') !== false || stripos($text, 'visa') !== false || stripos($text, 'mastercard') !== false) return 'credit_card';

        return 'cash';
    }

    /**
     * Guess category hint.
     */
    protected function guessCategoryHint(string $merchant, array $lines): string
    {
        $mLower = strtolower($merchant);
        $text = strtolower(implode(' ', $lines));

        if (
            str_contains($mLower, 'starbucks') || str_contains($mLower, 'kopi') || str_contains($mLower, 'coffee') ||
            str_contains($mLower, 'mcdonald') || str_contains($mLower, 'kfc') || str_contains($mLower, 'burger') ||
            str_contains($mLower, 'pizza') || str_contains($mLower, 'cafe') || str_contains($mLower, 'resto') ||
            str_contains($mLower, 'bakery') || str_contains($text, 'makanan') || str_contains($text, 'minuman') ||
            str_contains($text, 'latte') || str_contains($text, 'ayam') || str_contains($text, 'nasi')
        ) {
            return 'Food & Dining';
        }

        if (
            str_contains($mLower, 'indomaret') || str_contains($mLower, 'alfamart') || str_contains($mLower, 'superindo') ||
            str_contains($mLower, 'hypermart') || str_contains($mLower, 'trader joe') || str_contains($mLower, 'whole foods') ||
            str_contains($mLower, 'carrefour') || str_contains($mLower, 'market') || str_contains($mLower, 'grocery')
        ) {
            return 'Groceries';
        }

        if (
            str_contains($mLower, 'pertamina') || str_contains($mLower, 'shell') || str_contains($mLower, 'bp') ||
            str_contains($mLower, 'grab') || str_contains($mLower, 'gojek') || str_contains($mLower, 'uber') ||
            str_contains($text, 'bensin') || str_contains($text, 'fuel') || str_contains($text, 'pertamax') ||
            str_contains($text, 'solar') || str_contains($text, 'parkir') || str_contains($text, 'toll')
        ) {
            return 'Transportation';
        }

        if (
            str_contains($mLower, 'guardian') || str_contains($mLower, 'watsons') || str_contains($mLower, 'kimia farma') ||
            str_contains($mLower, 'apotek') || str_contains($mLower, 'pharmacy') || str_contains($mLower, 'hospital')
        ) {
            return 'Healthcare';
        }

        if (str_contains($mLower, 'cinema') || str_contains($mLower, 'xxi') || str_contains($mLower, 'cgv')) {
            return 'Entertainment';
        }

        return 'Shopping';
    }

    /**
     * Extract item descriptions and prices from lines.
     */
    protected function extractItems(array $lines): array
    {
        $items = [];

        foreach ($lines as $line) {
            // Match "1x Caffe Latte 48.000" or "Latte Grande 48,000"
            if (preg_match('/^(?:([0-9]+)\s*[xX]\s+)?([A-Za-z0-9\s\/\-\.&]{3,35})\s+([0-9.,]{3,12})$/', $line, $m)) {
                $name = trim($m[2]);
                if (!preg_match('/^(total|subtotal|tunai|cash|kembali|ppn|tax|diskon|discount)/i', $name)) {
                    $qty = !empty($m[1]) ? (int) $m[1] : 1;
                    $price = $this->cleanNumber($m[3]);
                    if ($price > 0 && $price < 50000000) {
                        $items[] = [
                            'name'  => ucwords(strtolower($name)),
                            'qty'   => $qty,
                            'price' => $price,
                        ];
                    }
                }
            }
        }

        return array_slice($items, 0, 8);
    }

    /**
     * Clean and parse currency string to standard float.
     */
    protected function cleanNumber(string $val): float
    {
        $val = trim($val);
        $val = preg_replace('/[^\d.,]/', '', $val);

        if (empty($val)) {
            return 0.0;
        }

        // If contains dot and comma (e.g. 1.500,00 or 1,500.00)
        if (str_contains($val, '.') && str_contains($val, ',')) {
            if (strrpos($val, ',') > strrpos($val, '.')) {
                // Indonesian: 1.500,00 -> 1500.00
                $val = str_replace('.', '', $val);
                $val = str_replace(',', '.', $val);
            } else {
                // US: 1,500.00 -> 1500.00
                $val = str_replace(',', '', $val);
            }
        } elseif (str_contains($val, ',')) {
            // Check if comma is decimal (2 digits after) or thousand separator (3 digits)
            $parts = explode(',', $val);
            if (count($parts) === 2 && strlen($parts[1]) === 2) {
                $val = str_replace(',', '.', $val);
            } else {
                $val = str_replace(',', '', $val);
            }
        } elseif (str_contains($val, '.')) {
            // Check if dot is thousand separator (e.g. 50.000)
            $parts = explode('.', $val);
            if (count($parts) > 1 && strlen(end($parts)) === 3) {
                $val = str_replace('.', '', $val);
            }
        }

        return (float) $val;
    }
}
