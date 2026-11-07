<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TransactionImportController extends Controller
{
    /**
     * Show import form and statement parser.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        Wallet::seedDefaultWalletsForUser($user);

        $wallets = $user->wallets()->orderBy('is_primary', 'desc')->get();
        $categories = $user->categories()->orderBy('name')->get();

        return view('transactions.import', compact('wallets', 'categories'));
    }

    /**
     * Parse uploaded CSV file or pasted statement text.
     */
    public function parse(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        $request->validate([
            'csv_file'   => ['nullable', 'file', 'mimes:csv,txt', 'max:5120'],
            'paste_text' => ['nullable', 'string', 'max:100000'],
            'wallet_id'  => ['nullable', 'exists:wallets,id'],
        ]);

        $rawContent = '';

        if ($request->hasFile('csv_file')) {
            $rawContent = file_get_contents($request->file('csv_file')->getRealPath());
        } elseif ($request->filled('paste_text')) {
            $rawContent = $request->input('paste_text');
        } else {
            return back()->withErrors(['csv_file' => 'Silakan unggah file CSV atau tempel teks mutasi bank.']);
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($rawContent));
        if (empty($lines)) {
            return back()->withErrors(['csv_file' => 'File atau teks mutasi kosong.']);
        }

        $categories = $user->categories()->get();
        $wallets = $user->wallets()->get();
        $selectedWalletId = $request->input('wallet_id');
        if (!$selectedWalletId) {
            $primary = $wallets->where('is_primary', true)->first();
            $selectedWalletId = $primary ? $primary->id : ($wallets->first()?->id);
        }

        $parsedRows = [];
        $headerSkipped = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) continue;

            // Skip common metadata headers from bank exports
            if (Str::startsWith(Str::lower($trimmed), ['periode', 'rekening', 'nomor rekening', 'mata uang', 'saldo awal', 'saldo akhir', 'informasi'])) {
                continue;
            }

            // Determine delimiter: comma, semicolon, or tab
            $delimiter = ',';
            if (substr_count($trimmed, ';') > substr_count($trimmed, ',')) {
                $delimiter = ';';
            } elseif (substr_count($trimmed, "\t") > substr_count($trimmed, ',')) {
                $delimiter = "\t";
            }

            $cols = str_getcsv($trimmed, $delimiter);
            $cols = array_map('trim', $cols);

            // Skip table header row
            $firstColLower = Str::lower($cols[0] ?? '');
            if (!$headerSkipped && (
                Str::contains($firstColLower, ['tanggal', 'date', 'tgl', 'waktu']) ||
                Str::contains(Str::lower($cols[1] ?? ''), ['keterangan', 'description', 'transaksi', 'uraian'])
            )) {
                $headerSkipped = true;
                continue;
            }

            // Attempt to parse row into transaction
            $parsed = $this->interpretRow($cols, $categories, $user);
            if ($parsed) {
                $parsedRows[] = $parsed;
            }
        }

        if (empty($parsedRows)) {
            return back()->withErrors(['csv_file' => 'Tidak dapat menemukan baris mutasi transaksi yang valid. Pastikan format file sesuai panduan.']);
        }

        return view('transactions.import-preview', [
            'rows'             => $parsedRows,
            'wallets'          => $wallets,
            'categories'       => $categories,
            'selectedWalletId' => $selectedWalletId,
        ]);
    }

    /**
     * Confirm and save selected parsed transactions into database.
     */
    public function confirm(Request $request): RedirectResponse
    {
        $user = $request->user();

        $transactionsData = $request->input('transactions', []);
        $selectedWalletId = $request->input('wallet_id');

        if (empty($transactionsData)) {
            return redirect()->route('transactions.import')->withErrors(['error' => 'Tidak ada transaksi yang dipilih untuk diimpor.']);
        }

        $savedCount = 0;
        $wallet = $user->wallets()->find($selectedWalletId);
        $walletName = $wallet ? $wallet->name : 'Imported';

        foreach ($transactionsData as $data) {
            // Only process checked rows
            if (empty($data['selected'])) continue;

            $amount = (float) ($data['amount'] ?? 0);
            if ($amount <= 0) continue;

            $title = trim($data['title'] ?? 'Transaksi Impor');
            $type = in_array($data['type'] ?? '', ['income', 'expense', 'transfer']) ? $data['type'] : 'expense';
            $categoryId = !empty($data['category_id']) ? $data['category_id'] : null;
            $date = !empty($data['date']) ? Carbon::parse($data['date'])->toDateString() : Carbon::now()->toDateString();
            $notes = trim($data['notes'] ?? 'Diimpor otomatis via Smart CSV Parser');

            $user->transactions()->create([
                'wallet_id'        => $selectedWalletId,
                'category_id'      => $categoryId,
                'amount'           => $amount,
                'type'             => $type,
                'payment_method'   => $walletName,
                'transaction_date' => $date,
                'title'            => $title,
                'notes'            => $notes,
            ]);

            $savedCount++;
        }

        return redirect()->route('transactions.index')
            ->with('status', "Berhasil mengimpor {$savedCount} transaksi secara otomatis ke Finora!");
    }

    /**
     * Download sample CSV template.
     */
    public function downloadTemplate(): Response
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="finora_sample_mutasi.csv"',
        ];

        $content = "Tanggal,Keterangan,Tipe,Nominal,Kategori,Catatan\n" .
                   "05/11/2026,QRIS INDOMARET TGR,EXPENSE,65000,Food & Dining,Belanja harian\n" .
                   "04/11/2026,SPBU PERTAMINA KEBAYORAN,EXPENSE,150000,Transportation,Isi bensin pertamax\n" .
                   "01/11/2026,TRANSFER GAJI PAYROLL,INCOME,8500000,Salary / Inflow,Gaji bulanan\n" .
                   "30/10/2026,NETFLIX JAKARTA ID,EXPENSE,186000,Entertainment,Langganan streaming bulanan\n" .
                   "28/10/2026,PLN POSTPAID TOKOPEDIA,EXPENSE,350000,Bills & Utilities,Tagihan listrik rumah\n";

        return response($content, 200, $headers);
    }

    /**
     * Interpret a single CSV row into a structured transaction candidate.
     */
    protected function interpretRow(array $cols, $categories, $user): ?array
    {
        if (count($cols) < 2) return null;

        $dateStr = null;
        $title = null;
        $amount = 0;
        $type = 'expense';
        $notes = '';

        // Case A: Finora Standard / Generic (Tanggal, Keterangan, Tipe, Nominal, Kategori, Catatan)
        // Check if date in col 0
        $parsedDate = $this->parseDate($cols[0]);
        if ($parsedDate) {
            $dateStr = $parsedDate->toDateString();
            $title = $cols[1] ?? 'Transaksi';

            // Find amount and type
            if (isset($cols[3]) && is_numeric($this->cleanNumber($cols[3]))) {
                // Col 2 might be Type (EXPENSE/INCOME/DB/CR/Sale/Payment/Credit/Debit)
                $typeCandidate = Str::upper($cols[2] ?? '');
                if (in_array($typeCandidate, ['INCOME', 'CR', 'KREDIT', 'CREDIT', 'DEPOSIT', 'PAYMENT', 'C'])) {
                    $type = 'income';
                } else {
                    $type = 'expense';
                }
                $isNeg = false;
                $amount = $this->cleanNumber($cols[3], $isNeg);
                if ($isNeg) $type = 'expense';
                $notes = $cols[5] ?? '';
            } elseif (isset($cols[2]) && is_numeric($this->cleanNumber($cols[2]))) {
                $isNeg = false;
                $amount = $this->cleanNumber($cols[2], $isNeg);
                if ($isNeg) {
                    $type = 'expense';
                }
            }
        }

        // Case B: Bank BCA / International Format with explicit DB/CR flag (Date, Description, Branch, Amount, Type/CR/DB, Balance)
        if (count($cols) >= 5 && in_array(Str::upper($cols[4] ?? ''), ['DB', 'CR', 'DEBIT', 'CREDIT'])) {
            $type = in_array(Str::upper($cols[4]), ['CR', 'CREDIT']) ? 'income' : 'expense';
            $amount = $this->cleanNumber($cols[3]);
        }

        // Case C: Mandiri / Dual Column (Date, Description, Debit/Expense, Credit/Income)
        if (count($cols) >= 4) {
            $colDebit = $this->cleanNumber($cols[2] ?? '');
            $colCredit = $this->cleanNumber($cols[3] ?? '');

            if ($colDebit > 0 && $colCredit == 0) {
                $type = 'expense';
                $amount = $colDebit;
            } elseif ($colCredit > 0 && $colDebit == 0) {
                $type = 'income';
                $amount = $colCredit;
            }
        }

        // Case D: Chase / US Statement Format (Details, Posting Date, Description, Amount, Type, Balance)
        if (count($cols) >= 4 && !$dateStr) {
            $dateCandidate = $this->parseDate($cols[1] ?? '');
            if ($dateCandidate) {
                $dateStr = $dateCandidate->toDateString();
                $title = $cols[2] ?? ($cols[0] ?? 'Transaction');
                $isNeg = false;
                $val = $this->cleanNumber($cols[3] ?? '', $isNeg);
                if ($val > 0) {
                    $amount = $val;
                    $type = $isNeg ? 'expense' : 'income';
                }
            }
        }

        if (!$dateStr || $amount <= 0) {
            return null;
        }

        // Format clean title
        $cleanTitle = Str::title(Str::limit(preg_replace('/\s+/', ' ', trim($title)), 80));

        // Auto categorize based on keywords in title & notes
        $matchedCategory = $this->guessCategory($title . ' ' . $notes, $type, $categories);

        // Duplicate check
        $isDuplicate = $user->transactions()
            ->whereDate('transaction_date', $dateStr)
            ->where('amount', $amount)
            ->exists();

        return [
            'date'         => $dateStr,
            'title'        => $cleanTitle,
            'amount'       => $amount,
            'type'         => $type,
            'category_id'  => $matchedCategory ? $matchedCategory->id : null,
            'category_name'=> $matchedCategory ? $matchedCategory->name : 'Uncategorized',
            'notes'        => $notes,
            'is_duplicate' => $isDuplicate,
        ];
    }

    /**
     * Clean messy currency string into float, tracking negative indicators.
     */
    protected function cleanNumber(?string $val, bool &$isNegative = false): float
    {
        if (empty($val)) return 0;

        $str = trim($val);
        $isNegative = str_starts_with($str, '-') || (str_starts_with($str, '(') && str_ends_with($str, ')'));

        // Remove currency symbols, parentheses, whitespace
        $str = preg_replace('/[Rp$€£¥A\(\)\s]/i', '', $str);
        $str = ltrim($str, '-+');

        // Format: Distinguish 1,250.50 (US/UK/International) vs 1.250,50 (European/Indonesian)
        if (str_contains($str, ',') && str_contains($str, '.')) {
            if (strrpos($str, '.') > strrpos($str, ',')) {
                $str = str_replace(',', '', $str);
            } else {
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            }
        } elseif (str_contains($str, ',')) {
            $parts = explode(',', $str);
            if (count($parts) === 2 && strlen($parts[1]) !== 3) {
                $str = str_replace(',', '.', $str);
            } else {
                $str = str_replace(',', '', $str);
            }
        }

        return (float) filter_var($str, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Parse multiple date formats into Carbon instance.
     */
    protected function parseDate(?string $str): ?Carbon
    {
        if (empty($str)) return null;
        $str = trim($str);

        $formats = [
            'd/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y', 'd-m-y',
            'd M Y', 'd F Y', 'Y/m/d', 'm/d/Y'
        ];

        foreach ($formats as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $str);
                if ($d && $d->year > 2000 && $d->year < 2050) {
                    return $d;
                }
            } catch (\Exception $e) {
                // Try next
            }
        }

        try {
            return Carbon::parse($str);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Smart rules for auto-categorization based on Indonesian merchant keywords.
     */
    protected function guessCategory(string $text, string $type, $categories): ?Category
    {
        $lower = Str::lower($text);

        if ($type === 'income') {
            return $categories->first(function ($c) use ($lower) {
                return $c->type === 'income' && (
                    Str::contains($lower, ['gaji', 'salary', 'payroll', 'upah', 'fee', 'bonus', 'dividen']) ||
                    Str::contains(Str::lower($c->name), ['salary', 'income', 'inflow', 'pemasukan'])
                );
            }) ?? $categories->where('type', 'income')->first();
        }

        // Expense keyword mapping (Indonesian & Global)
        $keywordMap = [
            'Food & Dining' => [
                'indomaret', 'alfamart', 'kopi', 'starbucks', 'mcdonalds', 'mcd', 'kfc', 'hokben', 'gofood', 'grabfood', 'shopeefood', 'restoran', 'cafe', 'warung', 'bakso', 'breadtalk', 'roti', 'superindo', 'hypermart', 'dendeng', 'padang',
                'uber eats', 'doordash', 'deliveroo', 'grocery', 'trader joe', 'whole foods', 'supermarket', 'restaurant', 'coffee', 'chipotle', 'subway', 'burger king', 'domino', 'walmart grocery', 'tesco', 'sainsbury', 'lidl', 'aldi', 'carrefour'
            ],
            'Transportation' => [
                'pertamina', 'spbu', 'shell', 'gojek', 'goride', 'gocar', 'grab', 'mrt', 'krl', 'commuter', 'kai', 'kereta', 'tiket', 'toll', 'etoll', 'parkir', 'bensin', 'bluebird',
                'uber', 'lyft', 'chevron', 'bp', 'texaco', 'exxon', 'transit', 'subway', 'metro', 'train', 'flight', 'airline', 'delta', 'united', 'american air', 'gas station', 'parking', 'transport for london', 'oyster'
            ],
            'Bills & Utilities' => [
                'pln', 'listrik', 'pdam', 'air', 'indihome', 'telkom', 'wifi', 'bpjs', 'pajak', 'pulsa', 'kuota', 'telkomsel', 'xl', 'indosat', 'iuran', 'ipl',
                'verizon', 'at&t', 't-mobile', 'vodafone', 'electric', 'water bill', 'utility', 'broadband', 'council tax', 'insurance', 'geico', 'state farm'
            ],
            'Entertainment' => [
                'netflix', 'spotify', 'youtube', 'bioskop', 'xxi', 'cgv', 'cinema', 'steam', 'playstation', 'game', 'nintendo',
                'hulu', 'disney', 'amazon prime', 'hbo', 'apple tv', 'amc', 'regal', 'xbox', 'twitch', 'audible', 'patreon'
            ],
            'Shopping & Lifestyle' => [
                'shopee', 'tokopedia', 'lazada', 'blibli', 'tiktok shop', 'uniqlo', 'zara', 'h&m', 'watsons', 'guardian', 'matahari', 'pakaian', 'baju', 'sepatu',
                'amazon', 'ebay', 'target', 'walmart', 'best buy', 'apple store', 'nike', 'adidas', 'ikea', 'costco', 'sephora', 'asos'
            ],
        ];

        foreach ($keywordMap as $catTarget => $keywords) {
            foreach ($keywords as $kw) {
                if (Str::contains($lower, $kw)) {
                    $found = $categories->first(function ($c) use ($catTarget) {
                        return Str::contains(Str::lower($c->name), Str::lower($catTarget)) ||
                               Str::contains(Str::lower($catTarget), Str::lower($c->name));
                    });
                    if ($found) return $found;
                }
            }
        }

        // Fallback to first expense category
        return $categories->where('type', 'expense')->first();
    }
}
