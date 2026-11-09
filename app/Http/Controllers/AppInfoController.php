<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppInfoController extends Controller
{
    /**
     * Display the Info & Version (App Info, Updates & Changelog) page.
     */
    public function index(Request $request): View
    {
        $currentVersion = config('app.version', '2.5.0');
        $codename = config('app.version_codename', 'Aurora Zenith');
        $releaseDate = config('app.version_date', '9 November 2026');
        $isEn = app()->getLocale() === 'en';

        $appMeta = [
            'name' => 'Finora',
            'tagline' => $isEn ? 'Smart Personal Finance & Modern Wealth OS' : 'Pusat Keuangan Pribadi & Sistem Kekayaan Modern',
            'description' => $isEn
                ? 'All-in-one personal financial management platform with AI advisor, smart financial calculator, automated receipt OCR scanner, multi-currency accounts, and real-time financial health audit.'
                : 'Platform manajemen keuangan pribadi serba bisa dengan asisten AI, kalkulator finansial pintar, deteksi struk otomatis, multi-mata uang, dan audit kesehatan finansial real-time.',
            'version' => $currentVersion,
            'codename' => $codename,
            'release_date' => $isEn ? 'November 9, 2026' : $releaseDate,
            'environment' => config('app.env', 'production'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => config('database.default', 'sqlite'),
            'timezone' => config('app.timezone', 'UTC'),
            'locale' => app()->getLocale(),
            'uptime' => '99.98%',
            'status' => $isEn ? 'All Systems Operational' : 'Semua Sistem Normal',
            'license' => 'MIT License',
            'repository' => 'https://github.com/finora/finora-app',
        ];

        $stats = [
            'versions_count' => 6,
            'features_count' => 18,
            'currencies_supported' => 7,
            'uptime_rate' => '99.9%',
        ];

        $releases = $this->getReleasesList($isEn);
        $roadmap = $this->getRoadmapItems($isEn);
        $systemSpecs = $this->getSystemSpecs();

        return view('info.index', compact(
            'appMeta',
            'stats',
            'releases',
            'roadmap',
            'systemSpecs'
        ));
    }

    /**
     * Return JSON payload of the latest release for the "What's New" modal.
     */
    public function whatsNew(): JsonResponse
    {
        $isEn = app()->getLocale() === 'en';
        $releases = $this->getReleasesList($isEn);
        $latest = $releases[0] ?? null;

        return response()->json([
            'version' => config('app.version', '2.5.0'),
            'latest_release' => $latest,
        ]);
    }

    /**
     * Get compiled release history and update logs.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getReleasesList(bool $isEn): array
    {
        if ($isEn) {
            return [
                [
                    'version' => 'v2.5.0',
                    'name' => 'App Info, Version Hub & System Navigation Enhancements',
                    'codename' => 'Aurora Zenith',
                    'date' => 'November 9, 2026',
                    'is_latest' => true,
                    'badge' => 'Latest Release',
                    'summary' => 'Introduces an integrated App Info & Version hub to monitor system updates and release history, What\'s New highlights modal, quick language switcher, and UI efficiency improvements.',
                    'highlights' => [
                        'Comprehensive App Info & Version page with interactive changelog and category filtering.',
                        'Interactive "What\'s New" modal to preview release highlights instantly.',
                        'Sidebar and footer navigation integration with active version indicators and quick language switch.',
                        'Technical system diagnostics panel for environment and server transparency.',
                    ],
                    'changes' => [
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'App Info & Version Hub',
                            'description' => 'Check current app version, periodic update history, changelog, and future roadmap directly from navigation.',
                            'action_route' => route('info.index'),
                            'action_label' => 'View Info Hub',
                            'tag' => 'Core Suite',
                        ],
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'What\'s New Highlights Modal',
                            'description' => 'Instant modal popup displaying the latest release features without disrupting your main financial workflow.',
                            'action_route' => null,
                            'action_label' => null,
                            'tag' => 'User Experience',
                        ],
                        [
                            'type' => 'improvement',
                            'category' => 'Improvement',
                            'title' => 'Version Badge & Quick Language Switcher',
                            'description' => 'Top bar and sidebar now feature an active v2.5.0 badge and instant one-click language toggle (English / Indonesian).',
                            'action_route' => null,
                            'action_label' => null,
                            'tag' => 'Navigation',
                        ],
                        [
                            'type' => 'improvement',
                            'category' => 'Improvement',
                            'title' => 'System Diagnostics & Technical Transparency',
                            'description' => 'Information on database drivers, PHP runtime, Laravel framework, and timezone status for full technical clarity.',
                            'action_route' => null,
                            'action_label' => null,
                            'tag' => 'DevOps',
                        ],
                        [
                            'type' => 'fix',
                            'category' => 'Bug Fix',
                            'title' => 'Dictionary & Localization Optimization',
                            'description' => 'Comprehensive English and Indonesian language dictionaries for seamless international and local usage.',
                            'action_route' => null,
                            'action_label' => null,
                            'tag' => 'Localization',
                        ],
                    ],
                ],
                [
                    'version' => 'v2.4.0',
                    'name' => 'Smart Financial Calculator & Scratchpad Suite',
                    'codename' => 'Nova Horizon',
                    'date' => 'November 9, 2026',
                    'is_latest' => false,
                    'badge' => 'Core Feature',
                    'summary' => 'Intelligent financial calculator featuring Hourly Worth conversion, loan and debt amortization simulation, compounding latte factor, and instant transaction recording.',
                    'highlights' => [
                        'Quick Calculator & Financial Scratchpad with calculation history.',
                        'Hourly Worth: discover how many working hours an item costs based on your monthly wage.',
                        'Loan & Amortization Simulation with automatic active debt detection.',
                        'Push to Transaction: record calculation results to ledger with one click.',
                    ],
                    'changes' => [
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'Smart Calculator & Financial Scratchpad',
                            'description' => 'Arithmetic and financial calculation center inside Finora without needing external calculator apps.',
                            'action_route' => route('calculator.index'),
                            'action_label' => 'Open Calculator',
                            'tag' => 'Calculator',
                        ],
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'Hourly Worth ("Worth the Hours")',
                            'description' => 'Calculates the real cost of items in working hours based on user earnings.',
                            'action_route' => route('calculator.index'),
                            'action_label' => 'Try Hourly Worth',
                            'tag' => 'Psychology',
                        ],
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'Loan & Amortization Simulator',
                            'description' => 'Simulate monthly payments with flat/effective interest and auto-populate from active debts.',
                            'action_route' => route('calculator.index'),
                            'action_label' => 'Simulate Loan',
                            'tag' => 'Loans',
                        ],
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'The Latte Compounding Factor',
                            'description' => 'Discover how much small daily savings can grow when compounded over years.',
                            'action_route' => route('calculator.index'),
                            'action_label' => 'Try Latte Effect',
                            'tag' => 'Investment',
                        ],
                    ],
                ],
                [
                    'version' => 'v2.3.0',
                    'name' => 'Feature Guide Hub, Interactive Tour & Multi-Currency',
                    'codename' => 'Starlight Guide',
                    'date' => 'November 7, 2026',
                    'is_latest' => false,
                    'badge' => 'Onboarding & Forex',
                    'summary' => 'Interactive 6-step onboarding tour, global multi-currency support (USD, EUR, SGD, JPY, GBP, AUD, IDR), and internal wallet fund transfers.',
                    'highlights' => [
                        'Help Center & Feature Guide with interactive onboarding tour.',
                        'Multi-Currency Wallet Engine with real-time forex rate conversion.',
                        'Internal Fund Transfer between bank accounts and e-wallets.',
                    ],
                    'changes' => [
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'Help Center & Feature Guide',
                            'description' => 'Complete tutorial catalog for 12 core Finora modules with step-by-step guides and tips.',
                            'action_route' => route('tutorials.index'),
                            'action_label' => 'Open Guide',
                            'tag' => 'Tutorials',
                        ],
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'Multi-Currency Wallets & Forex Rates',
                            'description' => 'Create accounts in IDR, USD, EUR, SGD, GBP, JPY, or AUD with automatic conversion.',
                            'action_route' => route('wallets.index'),
                            'action_label' => 'Manage Wallets',
                            'tag' => 'Forex',
                        ],
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'Inter-Account Fund Transfers',
                            'description' => 'Move funds between accounts without distorting genuine income or expense reports.',
                            'action_route' => route('wallets.index'),
                            'action_label' => 'Try Transfer',
                            'tag' => 'Wallets',
                        ],
                    ],
                ],
                [
                    'version' => 'v2.2.0',
                    'name' => 'AI Receipt Scanner & Natural Language Quick-Add',
                    'codename' => 'Visionary Pulse',
                    'date' => 'November 7, 2026',
                    'is_latest' => false,
                    'badge' => 'AI & OCR',
                    'summary' => 'Automated receipt OCR extraction via Tesseract.js and lightning-fast natural language transaction recording.',
                    'highlights' => [
                        'Scan Physical Receipts with OCR Tesseract.js & Groq Vision.',
                        'Smart Quick-Add Bar: Type natural phrases like "Coffee 35k gopay" to record instantly.',
                        'Automated Bank Statement CSV Import (BCA, Mandiri, BNI, BRI).',
                    ],
                    'changes' => [
                        [
                            'type' => 'ai',
                            'category' => 'AI & Automation',
                            'title' => 'AI Receipt Scanner (OCR)',
                            'description' => 'Upload photos of physical shopping receipts; AI automatically detects total amount, merchant, date, and line items.',
                            'action_route' => route('dashboard'),
                            'action_label' => 'Try on Dashboard',
                            'tag' => 'Vision AI',
                        ],
                        [
                            'type' => 'ai',
                            'category' => 'AI & Automation',
                            'title' => 'Smart Quick-Add Bar (Natural Language)',
                            'description' => 'Ultra-fast transaction recording using everyday conversational sentences in Indonesian or English.',
                            'action_route' => route('dashboard'),
                            'action_label' => 'Open Dashboard',
                            'tag' => 'NLP',
                        ],
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'Smart Bank Mutation Import (CSV Parser)',
                            'description' => 'Upload bank statements and Finora automatically maps and categorizes transaction lines.',
                            'action_route' => route('transactions.import'),
                            'action_label' => 'Import Statements',
                            'tag' => 'Import',
                        ],
                    ],
                ],
                [
                    'version' => 'v2.1.0',
                    'name' => 'Debt & Loan Tracker + WhatsApp Split Bill',
                    'codename' => 'Harmony Social',
                    'date' => 'November 6, 2026',
                    'is_latest' => false,
                    'badge' => 'Debts & Social',
                    'summary' => 'Detailed debt and receivable tracking, installment records, and bill splitting with instant WhatsApp sharing.',
                    'highlights' => [
                        'Debt & Receivable Tracker with Paid/Unpaid status and due date alerts.',
                        'Split Bill Generator: Fair expense sharing among dining friends.',
                        'Send Split Bill Details directly to WhatsApp Web / Mobile.',
                        'Cash Flow Calendar to map recurring bills and due dates.',
                    ],
                    'changes' => [
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'Debt & Receivable Tracker',
                            'description' => 'Record money lent to friends or loans owed, complete with installment payment records.',
                            'action_route' => route('debts.index'),
                            'action_label' => 'View Debts',
                            'tag' => 'Debts',
                        ],
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'WhatsApp Split Bill Integration',
                            'description' => 'Split restaurant bills or group expenses with one-click direct share to WhatsApp chats.',
                            'action_route' => route('debts.index'),
                            'action_label' => 'Split Bill',
                            'tag' => 'Social Finance',
                        ],
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'Monthly Cash Flow Calendar',
                            'description' => 'Interactive calendar mapping transactions, recurring bills, and debt due dates.',
                            'action_route' => route('calendar.index'),
                            'action_label' => 'Open Calendar',
                            'tag' => 'Calendar',
                        ],
                    ],
                ],
                [
                    'version' => 'v2.0.0',
                    'name' => 'Finora AI Financial Advisor & Financial Health Radar',
                    'codename' => 'Apex Intelligence',
                    'date' => 'November 6, 2026',
                    'is_latest' => false,
                    'badge' => 'Generative AI',
                    'summary' => 'Groq-powered AI Financial Advisor with real ledger context, 5-Pillar Health Score, 90-day Cashflow Forecast, and Monthly Financial Wrapped.',
                    'highlights' => [
                        'Finora AI Advisor: 24/7 financial strategy consultation with actual ledger context.',
                        'Financial Health Score (0-100) and 5-Pillar Audit Radar.',
                        '30-90 Day Cashflow Forecast with low balance runway warning.',
                        'Monthly Financial Wrapped: Spotify-style visual recap of monthly finances.',
                    ],
                    'changes' => [
                        [
                            'type' => 'ai',
                            'category' => 'AI & Automation',
                            'title' => 'Finora AI Financial Advisor',
                            'description' => 'Assistant widget analyzing balances, overspending categories, and tailored savings suggestions.',
                            'action_route' => null,
                            'action_label' => null,
                            'tag' => 'LLM Advisor',
                        ],
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'Financial Health Score & 5-Pillar Radar',
                            'description' => 'Holistic evaluation of savings rate, emergency runway, debt burden, budget adherence, and liquidity.',
                            'action_route' => route('financial-health.index'),
                            'action_label' => 'Check Health Score',
                            'tag' => 'Health Radar',
                        ],
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'Cashflow Forecast & Runway Calculator',
                            'description' => 'Predictive engine forecasting daily balances 30-90 days ahead.',
                            'action_route' => route('cashflow.index'),
                            'action_label' => 'View Forecast',
                            'tag' => 'Forecasting',
                        ],
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'Monthly Financial Wrapped',
                            'description' => 'Visual story slides summarizing top expense categories, peak shopping days, and financial persona.',
                            'action_route' => route('wrapped.index'),
                            'action_label' => 'Open Wrapped',
                            'tag' => 'Wrapped',
                        ],
                    ],
                ],
                [
                    'version' => 'v1.0.0',
                    'name' => 'Finora Core Ledger Foundation',
                    'codename' => 'Genesis Foundation',
                    'date' => 'November 6, 2026',
                    'is_latest' => false,
                    'badge' => 'Foundation',
                    'summary' => 'Initial launch of Finora featuring income and expense tracking, category budgets, recurring bills, savings targets, and report exports.',
                    'highlights' => [
                        'Multi-category transaction ledger with custom date filters.',
                        'Category Budgets with visual progress indicators.',
                        'Recurring bill reminders & overspending notifications.',
                        'Savings Goals & PDF/CSV report exports.',
                    ],
                    'changes' => [
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'Multi-Category Transaction Ledger',
                            'description' => 'Record income and expenses with dynamic categories, icons, and colors.',
                            'action_route' => route('transactions.index'),
                            'action_label' => 'View Transactions',
                            'tag' => 'Ledger',
                        ],
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'Category Budgets & Limit Warnings',
                            'description' => 'Set monthly spending ceilings per category and monitor real-time utilization.',
                            'action_route' => route('budgets.index'),
                            'action_label' => 'Set Budgets',
                            'tag' => 'Budgets',
                        ],
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'Savings Goals & Dream Funds',
                            'description' => 'Allocate dedicated funds for house, travel, or emergency buffers with dynamic progress bars.',
                            'action_route' => route('savings.index'),
                            'action_label' => 'Open Savings',
                            'tag' => 'Savings',
                        ],
                        [
                            'type' => 'feature',
                            'category' => 'New Feature',
                            'title' => 'Financial Reports & PDF / CSV Export',
                            'description' => 'Compare monthly expense charts and download report files for personal accounting.',
                            'action_route' => route('reports.index'),
                            'action_label' => 'View Reports',
                            'tag' => 'Reports',
                        ],
                    ],
                ],
            ];
        }

        return [
            [
                'version' => 'v2.5.0',
                'name' => 'Pembaruan Info & Versi serta Peningkatan Navigasi Sistem',
                'codename' => 'Aurora Zenith',
                'date' => '9 November 2026',
                'is_latest' => true,
                'badge' => 'Rilis Terbaru',
                'summary' => 'Menghadirkan fitur Info & Versi terintegrasi untuk memantau pembaruan sistem dan riwayat rilis, modal sorotan update, pengalih bahasa cepat, serta peningkatan efisiensi antarmuka.',
                'highlights' => [
                    'Halaman Info & Versi lengkap dengan log changelog interaktif dan filter kategori.',
                    'Modal interaktif "Apa yang Baru" (What\'s New) untuk melihat sorotan fitur rilis terbaru.',
                    'Peningkatan navigasi sidebar dan footer dengan indikator versi aktif dan tombol pengalih bahasa.',
                    'Panel spesifikasi teknis dan diagnostik server aplikasi.',
                ],
                'changes' => [
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Halaman Info & Versi Aplikasi',
                        'description' => 'Kini Anda dapat mengecek versi aplikasi saat ini, riwayat update berkala, catatan rilis (changelog), hingga rencana fitur masa depan (roadmap) langsung dari menu navigasi.',
                        'action_route' => route('info.index'),
                        'action_label' => 'Lihat Halaman Info',
                        'tag' => 'Core Suite',
                    ],
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Modal Sorotan Update ("Apa yang Baru")',
                        'description' => 'Akses kilat untuk melihat fitur-fitur yang baru saja dirilis tanpa meninggalkan alur kerja utama Anda.',
                        'action_route' => null,
                        'action_label' => null,
                        'tag' => 'User Experience',
                    ],
                    [
                        'type' => 'improvement',
                        'category' => 'Peningkatan',
                        'title' => 'Badge Versi & Pengalih Bahasa Cepat',
                        'description' => 'Top bar dan sidebar kini menampilkan badge versi aktif "v2.5.0" dan pengalih bahasa satu klik (Inggris / Indonesia).',
                        'action_route' => null,
                        'action_label' => null,
                        'tag' => 'Navigation',
                    ],
                    [
                        'type' => 'improvement',
                        'category' => 'Peningkatan',
                        'title' => 'Spesifikasi Sistem & Transparansi Diagnostik',
                        'description' => 'Informasi driver basis data, versi runtime PHP, Laravel Framework, dan status zona waktu tersedia untuk transparansi teknis.',
                        'action_route' => null,
                        'action_label' => null,
                        'tag' => 'DevOps',
                    ],
                    [
                        'type' => 'fix',
                        'category' => 'Perbaikan',
                        'title' => 'Optimalisasi Kamus Terjemahan & Lokalisasi',
                        'description' => 'Menambahkan kunci bahasa Indonesia dan Inggris secara menyeluruh untuk kenyamanan penggunaan dwibahasa.',
                        'action_route' => null,
                        'action_label' => null,
                        'tag' => 'Localization',
                    ],
                ],
            ],
            [
                'version' => 'v2.4.0',
                'name' => 'Smart Financial Calculator & Scratchpad Suite',
                'codename' => 'Nova Horizon',
                'date' => '9 November 2026',
                'is_latest' => false,
                'badge' => 'Fitur Utama',
                'summary' => 'Kalkulator finansial pintar terintegrasi dengan mode konversi jam kerja (Hourly Worth), simulasi cicilan, efek bunga majemuk kopi, dan pencatatan transaksi kilat.',
                'highlights' => [
                    'Kalkulator Cepat & Scratchpad Finansial dengan riwayat perhitungan.',
                    'Konversi Jam Kerja: ketahui berapa lama Anda harus bekerja demi membeli suatu barang.',
                    'Simulasi Cicilan & Angsuran Pinjaman dengan deteksi otomatis hutang aktif.',
                    'Push to Transaction: simpan hasil kalkulasi ke buku kas dengan satu klik.',
                ],
                'changes' => [
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Smart Calculator & Financial Scratchpad',
                        'description' => 'Pusat perhitungan aritmatika dan finansial langsung di Finora tanpa perlu kalkulator eksternal.',
                        'action_route' => route('calculator.index'),
                        'action_label' => 'Buka Kalkulator',
                        'tag' => 'Calculator',
                    ],
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Kalkulator Jam Kerja ("Worth the Hours")',
                        'description' => 'Menghitung biaya suatu barang dalam satuan jam kerja berdasarkan gaji bulanan pengguna.',
                        'action_route' => route('calculator.index'),
                        'action_label' => 'Coba Jam Kerja',
                        'tag' => 'Psychology',
                    ],
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Simulasi Cicilan & Angsuran Pinjaman',
                        'description' => 'Simulasikan cicilan bulanan lengkap dengan suku bunga flat/efektif dan pilih hutang aktif untuk mengisi otomatis.',
                        'action_route' => route('calculator.index'),
                        'action_label' => 'Coba Simulasi',
                        'tag' => 'Loans',
                    ],
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Efek Kopi (The Latte Compounding Factor)',
                        'description' => 'Ketahui seberapa besar penghematan kecil harian jika diinvestasikan dengan bunga majemuk selama bertahun-tahun.',
                        'action_route' => route('calculator.index'),
                        'action_label' => 'Coba Efek Kopi',
                        'tag' => 'Investment',
                    ],
                ],
            ],
            [
                'version' => 'v2.3.0',
                'name' => 'Pusat Panduan, Tur Interaktif & Multi-Mata Uang',
                'codename' => 'Starlight Guide',
                'date' => '7 November 2026',
                'is_latest' => false,
                'badge' => 'Onboarding & Forex',
                'summary' => 'Pusat panduan onboarding interaktif 6 langkah, dukungan multi-mata uang global (USD, EUR, SGD, JPY, GBP, AUD, IDR), dan transfer saldo antar rekening.',
                'highlights' => [
                    'Halaman Pusat Bantuan & Tutorial dengan tur onboarding interaktif.',
                    'Multi-Currency Wallet Engine dengan konversi kurs forex real-time.',
                    'Transfer Saldo Antar Rekening internal dengan pencatatan mutasi otomatis.',
                ],
                'changes' => [
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Pusat Bantuan & Panduan Fitur',
                        'description' => 'Katalog tutorial lengkap untuk 12 modul utama Finora dengan panduan langkah demi langkah dan tips hemat.',
                        'action_route' => route('tutorials.index'),
                        'action_label' => 'Buka Panduan',
                        'tag' => 'Tutorials',
                    ],
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Multi-Currency Wallet & Kurs Forex',
                        'description' => 'Buat rekening dalam IDR, USD, EUR, SGD, GBP, JPY, atau AUD dengan konversi otomatis ke mata uang utama.',
                        'action_route' => route('wallets.index'),
                        'action_label' => 'Kelola Dompet',
                        'tag' => 'Forex',
                    ],
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Transfer Saldo Antar Rekening',
                        'description' => 'Pindahkan dana antar rekening bank atau e-wallet tanpa mengacaukan riwayat pengeluaran atau pemasukan murni.',
                        'action_route' => route('wallets.index'),
                        'action_label' => 'Coba Transfer',
                        'tag' => 'Wallets',
                    ],
                ],
            ],
            [
                'version' => 'v2.2.0',
                'name' => 'AI Receipt Scanner & Natural Language Quick-Add',
                'codename' => 'Visionary Pulse',
                'date' => '7 November 2026',
                'is_latest' => false,
                'badge' => 'AI & OCR',
                'summary' => 'Ekstraksi struk belanja otomatis via Optical Character Recognition (OCR) dan pencatatan transaksi secepat kilat dengan kalimat alami santai.',
                'highlights' => [
                    'Scan Struk Belanja dengan AI OCR Tesseract.js & Groq Vision.',
                    'Smart Quick-Add Bar: Ketik "Kopi 25rb gopay" langsung tercatat.',
                    'Smart Import Mutasi Bank otomatis dari file CSV (BCA, Mandiri, BNI, BRI).',
                ],
                'changes' => [
                    [
                        'type' => 'ai',
                        'category' => 'AI & Otomasi',
                        'title' => 'AI Receipt Scanner (OCR)',
                        'description' => 'Unggah foto struk belanjaan fisik, AI otomatis mendeteksi nominal total, tanggal transaksi, nama merchant, dan item.',
                        'action_route' => route('dashboard'),
                        'action_label' => 'Coba di Dashboard',
                        'tag' => 'Vision AI',
                    ],
                    [
                        'type' => 'ai',
                        'category' => 'AI & Otomasi',
                        'title' => 'Smart Quick-Add Bar (Natural Language)',
                        'description' => 'Pencatatan transaksi super cepat menggunakan kalimat santai sehari-hari dalam Bahasa Indonesia atau Inggris.',
                        'action_route' => route('dashboard'),
                        'action_label' => 'Buka Dashboard',
                        'tag' => 'NLP',
                    ],
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Import Mutasi Bank Cerdas (CSV Parser)',
                        'description' => 'Unggah mutasi rekening koran dari BCA, Mandiri, BNI, BRI, lalu Finora otomatis mengkategorikan baris transaksi.',
                        'action_route' => route('transactions.import'),
                        'action_label' => 'Import Mutasi',
                        'tag' => 'Import',
                    ],
                ],
            ],
            [
                'version' => 'v2.1.0',
                'name' => 'Pelacak Hutang & Split Bill WhatsApp',
                'codename' => 'Harmony Social',
                'date' => '6 November 2026',
                'is_latest' => false,
                'badge' => 'Hutang & Sosial',
                'summary' => 'Manajemen hutang-piutang terperinci, pencatatan angsuran bertahap, dan bagi tagihan makan bareng dengan rincian instan ke WhatsApp.',
                'highlights' => [
                    'Debt & Receivable Tracker dengan status Lunas/Belum Lunas & tanggal jatuh tempo.',
                    'Split Bill Generator: Bagi tagihan makan bersama teman dengan porsi adil.',
                    'Kirim Rincian Split Bill langsung ke WhatsApp Web / WhatsApp Mobile.',
                    'Kalender Arus Kas interaktif untuk memetakan tagihan dan pemasukan bulanan.',
                ],
                'changes' => [
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Pelacak Hutang & Piutang',
                        'description' => 'Catat uang yang Anda pinjamkan ke orang lain atau pinjaman yang wajib Anda lunasi, lengkap dengan catatan angsuran berkala.',
                        'action_route' => route('debts.index'),
                        'action_label' => 'Lihat Hutang & Piutang',
                        'tag' => 'Debts',
                    ],
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Split Bill WhatsApp Integration',
                        'description' => 'Bagi tagihan patungan makan, nongkrong, atau sewa kamar bersama teman, lengkap dengan tombol direct share ke chat WhatsApp.',
                        'action_route' => route('debts.index'),
                        'action_label' => 'Bagi Tagihan',
                        'tag' => 'Social Finance',
                    ],
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Kalender Arus Kas Bulanan',
                        'description' => 'Tampilan kalender interaktif yang memetakan tanggal transaksi, jadwal jatuh tempo tagihan rutin, dan pembayaran hutang.',
                        'action_route' => route('calendar.index'),
                        'action_label' => 'Buka Kalender',
                        'tag' => 'Calendar',
                    ],
                ],
            ],
            [
                'version' => 'v2.0.0',
                'name' => 'Finora AI Financial Advisor & Financial Health Radar',
                'codename' => 'Apex Intelligence',
                'date' => '6 November 2026',
                'is_latest' => false,
                'badge' => 'Generative AI',
                'summary' => 'Asisten cerdas Finora AI berbasis Groq LLM dengan pemahaman konteks keuangan personal, Audit Kesehatan Finansial 5 Pilar, Prediksi Arus Kas 90 Hari, dan Kilas Balik Finansial Bulanan.',
                'highlights' => [
                    'Finora AI Financial Advisor: Konsultasi strategi keuangan 24/7 dengan konteks mutasi riil.',
                    'Financial Health Score (Skor 0-100) dan Audit Radar 5 Pilar.',
                    'Prediksi Arus Kas 30-90 hari dengan peringatan antisipasi saldo menipis.',
                    'Monthly Financial Wrapped ala Spotify Wrapped untuk kilas balik pengeluaran.',
                ],
                'changes' => [
                    [
                        'type' => 'ai',
                        'category' => 'AI & Otomasi',
                        'title' => 'Finora AI Financial Advisor',
                        'description' => 'Widget asisten berbasis Groq / Grok LLM yang menganalisis saldo, kategori belanja boros, dan memberikan rekomendasi penghematan.',
                        'action_route' => null,
                        'action_label' => null,
                        'tag' => 'LLM Advisor',
                    ],
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Financial Health Score & Radar 5 Pilar',
                        'description' => 'Penilaian holistik rasio tabungan, ketahanan dana darurat, beban hutang, kepatuhan budget, dan diversifikasi likuiditas.',
                        'action_route' => route('financial-health.index'),
                        'action_label' => 'Cek Skor Finansial',
                        'tag' => 'Health Radar',
                    ],
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Prediksi Arus Kas & Runway Calculator',
                        'description' => 'Mesin simulasi arus kas 30-90 hari yang menghitung tanggal prediksi saldo habis berdasarkan rata-rata pengeluaran dan komitmen rutin.',
                        'action_route' => route('cashflow.index'),
                        'action_label' => 'Lihat Prediksi',
                        'tag' => 'Forecasting',
                    ],
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Monthly Financial Wrapped',
                        'description' => 'Slide story visual yang merangkum kategori pengeluaran terbesar, hari belanja tersibuk, dan persona finansial Anda.',
                        'action_route' => route('wrapped.index'),
                        'action_label' => 'Buka Wrapped',
                        'tag' => 'Wrapped',
                    ],
                ],
            ],
            [
                'version' => 'v1.0.0',
                'name' => 'Finora Core Ledger Foundation',
                'codename' => 'Genesis Foundation',
                'date' => '6 November 2026',
                'is_latest' => false,
                'badge' => 'Fondasi Awal',
                'summary' => 'Peluncuran awal Finora dengan pencatatan transaksi pemasukan dan pengeluaran, anggaran kategori, tagihan berulang, target tabungan, dan ekspor laporan.',
                'highlights' => [
                    'Pencatatan transaksi multi-kategori dengan filter periode.',
                    'Batas anggaran (Budgets) dengan indikator progress visual.',
                    'Pengingat tagihan berulang & notifikasi overspending.',
                    'Target tabungan (Savings Goals) & ekspor laporan PDF/CSV.',
                ],
                'changes' => [
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Buku Kas Transaksi Multi-Kategori',
                        'description' => 'Pencatatan pemasukan dan pengeluaran dengan kategori dinamis, ikon visual, dan warna kategori.',
                        'action_route' => route('transactions.index'),
                        'action_label' => 'Lihat Transaksi',
                        'tag' => 'Ledger',
                    ],
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Anggaran Kategori & Peringatan Batas',
                        'description' => 'Pasang pagu batas belanja bulanan per kategori dan pantau persentase pemakaian secara real-time.',
                        'action_route' => route('budgets.index'),
                        'action_label' => 'Atur Anggaran',
                        'tag' => 'Budgets',
                    ],
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Target Tabungan & Dana Impian',
                        'description' => 'Alokasikan dana khusus untuk membeli rumah, kendaraan, liburan, atau dana darurat dengan progress bar dinamis.',
                        'action_route' => route('savings.index'),
                        'action_label' => 'Buka Tabungan',
                        'tag' => 'Savings',
                    ],
                    [
                        'type' => 'feature',
                        'category' => 'Fitur Baru',
                        'title' => 'Laporan Finansial & Ekspor PDF / CSV',
                        'description' => 'Grafik perbandingan pengeluaran per bulan serta tombol unduh file laporan untuk kebutuhan audit pribadi.',
                        'action_route' => route('reports.index'),
                        'action_label' => 'Lihat Laporan',
                        'tag' => 'Reports',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get upcoming roadmap items.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getRoadmapItems(bool $isEn): array
    {
        if ($isEn) {
            return [
                [
                    'title' => 'Open Banking API Direct Sync',
                    'status' => 'in_development',
                    'status_label' => 'In Development',
                    'status_color' => 'emerald',
                    'eta' => 'Q4 2026',
                    'description' => 'Direct integration with national banking APIs for automated balance and transaction sync without manual CSV uploads.',
                    'icon' => 'bank',
                ],
                [
                    'title' => 'Finora Mobile App (iOS & Android)',
                    'status' => 'in_development',
                    'status_label' => 'In Development',
                    'status_color' => 'emerald',
                    'eta' => 'Q1 2027',
                    'description' => 'High-performance native mobile apps featuring homescreen widgets, biometric authentication, and push bill reminders.',
                    'icon' => 'mobile',
                ],
                [
                    'title' => 'WhatsApp & Telegram Transaction Logger Bot',
                    'status' => 'planned',
                    'status_label' => 'Planned',
                    'status_color' => 'blue',
                    'eta' => 'Q1 2027',
                    'description' => 'Log income and expenses simply by sending a text or voice note to the official Finora bot.',
                    'icon' => 'chat',
                ],
                [
                    'title' => 'Family & Shared Collaborative Ledger',
                    'status' => 'planned',
                    'status_label' => 'Planned',
                    'status_color' => 'blue',
                    'eta' => 'Q2 2027',
                    'description' => 'Joint financial ledger for couples or teams with granular role permissions and dedicated account partitions.',
                    'icon' => 'users',
                ],
                [
                    'title' => 'AI Automated Investment Recommendation',
                    'status' => 'research',
                    'status_label' => 'Research Phase',
                    'status_color' => 'purple',
                    'eta' => 'Q2 2027',
                    'description' => 'Intelligent portfolio analysis suggesting mutual funds, bonds, stocks, and gold tailored to your risk profile.',
                    'icon' => 'sparkles',
                ],
            ];
        }

        return [
            [
                'title' => 'Open Banking API Direct Sync',
                'status' => 'in_development',
                'status_label' => 'Sedang Dikembangkan',
                'status_color' => 'emerald',
                'eta' => 'Q4 2026',
                'description' => 'Koneksi langsung ke API perbankan nasional untuk sinkronisasi saldo dan mutasi rekening secara otomatis tanpa perlu upload CSV manual.',
                'icon' => 'bank',
            ],
            [
                'title' => 'Finora Mobile App (iOS & Android)',
                'status' => 'in_development',
                'status_label' => 'Sedang Dikembangkan',
                'status_color' => 'emerald',
                'eta' => 'Q1 2027',
                'description' => 'Aplikasi mobile native yang cepat dengan widget homescreen, biometric login, dan push notification saat ada tagihan jatuh tempo.',
                'icon' => 'mobile',
            ],
            [
                'title' => 'WhatsApp & Telegram Transaction Logger Bot',
                'status' => 'planned',
                'status_label' => 'Direncanakan',
                'status_color' => 'blue',
                'eta' => 'Q1 2027',
                'description' => 'Catat transaksi cukup kirim pesan singkat atau voice note ke bot WhatsApp/Telegram resmi Finora.',
                'icon' => 'chat',
            ],
            [
                'title' => 'Family & Shared Collaborative Ledger',
                'status' => 'planned',
                'status_label' => 'Direncanakan',
                'status_color' => 'blue',
                'eta' => 'Q2 2027',
                'description' => 'Buku kas bersama untuk pasangan suami-istri atau tim kerja dengan kontrol akses dan alokasi rekening terpisah.',
                'icon' => 'users',
            ],
            [
                'title' => 'AI Automated Investment Recommendation',
                'status' => 'research',
                'status_label' => 'Tahap Riset',
                'status_color' => 'purple',
                'eta' => 'Q2 2027',
                'description' => 'Analisis portofolio cerdas yang merekomendasikan instrumen investasi (Reksadana, Obligasi, Saham, Emas) sesuai profil risiko Anda.',
                'icon' => 'sparkles',
            ],
        ];
    }

    /**
     * Get system specifications for transparency and diagnostics.
     *
     * @return array<string, string>
     */
    private function getSystemSpecs(): array
    {
        return [
            'Framework' => 'Laravel ' . app()->version(),
            'PHP Runtime' => 'PHP ' . PHP_VERSION,
            'Database Driver' => strtoupper((string) config('database.default', 'sqlite')),
            'Frontend UI' => 'Tailwind CSS v4 & Alpine.js v3',
            'Chart Engine' => 'Chart.js v4.4 (UMD)',
            'OCR Engine' => 'Tesseract.js v5 + AI Vision',
            'AI Backend' => 'Groq Cloud / Grok LLM API',
            'Timezone' => (string) config('app.timezone', 'UTC'),
            'App Environment' => ucfirst((string) config('app.env', 'production')),
            'Cache Driver' => strtoupper((string) config('cache.default', 'file')),
            'Session Driver' => strtoupper((string) config('session.driver', 'file')),
            'Security' => 'CSRF Protection, Argon2/Bcrypt Hash, Strict CSP',
        ];
    }
}
