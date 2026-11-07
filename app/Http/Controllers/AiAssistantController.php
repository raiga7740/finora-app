<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AiAssistantController extends Controller
{
    private const OUT_OF_CONTEXT_REPLY = 'Maaf, saya adalah Finora AI Financial Advisor yang khusus dirancang untuk membantu pengelolaan keuangan, anggaran (budget), tabungan, dan analisis transaksi Anda. Saya tidak dapat merespons pertanyaan di luar konteks keuangan.';
    private const OUT_OF_CONTEXT_REPLY_EN = 'I am Finora AI Financial Advisor, designed specifically to assist you with personal money management, budgeting, savings, and financial analysis. I am unable to respond to topics outside of personal finance.';

    public function chat(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string|max:1000',
                'history' => 'nullable|array|max:10',
                'history.*.role' => 'required_with:history|string|in:user,assistant',
                'history.*.content' => 'required_with:history|string|max:2000',
            ]);

            $question = trim($validated['message']);
            $user = $request->user();
            $isEnglish = $this->isEnglishContext($question, $user);

            // 1. Strict Domain Guardrail: Reject out-of-context topics immediately
            if ($this->isOutOfFinancialContext($question)) {
                return response()->json([
                    'reply' => $isEnglish ? self::OUT_OF_CONTEXT_REPLY_EN : self::OUT_OF_CONTEXT_REPLY,
                    'mode' => 'guardrail_rejected',
                ]);
            }
            $now = Carbon::now();
            $startOfMonth = $now->copy()->startOfMonth();
            $endOfMonth = $now->copy()->endOfMonth();

            // Gather user's all-time and monthly financial data
            $totalIncomeAll = (float) $user->transactions()->where('type', 'income')->sum('amount');
            $totalExpenseAll = (float) $user->transactions()->where('type', 'expense')->sum('amount');
            $netBalance = $totalIncomeAll - $totalExpenseAll;

            $monthlyIncome = (float) $user->transactions()
                ->where('type', 'income')
                ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
                ->sum('amount');

            $monthlyExpense = (float) $user->transactions()
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
                ->sum('amount');

            $activeIncome = ($monthlyIncome > 0 || $monthlyExpense > 0) ? $monthlyIncome : $totalIncomeAll;
            $activeExpense = ($monthlyIncome > 0 || $monthlyExpense > 0) ? $monthlyExpense : $totalExpenseAll;
            $activePeriodLabel = ($monthlyIncome > 0 || $monthlyExpense > 0)
                ? "Bulan Ini ({$now->format('F Y')})"
                : 'Keseluruhan (All-Time)';

            $netSavings = $activeIncome - $activeExpense;
            $savingsRate = $activeIncome > 0 ? round(($netSavings / $activeIncome) * 100, 1) : 0;

            // Top spending categories
            $expenseQuery = $user->transactions()->with('category')->where('type', 'expense');
            if ($monthlyExpense > 0) {
                $expenseQuery->whereBetween('transaction_date', [$startOfMonth, $endOfMonth]);
            }

            $topCategories = $expenseQuery->get()
                ->groupBy('category_id')
                ->map(function ($items) {
                    return [
                        'name' => $items->first()->category->name ?? 'Uncategorized',
                        'total' => (float) $items->sum('amount'),
                    ];
                })
                ->sortByDesc('total')
                ->take(5)
                ->values();

            $topCatText = $topCategories->isNotEmpty()
                ? $topCategories->map(fn ($c) => "{$c['name']} (" . $user->formatCurrency($c['total']) . ")")->implode(', ')
                : 'Belum ada pengeluaran tercatat';

            // Budgets status
            $budgets = $user->budgets()->with('category')->get();
            $budgetText = $budgets->isNotEmpty()
                ? $budgets->map(function ($b) use ($user) {
                    $pct = round($b->progress_percentage);
                    $status = $pct >= 90 ? ' [HAMPIR HABIS/OVER]' : ($pct >= 75 ? ' [WASPADA]' : ' [AMAN]');
                    return "{$b->name}: Terpakai " . $user->formatCurrency($b->spent) . " dari " . $user->formatCurrency($b->amount) . " ({$pct}%){$status}";
                })->implode('; ')
                : 'Belum ada batas budget yang diatur';

            // Savings Goals status
            $goals = $user->savingsGoals()->get();
            $goalsText = $goals->isNotEmpty()
                ? $goals->map(function ($g) use ($user) {
                    $pct = round($g->progress_percentage);
                    return "{$g->name}: Terkumpul " . $user->formatCurrency($g->current_amount) . " dari target " . $user->formatCurrency($g->target_amount) . " ({$pct}%, kurang " . $user->formatCurrency($g->remaining) . ")";
                })->implode('; ')
                : 'Belum ada target tabungan';

            // Recent 8 transactions
            $recentTx = $user->transactions()
                ->with('category')
                ->orderBy('transaction_date', 'desc')
                ->limit(8)
                ->get();

            $recentTxText = $recentTx->isNotEmpty()
                ? $recentTx->map(function ($t) use ($user) {
                    $sign = $t->type === 'income' ? '+' : '-';
                    $date = $t->transaction_date ? $user->formatUserDate($t->transaction_date) : '';
                    $cat = $t->category->name ?? 'General';
                    return "{$t->title} [{$cat}] ({$sign}" . $user->formatCurrency($t->amount) . ", {$date})";
                })->implode('; ')
                : 'Belum ada transaksi';

            $apiKey = trim((string) config('services.ai.key'));
            $baseUrl = rtrim((string) config('services.ai.base_url', 'https://api.groq.com/openai/v1'), '/');
            $configuredModel = trim((string) config('services.ai.model', 'llama-3.1-8b-instant'));

            // If API Key is configured, try Cloud LLM with fast timeout
            if (!empty($apiKey)) {
                $cloudReply = $this->tryCloudAiCompletion(
                    $apiKey,
                    $baseUrl,
                    $configuredModel,
                    $user,
                    $netBalance,
                    $activeIncome,
                    $activeExpense,
                    $activePeriodLabel,
                    $netSavings,
                    $savingsRate,
                    $topCatText,
                    $budgetText,
                    $goalsText,
                    $recentTxText,
                    $validated['history'] ?? [],
                    $question
                );

                if ($cloudReply !== null) {
                    return response()->json([
                        'reply' => $cloudReply,
                        'mode' => 'cloud_ai',
                    ]);
                }
            }

            // Intelligent Finora Financial Advisor Engine (Guaranteed instant response)
            $smartReply = $this->generateSmartFinancialResponse(
                $question,
                $user,
                $netBalance,
                $activeIncome,
                $activeExpense,
                $activePeriodLabel,
                $netSavings,
                $savingsRate,
                $topCatText,
                $budgetText,
                $goalsText,
                $recentTxText
            );

            return response()->json([
                'reply' => $smartReply,
                'mode' => 'local_intelligence',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('AI Assistant Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            $isEn = isset($user) && ($user->locale === 'en' || (isset($question) && $this->isEnglishContext($question, $user)));
            $fallbackMsg = $isEn
                ? 'Hello! I am Finora AI Advisor. Please feel free to ask about personal budgeting, savings goals, cash flow analysis, or your transactions.'
                : 'Halo! Saya Finora AI Advisor. Silakan tanyakan seputar cara mengelola keuangan, analisis anggaran (budget), strategi menabung, atau ringkasan transaksi Anda.';

            return response()->json([
                'reply' => $fallbackMsg,
                'mode' => 'fallback',
            ]);
        }
    }

    /**
     * Check if user is operating in English or asking in English.
     */
    private function isEnglishContext(string $question, ?\App\Models\User $user = null): bool
    {
        if ($user && $user->locale === 'en') {
            return true;
        }

        $q = mb_strtolower(trim($question));
        return (bool) preg_match('/\b(how|what|can|my|is|are|the|finance|money|saving|savings|budget|expense|expenses|income|spend|spending|investment|invest|debt|bill|bills|wallet|account|hello|hi|hey|please|balance)\b/i', $q);
    }

    /**
     * Check whether the user's message is outside the financial / Finora domain.
     */
    private function isOutOfFinancialContext(string $message): bool
    {
        $q = mb_strtolower(trim($message));

        // Check if message mentions any financial/money context alongside the topic (e.g. "budget beli kopi")
        $financeContextKeywords = [
            'uang', 'keuangan', 'finansial', 'finance', 'financial', 'saldo', 'balance', 'tabung', 'menabung',
            'saving', 'savings', 'budget', 'budgets', 'budgeting', 'anggaran', 'biaya', 'cost', 'costs',
            'harga', 'price', 'mahal', 'murah', 'cheap', 'expensive', 'hemat', 'boros', 'frugal',
            'pengeluaran', 'pemasukan', 'income', 'expense', 'expenses', 'spend', 'spending', 'gaji', 'salary', 'wage',
            'investasi', 'invest', 'investment', 'investing', 'saham', 'stocks', 'stock', 'reksadana', 'mutual fund',
            'emas', 'gold', 'deposito', 'deposit', 'obligasi', 'bond', 'bonds', 'crypto', 'kripto', 'bitcoin',
            'utang', 'hutang', 'debt', 'debts', 'cicilan', 'kredit', 'credit', 'kpr', 'mortgage', 'loan', 'loans',
            'pajak', 'tax', 'taxes', 'inflasi', 'inflation', 'dana darurat', 'emergency fund', 'emergency',
            'arus kas', 'cashflow', 'cash flow', 'transaksi', 'transaction', 'transactions', 'kategori', 'category',
            'laporan', 'report', 'target', 'goal', 'goals', 'finora', 'rupiah', 'dollar', 'usd', 'eur', 'sgd', 'jpy', 'gbp', 'aud',
            'belanja', 'beli', 'bayar', 'pay', 'payment', 'tagihan', 'bill', 'bills', 'subscription', 'alokasi', 'allocation', 'persen', 'percent',
            'kaya', 'wealth', 'net worth', 'miskin', 'aset', 'asset', 'assets', 'liabilitas', 'liability', 'modal', 'capital', 'untung', 'rugi', 'profit', 'loss',
            'bisnis', 'business', 'usaha', 'ekonomi', 'economy', 'dompet', 'wallet', 'wallets', 'rekening', 'account', 'accounts', 'bank', 'cash', 'qris',
        ];

        $hasFinanceKeyword = false;
        foreach ($financeContextKeywords as $kw) {
            if (str_contains($q, $kw)) {
                $hasFinanceKeyword = true;
                break;
            }
        }

        // Strongly out-of-context topics (programming, cooking recipes, entertainment, school subjects, etc.)
        $nonFinancePatterns = [
            // Coding / Programming / IT
            '/\b(kode\s*html|buatkan\s*kode|script|coding|pemrograman|javascript|python|php|css|laravel|react|flutter|sql|database|algoritma|source\s*code|write\s*code|html\s*code)\b/i',
            // Cooking / Food & Drink Recipes
            '/\b(cara\s*(mem)?buat\s*kopi|resep|cara\s*memasak|cara\s*masak|bumbu|bahan\s*masakan|recipe\s*for|how\s*to\s*cook)\b/i',
            // General trivia / Entertainment / Homework / Creative writing unrelated to finance
            '/\b(buatkan\s*(puisi|cerpen|pantun|lirik|cerita|lagu|essay)|write\s*a\s*poem|chord\s*gitar| kunci\s*gitar|siapa\s*presiden|who\s*is\s*the\s*president|ibukota\s*negara|rumus\s*(fisika|kimia|biologi)|terjemahkan|translate\s*ke)\b/i',
            '/\b(cara\s*main\s*game|rekomendasi\s*(film|anime|lagu|game|drakor)|ramalan\s*zodiak|arti\s*mimpi|cuaca\s*hari\s*ini|weather\s*today)\b/i',
        ];

        foreach ($nonFinancePatterns as $pattern) {
            if (preg_match($pattern, $q) && !$hasFinanceKeyword) {
                return true;
            }
        }

        // Conversational greetings & polite expressions are allowed
        $greetings = [
            'halo', 'hai', 'hi', 'hello', 'selamat pagi', 'selamat siang', 'selamat sore',
            'selamat malam', 'assalamualaikum', 'terima kasih', 'makasih', 'thanks', 'siapa kamu',
            'apa itu finora', 'bantu saya', 'tolong bantu', 'mulai', 'tes', 'test', 'oke', 'ok', 'baik',
        ];

        foreach ($greetings as $g) {
            if ($q === $g || str_starts_with($q, $g . ' ') || str_contains($q, 'finora')) {
                return false;
            }
        }

        // If the message has no finance keywords at all and asks "bagaimana cara..." / "tolong buatkan..." / "apa itu..." about non-financial things
        if (!$hasFinanceKeyword) {
            $generalQuestionTriggers = [
                'buatkan', 'tuliskan', 'jelaskan', 'bagaimana cara', 'gimana cara',
                'apa itu', 'siapa itu', 'dimana', 'kapan', 'mengapa', 'kenapa', 'resep', 'kode',
            ];
            foreach ($generalQuestionTriggers as $trigger) {
                if (str_contains($q, $trigger)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Attempt a single fast completion with Groq/Grok API (max 8 seconds total).
     */
    private function tryCloudAiCompletion(
        string $apiKey,
        string $baseUrl,
        string $configuredModel,
        \App\Models\User $user,
        float $netBalance,
        float $activeIncome,
        float $activeExpense,
        string $activePeriodLabel,
        float $netSavings,
        float $savingsRate,
        string $topCatText,
        string $budgetText,
        string $goalsText,
        string $recentTxText,
        array $history,
        string $question
    ): ?string {
        try {
            $userName = $user->name;
            $fmtBalance = $user->formatCurrency($netBalance);
            $fmtIncome = $user->formatCurrency($activeIncome);
            $fmtExpense = $user->formatCurrency($activeExpense);
            $fmtSavings = $user->formatCurrency($netSavings);
            $isEnglish = $this->isEnglishContext($question, $user);

            if ($isEnglish) {
                $systemPrompt = "You are Finora AI Advisor, an intelligent, professional personal financial consultant in the Finora application.\n"
                    . "LANGUAGE DIRECTIVE:\n"
                    . "- You MUST answer in fluent, clear, polite, and helpful ENGLISH.\n"
                    . "- DO NOT use emojis or emoticons.\n\n"
                    . "FINANCIAL DOMAIN RULES:\n"
                    . "1. You ONLY answer questions related to personal finance, budgeting, cash flow, debt, investments, emergency funds, savings targets, and Finora features.\n"
                    . "2. If the user asks out-of-context topics (e.g. coding, food recipes, pop culture, politics), politely decline: \"" . self::OUT_OF_CONTEXT_REPLY_EN . "\"\n"
                    . "3. Never recommend high-risk gambling or get-rich-quick schemes.\n\n"
                    . "USER FINANCIAL SNAPSHOT ({$userName}):\n"
                    . "- Total Net Balance: {$fmtBalance}\n"
                    . "- Current Cash Flow ({$activePeriodLabel}): Inflow {$fmtIncome} | Outflow {$fmtExpense}\n"
                    . "- Net Savings: {$fmtSavings} (Savings Rate: {$savingsRate}%)\n"
                    . "- Top Spending Category: {$topCatText}\n"
                    . "- Budget Constraints: {$budgetText}\n"
                    . "- Savings Goals: {$goalsText}\n"
                    . "- Recent Transactions: {$recentTxText}";
            } else {
                $systemPrompt = "Kamu adalah Finora AI Advisor, asisten keuangan pribadi profesional di aplikasi Finora.\n"
                    . "ATURAN BAHASA MUTLAK:\n"
                    . "- Kamu WAJIB dan SELALU menjawab HANYA dalam BAHASA INDONESIA yang ramah, sopan, dan jelas.\n"
                    . "- DILARANG KERAS menggunakan Bahasa Arab atau bahasa selain Indonesia.\n"
                    . "- JANGAN gunakan emoji/emote unicode apapun.\n\n"
                    . "ATURAN DOMAIN KEUANGAN:\n"
                    . "1. Kamu HANYA BOLEH menjawab pertanyaan yang berhubungan dengan keuangan pribadi, cara mengelola uang, anggaran (budgeting), menabung, investasi, dana darurat, utang, arus kas, perencanaan finansial, dan fitur aplikasi Finora.\n"
                    . "2. Jika pengguna bertanya hal di luar konteks keuangan (seperti meminta kode HTML/pemrograman, resep makanan/cara membuat kopi, hiburan, politik, dll.), TOLAK dan jawab persis: \"" . self::OUT_OF_CONTEXT_REPLY . "\"\n\n"
                    . "DATA KEUANGAN PENGGUNA SAAT INI ({$userName}):\n"
                    . "- Total Saldo Bersih: {$fmtBalance}\n"
                    . "- Arus Kas ({$activePeriodLabel}): Pemasukan {$fmtIncome} | Pengeluaran {$fmtExpense}\n"
                    . "- Surplus / Tabungan Bersih: {$fmtSavings} (Savings Rate: {$savingsRate}%)\n"
                    . "- Kategori Pengeluaran Terbesar: {$topCatText}\n"
                    . "- Status Budget: {$budgetText}\n"
                    . "- Target Tabungan: {$goalsText}\n"
                    . "- Transaksi Terakhir: {$recentTxText}";
            }

            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
            ];

            foreach ($history as $msg) {
                $messages[] = [
                    'role' => $msg['role'],
                    'content' => $msg['content'],
                ];
            }

            $messages[] = [
                'role' => 'user',
                'content' => $question,
            ];

            // Determine best model to use (check cache first)
            $cacheKey = 'finora_ai_model_' . md5($apiKey . $baseUrl);
            $modelToUse = Cache::get($cacheKey, $configuredModel);

            $response = Http::withToken($apiKey)
                ->withoutVerifying()
                ->timeout(8)
                ->post("{$baseUrl}/chat/completions", [
                    'model' => $modelToUse,
                    'messages' => $messages,
                    'temperature' => 0.65,
                    'max_tokens' => 700,
                ]);

            if ($response->successful()) {
                $reply = $response->json('choices.0.message.content');
                if (!empty($reply)) {
                    Cache::put($cacheKey, $modelToUse, now()->addHours(4));
                    return trim($reply);
                }
            }

            // If configured model failed due to model access, query /models once and retry with the first available chat model
            $discoveredModel = $this->discoverFirstAvailableChatModel($baseUrl, $apiKey, $modelToUse);
            if ($discoveredModel !== null) {
                $retryResponse = Http::withToken($apiKey)
                    ->withoutVerifying()
                    ->timeout(8)
                    ->post("{$baseUrl}/chat/completions", [
                        'model' => $discoveredModel,
                        'messages' => $messages,
                        'temperature' => 0.65,
                        'max_tokens' => 700,
                    ]);

                if ($retryResponse->successful()) {
                    $reply = $retryResponse->json('choices.0.message.content');
                    if (!empty($reply)) {
                        Cache::put($cacheKey, $discoveredModel, now()->addHours(4));
                        return trim($reply);
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Cloud AI Completion encountered an issue, falling back to local engine: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Discover the first available chat model from GET /models in a single fast call.
     */
    private function discoverFirstAvailableChatModel(string $baseUrl, string $apiKey, string $excludeModel): ?string
    {
        try {
            $resp = Http::withToken($apiKey)
                ->withoutVerifying()
                ->timeout(4)
                ->get("{$baseUrl}/models");

            if (!$resp->successful()) {
                return null;
            }

            $data = $resp->json('data') ?? [];
            $candidates = [];
            foreach ($data as $item) {
                $id = (string) ($item['id'] ?? '');
                if ($id === '' || $id === $excludeModel) {
                    continue;
                }
                $lower = strtolower($id);
                // Strictly exclude Arabic, Whisper, TTS, Guard, Embed, Vision, PlayAI models
                if (
                    str_contains($lower, 'allam') ||
                    str_contains($lower, 'arabic') ||
                    str_contains($lower, 'whisper') ||
                    str_contains($lower, 'tts') ||
                    str_contains($lower, 'orpheus') ||
                    str_contains($lower, 'guard') ||
                    str_contains($lower, 'safeguard') ||
                    str_contains($lower, 'embed') ||
                    str_contains($lower, 'vision') ||
                    str_contains($lower, 'playai')
                ) {
                    continue;
                }
                $candidates[] = $id;
            }

            // Priority: Llama 3.1 8B instant, Llama 3.3 70B, Gemma 2, Mixtral
            foreach ($candidates as $c) {
                if (str_contains(strtolower($c), 'llama-3.1-8b-instant')) {
                    return $c;
                }
            }
            foreach ($candidates as $c) {
                if (str_contains(strtolower($c), 'llama-3.3-70b')) {
                    return $c;
                }
            }
            foreach ($candidates as $c) {
                if (str_contains(strtolower($c), 'llama-3')) {
                    return $c;
                }
            }
            foreach ($candidates as $c) {
                if (str_contains(strtolower($c), 'gemma2') || str_contains(strtolower($c), 'mixtral')) {
                    return $c;
                }
            }

            return $candidates[0] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Built-in Finora Financial Advisor Knowledge & Ledger Analyzer.
     */
    private function generateSmartFinancialResponse(
        string $question,
        \App\Models\User $user,
        float $netBalance,
        float $activeIncome,
        float $activeExpense,
        string $activePeriodLabel,
        float $netSavings,
        float $savingsRate,
        string $topCatText,
        string $budgetText,
        string $goalsText,
        string $recentTxText
    ): string {
        $q = mb_strtolower(trim($question));
        $userName = $user->name;
        $firstName = explode(' ', trim($userName))[0];

        $fmtBalance = $user->formatCurrency($netBalance);
        $fmtInc = $user->formatCurrency($activeIncome);
        $fmtExp = $user->formatCurrency($activeExpense);
        $fmtSav = $user->formatCurrency($netSavings);

        $isEnglish = $this->isEnglishContext($question, $user);

        if ($isEnglish) {
            // 1. General Financial Management / Advice
            if (str_contains($q, 'manage') || str_contains($q, 'rule') || str_contains($q, 'tips') || str_contains($q, 'advice') || str_contains($q, 'how to') || str_contains($q, 'guide') || str_contains($q, 'money')) {
                return "Hello {$firstName}, here is strategic personal finance guidance tailored to your current Finora account status:\n\n"
                    . "1. Follow the 50/30/20 Framework:\n"
                    . "   - 50% of income for essential necessities (housing, utilities, groceries, transport).\n"
                    . "   - 30% for lifestyle & personal wants.\n"
                    . "   - At least 20% dedicated to savings, debt paydown, and long-term investments.\n\n"
                    . "2. Maintain Accurate Ledger Tracking:\n"
                    . "   - Your Total Net Balance is currently {$fmtBalance} with a {$savingsRate}% net savings rate.\n"
                    . "   - Keep logging transactions regularly to avoid untracked expense leakage.\n\n"
                    . "3. Adhere to Budget Limits:\n"
                    . "   - Active Budgets: {$budgetText}.\n"
                    . "   - Monitor category thresholds to stay under 80%-90% capacity by month-end.\n\n"
                    . "4. Solidify Emergency Reserve:\n"
                    . "   - Target 3-6 months worth of essential expenses in a liquid savings account.";
            }

            // 2. Budget Health
            if (str_contains($q, 'budget') || str_contains($q, 'limit') || str_contains($q, 'overspend') || str_contains($q, 'cut')) {
                return "Hello {$firstName}, here is your Budget and Expenditure health check:\n\n"
                    . "- Active Budget Status: {$budgetText}\n"
                    . "- Total Outflow ({$activePeriodLabel}): {$fmtExp} from inflow of {$fmtInc}\n"
                    . "- Top Spending Category: {$topCatText}\n\n"
                    . "Actionable Financial Tips:\n"
                    . "1. Review your highest-spending category and set strict limit caps in the Budgets menu.\n"
                    . "2. Use the 24-hour rule before making discretionary non-essential purchases.\n"
                    . "3. Keep monthly category spending below 80% to maintain a healthy financial surplus.";
            }

            // 3. Savings Goals
            if (str_contains($q, 'saving') || str_contains($q, 'save') || str_contains($q, 'goal') || str_contains($q, 'target')) {
                return "Hello {$firstName}, here is your Savings Goals & Wealth Accumulation review:\n\n"
                    . "- Goals Progress: {$goalsText}\n"
                    . "- Monthly Surplus ({$activePeriodLabel}): {$fmtSav} (Savings Rate: {$savingsRate}%)\n\n"
                    . "Finora Optimization Strategy:\n"
                    . "1. Pay Yourself First: Transfer savings immediately upon receiving income.\n"
                    . "2. Use the 'Add Funds' button in Savings Goals to deposit unspent monthly budget surplus.\n"
                    . "3. Trim 10% from discretionary expenses to accelerate your target completion dates.";
            }

            // 4. Default English Overview
            return "Hello {$firstName}, here is a comprehensive financial summary of your Finora ledger:\n\n"
                . "1. Total Net Balance: {$fmtBalance}\n"
                . "2. Monthly Cash Flow ({$activePeriodLabel}): Inflow {$fmtInc} | Outflow {$fmtExp}\n"
                . "3. Net Surplus / Savings: {$fmtSav} (Savings Rate: {$savingsRate}%)\n"
                . "4. Top Expense Category: {$topCatText}\n"
                . "5. Active Budgets: {$budgetText}\n"
                . "6. Goals Status: {$goalsText}\n\n"
                . ($savingsRate >= 20 
                    ? "Finora AI Assessment: Your savings rate ({$savingsRate}%) is very healthy! Maintain financial discipline and channel surplus towards your goals."
                    : "Finora AI Assessment: Keep monitoring your outflow to maintain a positive cash flow surplus each billing cycle.");
        }

        // 1. General Financial Management / How to manage money well ("Bagaimana cara mengelola keuangan dengan baik")
        if (
            (str_contains($q, 'kelola') || str_contains($q, 'mengelola') || str_contains($q, 'atur') || str_contains($q, 'mengatur') || str_contains($q, 'tips') || str_contains($q, 'cara')) &&
            (str_contains($q, 'keuangan') || str_contains($q, 'uang') || str_contains($q, 'finansial') || str_contains($q, 'gaji'))
        ) {
            return "Halo {$firstName}, berikut panduan strategis cara mengelola keuangan dengan baik beserta kaitannya dengan kondisi akun Finora Anda saat ini:\n\n"
                . "1. Terapkan Metode 50/30/20:\n"
                . "   - 50% dari pemasukan untuk kebutuhan pokok (makan, tagihan, transportasi).\n"
                . "   - 30% untuk keinginan & gaya hidup.\n"
                . "   - Minimal 20% langsung disisihkan untuk tabungan dan investasi di awal bulan.\n\n"
                . "2. Catat Seluruh Arus Kas Secara Rutin:\n"
                . "   - Saat ini Saldo Bersih Anda di Finora tercatat sebesar {$fmtBalance} dengan rasio tabungan (Savings Rate) {$savingsRate}%.\n"
                . "   - Pastikan setiap pengeluaran kecil maupun besar dicatat di menu Transactions agar tidak ada kebocoran anggaran.\n\n"
                . "3. Patuhi Batas Anggaran (Budget Limits):\n"
                . "   - Status Budget Anda saat ini: {$budgetText}.\n"
                . "   - Gunakan fitur Budgets di Finora untuk membatasi pengeluaran per kategori agar tidak melampaui 80%-90% dari batas bulanan.\n\n"
                . "4. Bangun Dana Darurat & Target Tabungan:\n"
                . "   - Siapkan dana darurat minimal 3-6 kali pengeluaran bulanan.\n"
                . "   - Buat target spesifik di menu Savings Goals untuk memantau progres tabungan Anda secara terukur.";
        }

        // 2. Dana Darurat & Investasi
        if (str_contains($q, 'investasi') || str_contains($q, 'dana darurat') || str_contains($q, 'emergency') || str_contains($q, 'reksadana') || str_contains($q, 'saham') || str_contains($q, 'emas')) {
            return "Halo {$firstName}, berikut panduan perencanaan Dana Darurat dan Investasi dari Finora AI:\n\n"
                . "1. Prioritaskan Dana Darurat Terlebih Dahulu:\n"
                . "   - Idealnya 3-6x pengeluaran bulanan disimpan di instrumen likuid (rekening terpisah atau reksa dana pasar uang).\n\n"
                . "2. Sesuaikan Instrumen dengan Jangka Waktu Tujuan:\n"
                . "   - Jangka Pendek (< 1 tahun): Deposito atau Reksa Dana Pasar Uang.\n"
                . "   - Jangka Menengah (1-5 tahun): Emas, Obligasi Negara (ORI/SBR), atau Reksa Dana Pendapatan Tetap.\n"
                . "   - Jangka Panjang (> 5 tahun): Reksa Dana Saham atau Saham Blue-Chip.\n\n"
                . "3. Kondisi Kas Anda Saat Ini:\n"
                . "   - Saldo Bersih Anda saat ini adalah {$fmtBalance} dengan surplus bersih {$fmtSav}. Anda dapat mulai mengalokasikan sebagian surplus tersebut ke menu Savings Goals di Finora.";
        }

        // 3. Budget / Anggaran questions
        if (str_contains($q, 'budget') || str_contains($q, 'anggaran') || str_contains($q, 'batas') || str_contains($q, 'boros') || str_contains($q, 'hemat')) {
            return "Halo {$firstName}, berikut analisis kesehatan Budget dan pengendalian pengeluaran Anda:\n\n"
                . "- Status Budget Saat Ini: {$budgetText}\n"
                . "- Total Pengeluaran ({$activePeriodLabel}): {$fmtExp} dari pemasukan {$fmtInc}\n"
                . "- Kategori Pengeluaran Terbesar: {$topCatText}\n\n"
                . "Tips Berhemat Finora AI:\n"
                . "1. Evaluasi kategori pengeluaran terbesar Anda dan tetapkan batas maksimal di menu Budgets.\n"
                . "2. Gunakan aturan tunggu 24 jam sebelum membeli barang non-prioritas.\n"
                . "3. Jaga pemakaian setiap kategori budget agar tetap di bawah 80% menjelang akhir bulan.";
        }

        // 4. Tabungan / Savings Goals questions
        if (str_contains($q, 'tabung') || str_contains($q, 'goal') || str_contains($q, 'target') || str_contains($q, 'saving')) {
            return "Halo {$firstName}, berikut evaluasi dan strategi Target Tabungan (Savings Goals) Anda:\n\n"
                . "- Progres Target Tabungan: {$goalsText}\n"
                . "- Surplus Bersih ({$activePeriodLabel}): {$fmtSav} (Savings Rate: {$savingsRate}%)\n\n"
                . "Strategi Mempercepat Tabungan:\n"
                . "1. Sisihkan minimal 20% pemasukan langsung di hari gajian (Pay Yourself First).\n"
                . "2. Alokasikan sisa budget bulanan yang tidak terpakai langsung ke tombol 'Add Funds' pada menu Savings Goals.\n"
                . "3. Kurangi 10-15% pengeluaran pada kategori terbesar ({$topCatText}) untuk menambah setoran tabungan.";
        }

        // 5. Transaksi / Riwayat questions
        if (str_contains($q, 'transaksi') || str_contains($q, 'riwayat') || str_contains($q, 'terakhir') || str_contains($q, 'catatan')) {
            return "Halo {$firstName}, berikut ringkasan aktivitas transaksi Anda di Finora:\n\n"
                . "- Saldo Bersih Saat Ini: {$fmtBalance}\n"
                . "- Arus Kas ({$activePeriodLabel}): Pemasukan {$fmtInc} | Pengeluaran {$fmtExp}\n"
                . "- Transaksi Terakhir: {$recentTxText}\n"
                . "- Kategori Pengeluaran Terbesar: {$topCatText}";
        }

        // 6. Default Comprehensive Financial Overview
        return "Halo {$firstName}, berikut analisis lengkap kesehatan keuangan Anda dari Finora AI:\n\n"
            . "1. Saldo Bersih Total: {$fmtBalance}\n"
            . "2. Arus Kas ({$activePeriodLabel}): Pemasukan {$fmtInc} | Pengeluaran {$fmtExp}\n"
            . "3. Surplus Bersih: {$fmtSav} (Savings Rate: {$savingsRate}%)\n"
            . "4. Pengeluaran Terbesar: {$topCatText}\n"
            . "5. Status Budget: {$budgetText}\n"
            . "6. Target Tabungan: {$goalsText}\n\n"
            . ($savingsRate >= 20
                ? "Rekomendasi Finora AI: Rasio tabungan Anda sangat sehat ({$savingsRate}%). Pertahankan disiplin pencatatan transaksi dan alokasikan surplus bersih Anda ke Savings Goals."
                : "Rekomendasi Finora AI: Untuk mengoptimalkan keuangan Anda, usahakan menyisihkan minimal 20% dari pemasukan untuk tabungan dan pantau batas anggaran di menu Budgets.");
    }
}
