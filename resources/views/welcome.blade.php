<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Primary SEO Meta Tags -->
    <title>Finora – Take Control of Your Money, Build Your Future</title>
    <meta name="title" content="Finora – Take Control of Your Money, Build Your Future">
    <meta name="description" content="Finora adalah platform manajemen keuangan pribadi modern dengan AI Financial Advisor, pelacak anggaran real-time, Smart Quick-Add, pemindai kuitansi OCR, pencatat utang & tagihan, serta analitik komprehensif.">
    <meta name="keywords" content="finora, aplikasi keuangan, manajemen keuangan pribadi, pelacak anggaran, budget tracker, catat pengeluaran, ai financial advisor, financial health score, scanner struk ocr, pencatat utang, target tabungan">
    <meta name="author" content="Finora">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Facebook / WhatsApp / LinkedIn -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="Finora">
    <meta property="og:title" content="Finora – Take Control of Your Money, Build Your Future">
    <meta property="og:description" content="Kelola arus kas, anggaran bulanan, target tabungan, tagihan rutin, serta analisis kesehatan finansial Anda secara aman dan cerdas bersama Finora.">
    <meta property="og:image" content="{{ asset('images/logo-512.png') }}">
    <meta property="og:image:width" content="512">
    <meta property="og:image:height" content="512">
    <meta property="og:image:alt" content="Finora - Personal Finance Platform">
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) == 'id' ? 'id_ID' : 'en_US' }}">

    <!-- Twitter / X Cards -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="Finora – Take Control of Your Money, Build Your Future">
    <meta name="twitter:description" content="Kelola arus kas, anggaran bulanan, target tabungan, tagihan rutin, serta analisis kesehatan finansial Anda bersama Finora.">
    <meta name="twitter:image" content="{{ asset('images/logo-512.png') }}">

    <!-- Mobile Theme, PWA & Favicon -->
    <meta name="theme-color" content="#00c078">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Finora">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('images/logo-192.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <!-- Fonts & Assets Preload -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:300,400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Structured Data (JSON-LD Schema.org) -->
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebApplication',
                '@id' => url('/') . '#webapp',
                'name' => 'Finora',
                'url' => url('/'),
                'applicationCategory' => 'FinanceApplication',
                'operatingSystem' => 'All',
                'description' => 'Platform manajemen keuangan pribadi cerdas dengan asisten AI, pelacak anggaran, pencatat transaksi instan, pemindai kuitansi OCR, dan analitik keuangan komprehensif.',
                'image' => asset('images/logo-512.png'),
                'offers' => [
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'IDR',
                ],
                'featureList' => [
                    'Smart Quick-Add Transaction Logger',
                    'Budget Ceiling & Overspending Alerts',
                    'AI Financial Advisor',
                    'Financial Health Score & Audit',
                    'OCR Receipt Scanner',
                    'Multi-Wallet & Bank Reconciliation',
                    'Debt & Loan Settlement Tracker',
                    'Financial Wrapped Annual Retrospective',
                    'CSV & PDF Statement Export',
                ],
            ],
            [
                '@type' => 'Organization',
                '@id' => url('/') . '#organization',
                'name' => 'Finora',
                'url' => url('/'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset('images/logo-512.png'),
                ],
            ],
            [
                '@type' => 'FAQPage',
                '@id' => url('/') . '#faq',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'Is Finora completely free to use?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Yes, Finora is free with zero subscription fees, hidden charges, or payment gating. You have unrestricted access to all features, including budget health, goal tracking, and PDF reports.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'How secure is my personal financial data?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Your data is guarded by strict Laravel Authorization Policies. No other user can ever query or view your transactions. Passwords are encrypted with Bcrypt (12 rounds) and transactions use strict decimal precision.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Can I customize my transaction categories?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Yes! Upon registration, Finora initializes 8 standard categories for you. You can add, edit, or delete as many custom categories for income, expense, and savings as you need.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'How do the budget spending ceilings work?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'You configure a ceiling amount for any category and timeframe. As you log expenses, Finora computes consumed percentages in real time and automatically warns you with a bright NEAR LIMIT! indicator once you cross 90%.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Can I export my financial data for tax or bookkeeping?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Yes. Finora includes a dedicated Financial Reports module allowing you to export filtered statement ledgers into structured CSV spreadsheets or print-ready PDF audit sheets with a single click.',
                        ],
                    ],
                ],
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    <script>
        (function () {
            const savedTheme = localStorage.getItem('finora_theme') || 'light';
            if (savedTheme === 'dark' || (savedTheme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
</head>
<body class="font-sans antialiased bg-[#fbfcfd] text-gray-900 selection:bg-[#00c078] selection:text-white overflow-x-hidden w-full max-w-full" x-data="{ mobileMenuOpen: false }">

    <!-- ============================================================== -->
    <!-- NAVIGATION BAR                                                 -->
    <!-- ============================================================== -->
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-xs transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 sm:h-24 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3.5 group">
                <img src="{{ asset('images/logo.jpg') }}" alt="Finora" width="44" height="44" style="width: 44px; height: 44px; object-fit: cover;" class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl object-cover shadow-xs ring-1 ring-gray-100 group-hover:scale-105 transition-transform" onerror="this.style.display='none'">
                <span class="text-2xl sm:text-3xl font-black tracking-tight text-gray-900 group-hover:text-gray-800 transition-colors">Finora</span>
            </a>

            <!-- Desktop Menu Links -->
            <nav class="hidden md:flex items-center gap-10 text-[15px] font-bold text-gray-600">
                <a href="#features" class="hover:text-[#00c078] transition-colors py-1">Features</a>
                <a href="#how-it-works" class="hover:text-[#00c078] transition-colors py-1">How It Works</a>
                <a href="#showcase" class="hover:text-[#00c078] transition-colors py-1">Showcase</a>
                <a href="#faq" class="hover:text-[#00c078] transition-colors py-1">FAQ</a>
            </nav>

            <!-- Auth Buttons -->
            <div class="hidden sm:flex items-center gap-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#00c078] hover:bg-[#00a868] text-white text-sm font-bold rounded-xl shadow-md shadow-[#00c078]/25 hover:shadow-lg hover:shadow-[#00c078]/30 transition-all">
                            <span>Go to Dashboard</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2.5 text-sm font-bold text-gray-700 hover:text-[#00c078] transition-colors">
                            Login
                        </a>
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#00c078] hover:bg-[#00a868] text-white text-sm font-bold rounded-xl shadow-md shadow-[#00c078]/25 hover:shadow-lg hover:shadow-[#00c078]/30 hover:-translate-y-0.5 transition-all">
                            <span>Get Started Free</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @endauth
                @endif
            </div>

            <!-- Mobile Menu Toggle -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2.5 rounded-xl border border-gray-200 text-gray-700 hover:text-gray-900 hover:bg-gray-50 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="mobileMenuOpen ? 'M6 18L18 6M6 6l12 12' : 'M4 6h16M4 12h16M4 18h16'"/>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="md:hidden border-b border-gray-100 bg-white px-5 py-5 space-y-3.5 shadow-lg">
            <a href="#features" @click="mobileMenuOpen = false" class="block py-2 text-base font-bold text-gray-800 hover:text-[#00c078]">Features</a>
            <a href="#how-it-works" @click="mobileMenuOpen = false" class="block py-2 text-base font-bold text-gray-800 hover:text-[#00c078]">How It Works</a>
            <a href="#showcase" @click="mobileMenuOpen = false" class="block py-2 text-base font-bold text-gray-800 hover:text-[#00c078]">Showcase</a>
            <a href="#faq" @click="mobileMenuOpen = false" class="block py-2 text-base font-bold text-gray-800 hover:text-[#00c078]">FAQ</a>
            <div class="pt-4 border-t border-gray-100 flex flex-col gap-2.5">
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full text-center py-3 bg-[#00c078] text-white text-sm font-bold rounded-xl shadow-xs">Go to Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="w-full text-center py-2.5 text-sm font-bold text-gray-700 border border-gray-200 rounded-xl hover:bg-gray-50">Login</a>
                    <a href="{{ route('register') }}" class="w-full text-center py-3 bg-[#00c078] text-white text-sm font-bold rounded-xl shadow-md shadow-[#00c078]/20">Get Started Free</a>
                @endauth
            </div>
        </div>
    </header>


    <!-- ============================================================== -->
    <!-- HERO SECTION                                                   -->
    <!-- ============================================================== -->
    <section class="relative pt-12 pb-20 sm:pt-16 sm:pb-24 lg:pt-20 lg:pb-32 overflow-hidden">
        <!-- Subtle Glow Background -->
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-96 bg-gradient-to-b from-emerald-50/70 via-teal-50/20 to-transparent pointer-events-none -z-10"></div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Pill Tag -->
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-100/80 text-[#00c078] text-[11px] font-bold tracking-wide uppercase mb-6 shadow-2xs">
                <span>NEW RELEASE v2.4</span>
                <span class="text-emerald-300">•</span>
                <span class="text-emerald-700">Real-Time Sync & Multi-Asset Ledger</span>
            </div>

            <!-- Main Heading -->
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-gray-900 tracking-tight leading-[1.12] mb-6">
                Take Control of Your Money,<br class="hidden sm:inline">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900">Build Your Future.</span>
            </h1>

            <!-- Subheading -->
            <p class="max-w-2xl mx-auto text-sm sm:text-base text-gray-500 leading-relaxed mb-8">
                Finora gives you a real-time financial command center over your budgets, cash flow, and savings goals. Built specifically for modern individuals looking for institutional-grade clarity and personal security.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 mb-10">
                <a href="{{ route('register') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-[#00c078] hover:bg-[#00a868] text-white text-sm font-bold rounded-2xl shadow-lg shadow-[#00c078]/25 transition-all duration-150">
                    Get Started Free
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
                <a href="#features"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 text-sm font-bold rounded-2xl shadow-2xs transition-all">
                    Explore Features
                </a>
            </div>

            <!-- Trust Points -->
            <div class="flex flex-wrap items-center justify-center gap-6 text-xs font-semibold text-gray-500 mb-14">
                <span class="flex items-center gap-1.5 text-gray-600">
                    <svg class="w-4 h-4 text-[#00c078]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    100% Free & Open-Source
                </span>
                <span class="flex items-center gap-1.5 text-gray-600">
                    <svg class="w-4 h-4 text-[#00c078]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Bank-Grade AES-256 Encryption
                </span>
                <span class="flex items-center gap-1.5 text-gray-600">
                    <svg class="w-4 h-4 text-[#00c078]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Zero Hidden Fees
                </span>
            </div>

            <!-- ============================================================== -->
            <!-- HERO MOCKUP CARD (GOOGLE STITCH UI WORKSPACE)                  -->
            <!-- ============================================================== -->
            <div class="relative mx-auto max-w-5xl rounded-2xl lg:rounded-3xl p-2 sm:p-3 bg-gradient-to-b from-gray-200/60 to-gray-100/40 shadow-2xl shadow-emerald-500/10 border border-gray-200/80">
                <div class="bg-white rounded-xl lg:rounded-2xl border border-gray-100 overflow-hidden text-left">
                    <!-- Browser Window Header -->
                    <div class="px-4 py-3 bg-gray-50/80 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-red-400"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                            <span class="ml-3 text-[11px] font-mono text-gray-400 hidden sm:inline-block">https://finora.app/dashboard</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#00c078] animate-pulse"></span>
                            <span class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">Live Sync Active</span>
                        </div>
                    </div>

                    <!-- Inner Mockup Dashboard Content -->
                    <div class="p-4 sm:p-6 lg:p-7 space-y-5 bg-[#fbfcfd]">
                        <!-- Metrics Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs">
                                <div class="flex items-center justify-between text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">
                                    <span>Total Balance</span>
                                    <span class="text-[#00c078]">+14.2%</span>
                                </div>
                                <p class="text-xl font-black text-gray-900">Rp12.000.000</p>
                                <p class="text-[10px] text-gray-400 mt-1">Vs last month (Rp10.500.000)</p>
                            </div>
                            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs">
                                <div class="flex items-center justify-between text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">
                                    <span>Total Inflow</span>
                                    <span class="text-[#00c078]">+8.5%</span>
                                </div>
                                <p class="text-xl font-black text-gray-900">Rp5.000.000</p>
                                <p class="text-[10px] text-gray-400 mt-1">2 verified salary & project entries</p>
                            </div>
                            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs">
                                <div class="flex items-center justify-between text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">
                                    <span>Expenses This Month</span>
                                    <span class="text-red-500">-3.1%</span>
                                </div>
                                <p class="text-xl font-black text-gray-900">Rp2.750.000</p>
                                <p class="text-[10px] text-gray-400 mt-1">55% of monthly income ceiling</p>
                            </div>
                        </div>

                        <!-- Split Cards: Charts & Budget -->
                        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
                            <!-- Trajectory Chart Graphic -->
                            <div class="lg:col-span-3 bg-white p-4 rounded-xl border border-gray-100 shadow-2xs">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold text-gray-900">Historical Cash Flow Trajectory</span>
                                    <div class="flex items-center gap-3 text-[10px] font-semibold text-gray-500">
                                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#00c078]"></span> Inflow</span>
                                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-[#ff7360]"></span> Outflow</span>
                                    </div>
                                </div>
                                <!-- Vector SVG Trajectory Curve -->
                                <div class="h-32 w-full pt-2">
                                    <svg class="w-full h-full" viewBox="0 0 500 120" fill="none" preserveAspectRatio="none">
                                        <!-- Gradient defs -->
                                        <defs>
                                            <linearGradient id="heroInc" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="0%" stop-color="#00c078" stop-opacity="0.25"/>
                                                <stop offset="100%" stop-color="#00c078" stop-opacity="0.0"/>
                                            </linearGradient>
                                        </defs>
                                        <path d="M0,80 Q100,70 200,50 T400,25 T500,20 L500,120 L0,120 Z" fill="url(#heroInc)"/>
                                        <path d="M0,80 Q100,70 200,50 T400,25 T500,20" stroke="#00c078" stroke-width="3" stroke-linecap="round"/>
                                        <path d="M0,95 Q120,90 220,80 T400,60 T500,55" stroke="#ff7360" stroke-width="2.5" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <div class="flex justify-between text-[10px] text-gray-400 font-bold uppercase mt-2 pt-2 border-t border-gray-50">
                                    <span>May</span><span>Jun</span><span>Jul</span><span>Aug</span><span>Sep</span><span class="text-[#00c078]">Oct 2026</span>
                                </div>
                            </div>

                            <!-- Budget & Goals Preview -->
                            <div class="lg:col-span-2 bg-white p-4 rounded-xl border border-gray-100 shadow-2xs flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-bold text-gray-900">Budget Health</span>
                                        <span class="text-[10px] text-[#00c078] font-bold">Caps Active</span>
                                    </div>
                                    <div class="space-y-2 text-[11px]">
                                        <div>
                                            <div class="flex justify-between text-gray-700 font-semibold mb-1">
                                                <span>Food & Dining</span>
                                                <span class="text-gray-900 font-bold">Rp962.500 / Rp1.2m</span>
                                            </div>
                                            <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-amber-500 h-full rounded-full" style="width: 80%"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex justify-between text-gray-700 font-semibold mb-1">
                                                <span>Transportation</span>
                                                <span class="text-gray-900 font-bold">Rp350.000 / Rp500k</span>
                                            </div>
                                            <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-[#00c078] h-full rounded-full" style="width: 70%"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="flex justify-between text-gray-700 font-semibold mb-1">
                                                <span class="text-red-500">Bills & Utilities</span>
                                                <span class="text-red-600 font-bold">NEAR LIMIT</span>
                                            </div>
                                            <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                                <div class="bg-red-500 h-full rounded-full" style="width: 98%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-t border-gray-50 flex items-center justify-between text-[11px]">
                                    <span class="text-gray-500 font-semibold">MacBook Pro M3 Fund</span>
                                    <span class="text-[#00c078] font-bold">75% Achieved</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ============================================================== -->
    <!-- FEATURES GRID (EVERYTHING YOU NEED TO MASTER YOUR MONEY)       -->
    <!-- ============================================================== -->
    <section id="features" class="py-20 lg:py-28 bg-white border-y border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-14">
                <div>
                    <span class="text-[11px] font-black text-[#00c078] uppercase tracking-widest block mb-2">Powerful Modules</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Everything You Need to Master Your Money</h2>
                    <p class="text-sm text-gray-500 max-w-2xl mt-2">
                        From transaction tracking to automated budget ceilings and deep financial analytics. Every single feature is built with precision, speed, and privacy at its core.
                    </p>
                </div>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-600 self-start md:self-auto">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Encrypted & Isolated User Sessions
                </div>
            </div>

            <!-- 4 Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                <!-- Feature 1 -->
                <div class="p-6 sm:p-8 rounded-2xl border border-gray-100 bg-[#fbfcfd] hover:shadow-lg hover:shadow-gray-200/50 transition-all flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-[#00c078] flex items-center justify-center mb-5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Smart Transaction Tracking</h3>
                        <p class="text-xs sm:text-sm text-gray-500 leading-relaxed mb-6">
                            Comprehensive multi-currency support, Bank BCA, GoPay, QRIS, and cash ledgering with intelligent category suggestions, recurring income schedules, and payment metadata tags.
                        </p>
                    </div>
                    <!-- Mini visual preview -->
                    <div class="bg-white p-3.5 rounded-xl border border-gray-100 shadow-2xs space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#00c078]"></span>
                                <span class="font-bold text-gray-800">Salary Deposit • PT Tech</span>
                            </div>
                            <span class="font-black text-[#00c078]">+Rp5.000.000</span>
                        </div>
                        <div class="flex items-center justify-between text-xs pt-1.5 border-t border-gray-50">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-red-400"></span>
                                <span class="font-medium text-gray-600">Superindo Groceries • QRIS</span>
                            </div>
                            <span class="font-bold text-gray-900">-Rp350.000</span>
                        </div>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="p-6 sm:p-8 rounded-2xl border border-gray-100 bg-[#fbfcfd] hover:shadow-lg hover:shadow-gray-200/50 transition-all flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-[#00c078] flex items-center justify-center mb-5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Budget Management & Ceilings</h3>
                        <p class="text-xs sm:text-sm text-gray-500 leading-relaxed mb-6">
                            Set monthly ceilings per category. Monitor progress with real-time alerts when spending approaches 90% and prevent overshooting before it happens.
                        </p>
                    </div>
                    <!-- Mini visual preview -->
                    <div class="bg-white p-3.5 rounded-xl border border-gray-100 shadow-2xs space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold text-gray-800">
                            <span>Monthly Food Cap</span>
                            <span class="text-amber-600">80% Consumed</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-amber-500 h-full rounded-full" style="width: 80%"></div>
                        </div>
                        <div class="flex justify-between text-[11px] text-gray-400">
                            <span>Rp962.500 spent</span>
                            <span>Rp237.500 remaining limit</span>
                        </div>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="p-6 sm:p-8 rounded-2xl border border-gray-100 bg-[#fbfcfd] hover:shadow-lg hover:shadow-gray-200/50 transition-all flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-[#00c078] flex items-center justify-center mb-5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Financial Insights & Analytics</h3>
                        <p class="text-xs sm:text-sm text-gray-500 leading-relaxed mb-6">
                            Real-time breakdown of spending by expense category, 12-month trajectory cash flow, and exportable statements in formatted CSV and print-ready PDF audit sheets.
                        </p>
                    </div>
                    <!-- Mini visual preview -->
                    <div class="bg-white p-3.5 rounded-xl border border-gray-100 shadow-2xs flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full border-4 border-[#00c078] flex items-center justify-center text-[10px] font-black text-gray-900">
                                45%
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-900">Net Savings Surplus</p>
                                <p class="text-[10px] text-gray-400">Target recommended ≥ 20%</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg bg-emerald-50 text-[#00c078] text-[11px] font-bold">AUDIT OK</span>
                    </div>
                </div>

                <!-- Feature 4 -->
                <div class="p-6 sm:p-8 rounded-2xl border border-gray-100 bg-[#fbfcfd] hover:shadow-lg hover:shadow-gray-200/50 transition-all flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-[#00c078] flex items-center justify-center mb-5">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Goal & Milestone Vaults</h3>
                        <p class="text-xs sm:text-sm text-gray-500 leading-relaxed mb-6">
                            Create target-oriented saving vaults for tech upgrades, emergency funds, vacations, or investments with inline contributions and progress indicators.
                        </p>
                    </div>
                    <!-- Mini visual preview -->
                    <div class="bg-white p-3.5 rounded-xl border border-gray-100 shadow-2xs space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-gray-900">MacBook Pro M3 Fund</span>
                            <span class="text-[#00c078]">Rp18.750.000 / Rp25m</span>
                        </div>
                        <div class="w-full bg-emerald-50 rounded-full h-2 overflow-hidden">
                            <div class="bg-[#00c078] h-full rounded-full" style="width: 75%"></div>
                        </div>
                        <div class="flex justify-between items-center text-[10px] text-gray-400">
                            <span class="inline-flex items-center gap-1">
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Approx. 3 months away</span>
                            </span>
                            <span class="px-2 py-0.5 rounded bg-gray-50 text-gray-700 font-bold border border-gray-200">+ Add Funds</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ============================================================== -->
    <!-- HOW IT WORKS (THREE SIMPLE STEPS)                              -->
    <!-- ============================================================== -->
    <section id="how-it-works" class="py-20 lg:py-28 bg-[#fbfcfd]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-[11px] font-black text-[#00c078] uppercase tracking-widest block mb-2">How It Works</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Three Simple Steps to Financial Clarity</h2>
                <p class="text-sm text-gray-500 mt-2">No complicated setup or linked account credentials. Start tracking in under 60 seconds with total privacy.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                <!-- Step 1 -->
                <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-gray-900 text-white flex items-center justify-center text-lg font-black mb-6">
                            01
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Create Your Account</h3>
                        <p class="text-xs sm:text-sm text-gray-500 leading-relaxed mb-6">
                            Register in seconds with just your name and email. Finora automatically sets up your initial tailored categories for immediate logging.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-gray-50 flex items-center gap-2 text-xs font-semibold text-emerald-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Zero credit card required
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-[#00c078] text-white flex items-center justify-center text-lg font-black mb-6 shadow-sm shadow-[#00c078]/30">
                            02
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Log Your Transactions</h3>
                        <p class="text-xs sm:text-sm text-gray-500 leading-relaxed mb-6">
                            Record your daily income and expenditures with category tagging, payment methods (QRIS, bank transfer, e-wallet), and date filters.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-gray-50 flex items-center gap-2 text-xs font-semibold text-emerald-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Quick entry in under 10 seconds
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="bg-white p-8 rounded-2xl border border-gray-100 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-gray-900 text-white flex items-center justify-center text-lg font-black mb-6">
                            03
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Review & Optimize</h3>
                        <p class="text-xs sm:text-sm text-gray-500 leading-relaxed mb-6">
                            Monitor budget health, track your savings vault milestones, and export print-ready PDF statements to achieve true financial independence.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-gray-50 flex items-center gap-2 text-xs font-semibold text-emerald-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Real-time automated calculations
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ============================================================== -->
    <!-- INTERACTIVE SHOWCASE (FINORA COMMAND CENTER IN ACTION)         -->
    <!-- ============================================================== -->
    <section id="showcase" class="py-20 lg:py-28 bg-white border-y border-gray-100" x-data="{ tab: 'overview' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <span class="text-[11px] font-black text-[#00c078] uppercase tracking-widest block mb-2">Live Dashboard Preview</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Finora Command Center in Action</h2>
                <p class="text-sm text-gray-500 mt-2">Experience the clean, high-density financial workspace engineered for modern professionals.</p>
            </div>

            <!-- Tab Switcher Navigation -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-100">
                <div class="flex items-center gap-1 sm:gap-2 bg-gray-50 p-1 rounded-xl text-xs font-bold text-gray-600 overflow-x-auto max-w-full">
                    <button @click="tab = 'overview'"
                            :class="tab === 'overview' ? 'bg-white text-gray-900 shadow-xs' : 'hover:text-gray-900'"
                            class="whitespace-nowrap shrink-0 px-3 sm:px-4 py-2 rounded-lg transition-all">Overview</button>
                    <button @click="tab = 'transactions'"
                            :class="tab === 'transactions' ? 'bg-white text-gray-900 shadow-xs' : 'hover:text-gray-900'"
                            class="whitespace-nowrap shrink-0 px-3 sm:px-4 py-2 rounded-lg transition-all">Transaction Ledger</button>
                    <button @click="tab = 'budgets'"
                            :class="tab === 'budgets' ? 'bg-white text-gray-900 shadow-xs' : 'hover:text-gray-900'"
                            class="whitespace-nowrap shrink-0 px-3 sm:px-4 py-2 rounded-lg transition-all">Budget Limits</button>
                    <button @click="tab = 'milestones'"
                            :class="tab === 'milestones' ? 'bg-white text-gray-900 shadow-xs' : 'hover:text-gray-900'"
                            class="whitespace-nowrap shrink-0 px-3 sm:px-4 py-2 rounded-lg transition-all">Milestones</button>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto text-center px-3.5 py-2 bg-gray-950 hover:bg-black text-white text-xs font-bold rounded-xl shadow-xs transition-colors">
                        Launch Live App
                    </a>
                </div>
            </div>

            <!-- Showcase Display Card -->
            <div class="bg-[#fbfcfd] border border-gray-200/80 rounded-2xl p-6 lg:p-8 shadow-sm">
                <!-- 4 Metrics Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs">
                        <span class="text-[10px] uppercase font-bold text-gray-400">Monthly Inflow</span>
                        <p class="text-xl font-black text-gray-900 mt-0.5">Rp5.340.000</p>
                        <span class="text-[10px] text-[#00c078] font-bold">100% of cycle target</span>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs">
                        <span class="text-[10px] uppercase font-bold text-gray-400">Total Spend</span>
                        <p class="text-xl font-black text-gray-900 mt-0.5">Rp1.160.000</p>
                        <span class="text-[10px] text-gray-400">22% of total inflow</span>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs">
                        <span class="text-[10px] uppercase font-bold text-gray-400">Net Surplus</span>
                        <p class="text-xl font-black text-[#00c078] mt-0.5">Rp3.000.000</p>
                        <span class="text-[10px] text-[#00c078] font-bold">+18.4% surplus growth</span>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-2xs">
                        <span class="text-[10px] uppercase font-bold text-gray-400">Vault Balance</span>
                        <p class="text-xl font-black text-gray-900 mt-0.5">Rp12.500.000</p>
                        <span class="text-[10px] text-gray-400">Across 3 active goals</span>
                    </div>
                </div>

                <!-- Showcase Content Views -->
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                    <!-- Left: Verified Entries -->
                    <div class="lg:col-span-3 bg-white p-5 rounded-xl border border-gray-100 shadow-2xs">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-bold text-gray-900">Verified Ledger Entries</h4>
                            <span class="text-[11px] text-[#00c078] font-bold">AUDIT OK</span>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-xs p-2.5 rounded-lg bg-gray-50/70 border border-gray-100">
                                <div>
                                    <p class="font-bold text-gray-900">Salary Deposit • PT Tech Indonesia</p>
                                    <p class="text-[10px] text-gray-400">Bank BCA • 25 Oct 2026</p>
                                </div>
                                <span class="font-black text-[#00c078]">+Rp5.000.000</span>
                            </div>
                            <div class="flex items-center justify-between text-xs p-2.5 rounded-lg bg-gray-50/70 border border-gray-100">
                                <div>
                                    <p class="font-bold text-gray-900">PLN Electricity & Water Utility</p>
                                    <p class="text-[10px] text-gray-400">Auto-Debit BCA • 20 Oct 2026</p>
                                </div>
                                <span class="font-bold text-gray-900">-Rp450.000</span>
                            </div>
                            <div class="flex items-center justify-between text-xs p-2.5 rounded-lg bg-gray-50/70 border border-gray-100">
                                <div>
                                    <p class="font-bold text-gray-900">Superindo Grocery Monthly Stock</p>
                                    <p class="text-[10px] text-gray-400">GoPay QRIS • 18 Oct 2026</p>
                                </div>
                                <span class="font-bold text-gray-900">-Rp350.000</span>
                            </div>
                            <div class="flex items-center justify-between text-xs p-2.5 rounded-lg bg-gray-50/70 border border-gray-100">
                                <div>
                                    <p class="font-bold text-gray-900">MRT Jakarta Pass Replenishment</p>
                                    <p class="text-[10px] text-gray-400">JakLingko • 15 Oct 2026</p>
                                </div>
                                <span class="font-bold text-gray-900">-Rp120.000</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Vault Milestones -->
                    <div class="lg:col-span-2 bg-white p-5 rounded-xl border border-gray-100 shadow-2xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-bold text-gray-900">MacBook Pro M3 Fund</h4>
                                <span class="text-xs font-black text-[#00c078]">75%</span>
                            </div>
                            <p class="text-xs text-gray-400 mb-4">High Priority • Tech Equipment Target</p>
                            <div class="flex justify-between text-xs mb-1.5 font-bold">
                                <span class="text-gray-900">Rp18.750.000</span>
                                <span class="text-gray-400">Goal: Rp25.000.000</span>
                            </div>
                            <div class="w-full bg-emerald-50 rounded-full h-2.5 overflow-hidden">
                                <div class="bg-[#00c078] h-full rounded-full" style="width: 75%"></div>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-2 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Rp6.250.000 left • Approx. 3 months away</span>
                            </p>
                        </div>
                        <a href="{{ route('register') }}"
                           class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl transition-colors mt-6">
                            <span>Assign Funds in Live App</span>
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ============================================================== -->
    <!-- WHY MODERN PROFESSIONALS CHOOSE FINORA                         -->
    <!-- ============================================================== -->
    <section class="py-20 lg:py-28 bg-[#fbfcfd]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mb-14">
                <span class="text-[11px] font-black text-[#00c078] uppercase tracking-widest block mb-2">Engineered for Privacy</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Why Modern Professionals Choose Finora</h2>
                <p class="text-sm text-gray-500 mt-2">Built for users who care about speed, visual simplicity, and full ownership of their financial records.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Point 1 -->
                <div class="p-6 bg-white rounded-2xl border border-gray-100 shadow-2xs">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#00c078] flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-2">Isolated Data Ownership</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Each account operates in complete logical isolation. Your financial figures are accessible strictly by you and never aggregated or shared.
                    </p>
                </div>

                <!-- Point 2 -->
                <div class="p-6 bg-white rounded-2xl border border-gray-100 shadow-2xs">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#00c078] flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-2">Zero Credential Sharing</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Unlike traditional open-banking apps that require your online banking credentials, Finora never asks for bank passwords or API access tokens.
                    </p>
                </div>

                <!-- Point 3 -->
                <div class="p-6 bg-white rounded-2xl border border-gray-100 shadow-2xs">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#00c078] flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-2">High-Density Design</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Crafted with inspiration from modern command center UIs, presenting vital financial indicators without clutter or unnecessary animations.
                    </p>
                </div>

                <!-- Point 4 -->
                <div class="p-6 bg-white rounded-2xl border border-gray-100 shadow-2xs">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#00c078] flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-2">Offline & Export Ready</h3>
                    <p class="text-xs text-gray-500 leading-relaxed">
                        Complete control over your data with one-click full CSV database dumps and official printable PDF financial statement audit sheets.
                    </p>
                </div>
            </div>
        </div>
    </section>


    <!-- ============================================================== -->
    <!-- FAQ ACCORDION                                                  -->
    <!-- ============================================================== -->
    <section id="faq" class="py-20 lg:py-28 bg-white border-t border-gray-100" x-data="{ activeFaq: null }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <span class="text-[11px] font-black text-[#00c078] uppercase tracking-widest block mb-2">Frequently Asked Questions</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Clear Answers to Honest Inquiries</h2>
                <p class="text-sm text-gray-500 mt-2">Everything you need to know about Finora's privacy, data security, and capabilities.</p>
            </div>

            <div class="space-y-3">
                <!-- FAQ Item 1 -->
                <div class="border border-gray-200 rounded-2xl overflow-hidden transition-all">
                    <button @click="activeFaq = activeFaq === 1 ? null : 1"
                            class="w-full px-6 py-4 text-left font-bold text-gray-900 text-sm flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <span>Is Finora completely free to use?</span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="activeFaq === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="activeFaq === 1" x-collapse class="px-6 pb-4 pt-1 text-xs text-gray-600 leading-relaxed border-t border-gray-50 bg-gray-50/50">
                        Yes, Finora is free with zero subscription fees, hidden charges, or payment gating. You have unrestricted access to all features, including budget health, goal tracking, and PDF reports.
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="border border-gray-200 rounded-2xl overflow-hidden transition-all">
                    <button @click="activeFaq = activeFaq === 2 ? null : 2"
                            class="w-full px-6 py-4 text-left font-bold text-gray-900 text-sm flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <span>How secure is my personal financial data?</span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="activeFaq === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="activeFaq === 2" x-collapse class="px-6 pb-4 pt-1 text-xs text-gray-600 leading-relaxed border-t border-gray-50 bg-gray-50/50">
                        Your data is guarded by strict Laravel Authorization Policies. No other user can ever query or view your transactions. Passwords are encrypted with Bcrypt (12 rounds) and transactions use strict decimal precision.
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="border border-gray-200 rounded-2xl overflow-hidden transition-all">
                    <button @click="activeFaq = activeFaq === 3 ? null : 3"
                            class="w-full px-6 py-4 text-left font-bold text-gray-900 text-sm flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <span>Can I customize my transaction categories?</span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="activeFaq === 3 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="activeFaq === 3" x-collapse class="px-6 pb-4 pt-1 text-xs text-gray-600 leading-relaxed border-t border-gray-50 bg-gray-50/50">
                        Yes! Upon registration, Finora initializes 8 standard categories for you. You can add, edit, or delete as many custom categories for income, expense, and savings as you need.
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="border border-gray-200 rounded-2xl overflow-hidden transition-all">
                    <button @click="activeFaq = activeFaq === 4 ? null : 4"
                            class="w-full px-6 py-4 text-left font-bold text-gray-900 text-sm flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <span>How do the budget spending ceilings work?</span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="activeFaq === 4 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="activeFaq === 4" x-collapse class="px-6 pb-4 pt-1 text-xs text-gray-600 leading-relaxed border-t border-gray-50 bg-gray-50/50">
                        You configure a ceiling amount for any category and timeframe. As you log expenses, Finora computes consumed percentages in real time and automatically warns you with a bright "NEAR LIMIT!" indicator once you cross 90%.
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div class="border border-gray-200 rounded-2xl overflow-hidden transition-all">
                    <button @click="activeFaq = activeFaq === 5 ? null : 5"
                            class="w-full px-6 py-4 text-left font-bold text-gray-900 text-sm flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <span>Can I export my financial data for tax or bookkeeping?</span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="activeFaq === 5 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="activeFaq === 5" x-collapse class="px-6 pb-4 pt-1 text-xs text-gray-600 leading-relaxed border-t border-gray-50 bg-gray-50/50">
                        Yes. Finora includes a dedicated Financial Reports module allowing you to export filtered statement ledgers into structured CSV spreadsheets or print-ready PDF audit sheets with a single click.
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ============================================================== -->
    <!-- BOTTOM CTA BANNER                                              -->
    <!-- ============================================================== -->
    <section class="py-16 sm:py-20 lg:py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative rounded-3xl bg-[#0b1320] text-white p-8 sm:p-12 lg:p-16 overflow-hidden shadow-2xl">
                <!-- Background Subtle Glow -->
                <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-[#00c078]/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 max-w-2xl">
                    <span class="text-[11px] font-black text-[#00c078] tracking-widest uppercase block mb-3">Get Started in Under 60 Seconds</span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight mb-4">
                        Start Building Better Financial Habits Today.
                    </h2>
                    <p class="text-sm text-gray-300 leading-relaxed mb-8">
                        Join individuals who have taken full control over their cash flow, savings vaults, and budget allocations with Finora.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <a href="{{ route('register') }}"
                           class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-[#00c078] hover:bg-[#00a868] text-white text-sm font-bold rounded-2xl shadow-lg shadow-[#00c078]/30 transition-all">
                            Get Started Free
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                        <a href="{{ route('login') }}"
                           class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3.5 bg-white/5 hover:bg-white/10 border border-gray-700 text-white text-sm font-bold rounded-2xl transition-all">
                            Sign In to Account
                        </a>
                    </div>

                    <div class="flex flex-wrap items-center gap-6 mt-8 text-xs font-semibold text-gray-400">
                        <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-[#00c078]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> 100% Free Forever</span>
                        <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-[#00c078]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> No Card Required</span>
                        <span class="flex items-center gap-1.5"><svg class="w-4 h-4 text-[#00c078]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg> Setup in 60 Seconds</span>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- ============================================================== -->
    <!-- FOOTER                                                         -->
    <!-- ============================================================== -->
    <footer class="bg-white border-t border-gray-100 py-12 text-xs text-gray-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-8 mb-10">
                <!-- Col 1: Brand -->
                <div class="md:col-span-2 space-y-4">
                    <a href="/" class="flex items-center gap-2.5">
                        <img src="{{ asset('images/logo.jpg') }}" alt="Finora" class="w-7 h-7 rounded-lg object-cover shadow-xs" onerror="this.style.display='none'">
                        <span class="text-xl font-black tracking-tight text-gray-900">Finora</span>
                    </a>
                    <p class="text-xs text-gray-500 max-w-sm leading-relaxed">
                        Institutional-grade personal finance tracking, budget constraints, and milestone savings vaults for clarity and peace of mind.
                    </p>
                    <div class="flex items-center gap-2 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg w-max">
                        <span class="w-2 h-2 rounded-full bg-[#00c078] animate-pulse"></span>
                        <span>All Systems Operational</span>
                    </div>
                </div>

                <!-- Col 2: Solutions -->
                <div>
                    <p class="font-bold text-gray-900 uppercase tracking-wider text-[11px] mb-3">Solutions</p>
                    <ul class="space-y-2">
                        <li><a href="{{ route('register') }}" class="hover:text-gray-900">Dashboard Command</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-gray-900">Transaction Ledger</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-gray-900">Budget Health Caps</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-gray-900">Savings Vaults</a></li>
                    </ul>
                </div>

                <!-- Col 3: Company -->
                <div>
                    <p class="font-bold text-gray-900 uppercase tracking-wider text-[11px] mb-3">Company</p>
                    <ul class="space-y-2">
                        <li><a href="#features" class="hover:text-gray-900">About Finora</a></li>
                        <li><a href="#how-it-works" class="hover:text-gray-900">Architecture</a></li>
                        <li><a href="#faq" class="hover:text-gray-900">Security Policies</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-gray-900">Sign In</a></li>
                    </ul>
                </div>

                <!-- Col 4: Resources -->
                <div>
                    <p class="font-bold text-gray-900 uppercase tracking-wider text-[11px] mb-3">Resources</p>
                    <ul class="space-y-2">
                        <li><a href="#faq" class="hover:text-gray-900">FAQ & Documentation</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-gray-900">Release Notes v2.4</a></li>
                        <li><a href="#features" class="hover:text-gray-900">Audit Statements</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Copyright Bar -->
            <div class="pt-6 border-t border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4 text-gray-400">
                <p>© {{ date('Y') }} Finora Technologies. All rights reserved.</p>
                <div class="flex items-center gap-6">
                    <a href="#faq" class="hover:text-gray-600">Privacy Policy</a>
                    <a href="#faq" class="hover:text-gray-600">Terms of Service</a>
                    <a href="#faq" class="hover:text-gray-600">Security Architecture</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
