<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <!-- Top Command Center Header -->
    <div class="mb-7 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="text-[11px] font-black text-[#00c078] tracking-widest uppercase">{{ __('Financial Command Center') }}</span>
                <span class="text-gray-300">•</span>
                <span class="text-[11px] font-semibold text-gray-400">{{ __('Real-Time Sync') }}</span>
            </div>
            <h1 class="text-3xl lg:text-4xl font-extrabold text-gray-900 tracking-tight">
                {{ now()->hour < 12 ? __('Good morning') : (now()->hour < 17 ? __('Good afternoon') : __('Good evening')) }}, {{ explode(' ', auth()->user()->name)[0] }}!
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">
                {{ __("Here's your financial overview for this cycle. All ledgers are balanced.") }}
            </p>
        </div>

        <!-- Filter Pills & Export Button -->
        <div class="flex flex-wrap items-center gap-2">
            <div class="inline-flex p-1 bg-gray-100/80 rounded-xl text-xs font-semibold text-gray-600">
                <a href="{{ route('dashboard', ['period' => 'this_month']) }}"
                   class="px-3 py-1.5 rounded-lg transition-all {{ ($period ?? 'this_month') === 'this_month' ? 'bg-white shadow-xs text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-900 font-semibold' }}">
                    {{ __('This Month') }}
                </a>
                <a href="{{ route('dashboard', ['period' => 'last_30_days']) }}"
                   class="px-3 py-1.5 rounded-lg transition-all {{ ($period ?? '') === 'last_30_days' ? 'bg-white shadow-xs text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-900 font-semibold' }}">
                    {{ __('Last 30 Days') }}
                </a>
                <a href="{{ route('dashboard', ['period' => 'quarter']) }}"
                   class="px-3 py-1.5 rounded-lg transition-all hidden sm:inline-block {{ ($period ?? '') === 'quarter' ? 'bg-white shadow-xs text-gray-900 font-bold' : 'text-gray-500 hover:text-gray-900 font-semibold' }}">
                    Q{{ ceil(now()->month / 3) }} {{ now()->year }}
                </a>
            </div>

            <!-- Date Range Selector Dropdown -->
            <div class="relative hidden sm:block" x-data="{ rangeOpen: false }">
                <button @click="rangeOpen = !rangeOpen" @click.outside="rangeOpen = false" type="button"
                        class="flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors shadow-2xs cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ $dateRangeLabel ?? (now()->startOfMonth()->format('M 1') . ' - ' . now()->endOfMonth()->format('M d, Y')) }}</span>
                    <svg class="w-3 h-3 text-gray-400 transition-transform" :class="rangeOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="rangeOpen" x-transition.opacity.duration.150ms
                     class="absolute right-0 mt-2 w-64 bg-white border border-gray-100 rounded-xl shadow-lg py-2 z-50 text-xs">
                    <p class="px-3 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Select Statement Period') }}</p>
                    @foreach ($availableMonths ?? [] as $m)
                        <a href="{{ route('dashboard', ['month' => $m['month'], 'year' => $m['year']]) }}"
                           class="flex items-center justify-between px-3 py-2 text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-medium transition-colors {{ (isset($selectedMonth, $selectedYear) && $selectedMonth == $m['month'] && $selectedYear == $m['year'] && ($period ?? '') === 'custom') ? 'bg-emerald-50 text-emerald-700 font-bold' : '' }}">
                            <span>{{ $m['label'] }}</span>
                            <span class="text-[10px] text-gray-400">{{ $m['range'] }}</span>
                        </a>
                    @endforeach
                    <div class="border-t border-gray-100 my-1"></div>
                    <a href="{{ route('dashboard', ['period' => 'last_30_days']) }}" class="flex items-center justify-between px-3 py-2 text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-medium transition-colors {{ ($period ?? '') === 'last_30_days' ? 'bg-emerald-50 text-emerald-700 font-bold' : '' }}">
                        <span>{{ __('Last 30 Days') }}</span>
                        <span class="text-[10px] text-gray-400">{{ __('Rolling 30d') }}</span>
                    </a>
                    <a href="{{ route('dashboard', ['period' => 'quarter']) }}" class="flex items-center justify-between px-3 py-2 text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-medium transition-colors {{ ($period ?? '') === 'quarter' ? 'bg-emerald-50 text-emerald-700 font-bold' : '' }}">
                        <span>Q{{ ceil(now()->month / 3) }} {{ now()->year }}</span>
                        <span class="text-[10px] text-gray-400">{{ __('Quarterly') }}</span>
                    </a>
                </div>
            </div>

            <a href="{{ route('tutorials.index') }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-white hover:bg-gray-50 border border-gray-200 text-gray-700 text-xs font-bold rounded-xl shadow-2xs transition-all">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <span>{{ __('Panduan Fitur') }}</span>
            </a>

            <button
                type="button"
                @click="$dispatch('open-receipt-scanner')"
                class="inline-flex items-center gap-2 px-3.5 py-2 bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-bold rounded-xl shadow-sm shadow-[#00c078]/25 transition-all cursor-pointer"
            >
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
                </svg>
                <span>{{ __('Scan Receipt') }}</span>
            </button>

            <a href="{{ route('reports.export.pdf') }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-950 hover:bg-black text-white text-xs font-bold rounded-xl shadow-sm transition-all duration-150">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>{{ __('Export Report') }}</span>
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="mb-5 rounded-2xl bg-[#e6f9f1] border border-[#00c078]/20 px-4 py-3 flex items-center justify-between text-xs font-semibold text-[#00995f]">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('status') }}</span>
            </div>
        </div>
    @endif

    <!-- Smart Quick-Add Bar (Natural Language Transaction Input) -->
    <x-smart-quick-add />

    <!-- 4 Key Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- 1. Total Net Balance -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#00c078] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-[#00c078]">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    +14.2%
                </span>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Total Net Balance') }}</p>
                <p class="text-2xl font-black text-gray-900 mt-1">@currency($netBalance)</p>
            </div>
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-50 text-[11px] text-gray-400">
                <span>{{ __('Vs last month') }} (@currency($lastMonthNetBalance))</span>
                <!-- Green sparkline -->
                <svg class="w-16 h-5 text-[#00c078]" fill="none" stroke="currentColor" viewBox="0 0 64 20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 15 L16 14 L30 11 L44 9 L62 3"/>
                </svg>
            </div>
        </div>

        <!-- 2. Total Inflow / Income -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#00c078] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-[#00c078]">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    +8.5%
                </span>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Total Inflow / Income') }}</p>
                <p class="text-2xl font-black text-gray-900 mt-1">@currency($totalIncome)</p>
            </div>
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-50 text-[11px] text-gray-400">
                <span>{{ $incomeSourcesCount }} {{ __('active salary & project sources') }}</span>
                <svg class="w-16 h-5 text-[#00c078]" fill="none" stroke="currentColor" viewBox="0 0 64 20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 16 L20 12 L38 13 L48 7 L62 4"/>
                </svg>
            </div>
        </div>

        <!-- 3. Total Spend / Outflow -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-red-50 text-red-500 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-50 text-red-500">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    -3.1%
                </span>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Total Spend / Outflow') }}</p>
                <p class="text-2xl font-black text-gray-900 mt-1">@currency($totalExpense)</p>
            </div>
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-50 text-[11px] text-gray-400">
                <span>{{ $spendPercentage }}% {{ __('of monthly income') }}</span>
                <svg class="w-16 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 64 20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 5 L20 12 L38 9 L50 15 L62 17"/>
                </svg>
            </div>
        </div>

        <!-- 4. Monthly Net Savings -->
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs flex flex-col justify-between">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-[#00c078]">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    +18.4%
                </span>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Monthly Net Savings') }}</p>
                <p class="text-2xl font-black text-gray-900 mt-1">@currency($netSavings)</p>
            </div>
            <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-50 text-[11px] text-gray-400">
                <span>{{ __('Savings rate') }}: {{ $savingsRate }}%</span>
                <svg class="w-16 h-5 text-[#00c078]" fill="none" stroke="currentColor" viewBox="0 0 64 20">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 17 L22 14 L36 12 L50 8 L62 4"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Middle Row: Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-6">
        <!-- Income vs. Expenses Chart (60% width) -->
        <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">{{ __('Income vs. Expenses') }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ __('6-Month trajectory and operational cash-flow') }}</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-semibold">
                        <span class="flex items-center gap-1.5 text-gray-600">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#00c078] inline-block"></span> {{ __('Income') }}
                        </span>
                        <span class="flex items-center gap-1.5 text-gray-600">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#ff7360] inline-block"></span> {{ __('Expenses') }}
                        </span>
                    </div>
                </div>

                <div class="h-64 mt-4">
                    <canvas id="lineChart"></canvas>
                </div>
            </div>

            <!-- Bottom audit pill banner -->
            <div class="mt-4 px-4 py-2.5 rounded-xl bg-gray-50 border border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-xs">
                <div class="flex items-center gap-2 text-gray-700">
                    <svg class="w-4 h-4 text-[#00c078] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ __('Net savings rate surged by +4.2% relative to Q3 baseline.') }}</span>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white text-gray-500 border border-gray-200 shrink-0">AUDIT OK</span>
            </div>
        </div>

        <!-- Spending by Category (40% width) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-1">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">{{ __('Spending by Category') }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $periodLabel ?? now()->format('F') }} {{ __('expenditure allocation') }}</p>
                    </div>
                    <a href="{{ route('categories.index') }}" class="text-gray-400 hover:text-gray-600 p-1" title="{{ __('Manage Categories') }}">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/></svg>
                    </a>
                </div>

                @if ($spendingByCategory->isNotEmpty())
                    <div class="relative my-4 flex justify-center items-center">
                        <div class="w-44 h-44">
                            <canvas id="donutChart"></canvas>
                        </div>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                            <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400">{{ __('TOTAL SPENT') }}</span>
                            <span class="text-sm font-black text-gray-900 mt-0.5">@currency($totalExpense)</span>
                            <span class="text-[10px] font-bold text-emerald-600">{{ $spendingByCategory->count() }} {{ __('Categories') }}</span>
                        </div>
                    </div>

                    <div class="space-y-2.5 mt-2">
                        @foreach ($spendingByCategory->take(5) as $item)
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $item['color'] }}"></span>
                                    <span class="text-gray-700 font-medium">{{ $item['name'] }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-gray-400 text-[11px]">{{ $item['percentage'] }}%</span>
                                    <span class="font-bold text-gray-900">@currency($item['total'])</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-16 text-center text-gray-400 text-xs">
                        {{ __('No expenses recorded this month yet.') }}
                    </div>
                @endif
            </div>

            <div class="mt-4 pt-3 border-t border-gray-50">
                <a href="{{ route('budgets.index') }}"
                   class="w-full py-2 px-3 rounded-xl bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-bold flex items-center justify-center gap-1.5 transition-colors">
                    {{ __('Manage Budget Limits') }}
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Recent Transactions + Budget & Savings -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        <!-- Recent Transactions Table (60% width) -->
        <div class="lg:col-span-3 bg-white rounded-2xl border border-gray-100 p-6 shadow-2xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">{{ __('Recent Transactions') }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ __('Verified ledger entries for the current statement') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('transactions.index') }}" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-50" title="{{ __('Filter') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        </a>
                        <a href="{{ route('reports.export.csv') }}" class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-50" title="{{ __('Export CSV') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </a>
                    </div>
                </div>

                @if ($recentTransactions->isNotEmpty())
                    <div class="overflow-x-auto -mx-2 sm:mx-0">
                        <table class="w-full text-xs min-w-[500px]">
                            <thead class="text-gray-400 font-bold uppercase tracking-wider text-left border-b border-gray-100">
                                <tr>
                                    <th class="pb-3 text-[10px]">{{ __('Transaction Name') }}</th>
                                    <th class="pb-3 text-[10px]">{{ __('Category') }}</th>
                                    <th class="pb-3 text-[10px]">{{ __('Date') }}</th>
                                    <th class="pb-3 text-[10px]">{{ __('Payment Method') }}</th>
                                    <th class="pb-3 text-[10px] text-right">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($recentTransactions as $tx)
                                    <tr class="hover:bg-gray-50/60 transition-colors">
                                        <td class="py-3.5 pr-2">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-xl flex-shrink-0 flex items-center justify-center
                                                    {{ $tx->type === 'income' ? 'bg-emerald-50 text-[#00c078]' : 'bg-gray-100 text-gray-500' }}">
                                                    @if ($tx->type === 'income')
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    @else
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                                    @endif
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="font-bold text-gray-900 truncate">{{ $tx->title }}</p>
                                                    @if ($tx->notes)
                                                        <p class="text-[10px] text-gray-400 truncate">{{ $tx->notes }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 pr-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold
                                                {{ $tx->type === 'income' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                                                {{ $tx->category->name ?? __('General') }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 pr-2 text-gray-500 text-[11px] whitespace-nowrap">
                                            {{ $tx->transaction_date ? format_user_date($tx->transaction_date) : '—' }}
                                        </td>
                                        <td class="py-3.5 pr-2 text-gray-600 text-[11px]">
                                            {{ $tx->payment_method ?? __('Bank Transfer') }}
                                        </td>
                                        <td class="py-3.5 text-right font-black whitespace-nowrap {{ $tx->type === 'income' ? 'text-[#00c078]' : 'text-gray-900' }}">
                                            {{ $tx->type === 'income' ? '+' : '-' }}@currency($tx->amount)
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-12 text-center text-gray-400 text-xs">
                        {{ __('No transactions recorded yet.') }} <a href="{{ route('transactions.create') }}" class="text-[#00c078] font-bold">{{ __('Add first transaction') }}</a>
                    </div>
                @endif
            </div>

            <div class="mt-4 pt-3 border-t border-gray-50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-xs text-gray-500">
                <span>{{ __('Showing') }} {{ $recentTransactions->count() }} {{ __('of') }} {{ $totalStatementsCount }} {{ __('verified statements') }}</span>
                <a href="{{ route('transactions.index') }}" class="font-bold text-[#00c078] hover:text-emerald-700 flex items-center gap-1">
                    {{ __('View All Transactions') }}
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>

        <!-- Right Column: Budget Health & MacBook Goal Card (40% width) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Budget Health Card -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-2xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">{{ __('Budget Health') }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ __('Caps & monthly ceiling constraints') }}</p>
                    </div>
                    <a href="{{ route('budgets.index') }}" class="text-gray-400 hover:text-gray-600 p-1" title="{{ __('Settings') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                    </a>
                </div>

                @if ($budgets->isNotEmpty())
                    <div class="space-y-4">
                        @foreach ($budgets->take(3) as $budget)
                            @php
                                $spent = $budget->spent;
                                $amount = $budget->amount;
                                $pct = $budget->progress_percentage;
                                $remaining = max(0, $amount - $spent);
                                $isNearLimit = $pct >= 90;
                            @endphp
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1.5">
                                    <div class="flex items-center gap-1.5 font-bold text-gray-800">
                                        <span>{{ $budget->name }}</span>
                                        @if ($isNearLimit)
                                            <span class="px-1.5 py-0.2 bg-red-600 text-white text-[9px] font-black rounded uppercase tracking-wider">NEAR LIMIT!</span>
                                        @endif
                                    </div>
                                    <span class="font-bold {{ $isNearLimit ? 'text-red-500' : 'text-gray-600' }}">
                                        @currency($spent) / @currency($amount)
                                    </span>
                                </div>

                                <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-300
                                        {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 75 ? 'bg-amber-500' : 'bg-[#00c078]') }}"
                                         style="width: {{ min(100, $pct) }}%"></div>
                                </div>

                                <div class="flex items-center justify-between text-[11px] mt-1 text-gray-400">
                                    <span class="{{ $pct >= 90 ? 'text-red-500 font-bold' : ($pct >= 75 ? 'text-amber-600 font-bold' : 'text-[#00c078] font-bold') }}">
                                        {{ round($pct) }}% {{ __('consumed') }}
                                    </span>
                                    <span>
                                        @if ($isNearLimit)
                                            <strong class="text-red-500">{{ __('Only') }} @currency($remaining) {{ __('left') }}</strong>
                                        @else
                                            @currency($remaining) {{ __('remaining') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-6 text-center text-gray-400 text-xs">
                        {{ __('No budget constraints created yet.') }}
                        <a href="{{ route('budgets.create') }}" class="text-[#00c078] font-bold block mt-1">{{ __('Set a budget') }}</a>
                    </div>
                @endif
            </div>

            <!-- Upcoming Bills Card -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-2xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">{{ __('Upcoming Bills') }}</h3>
                        <p class="text-[11px] text-gray-400">{{ __('Tagihan berulang siklus ini') }}</p>
                    </div>
                    <a href="{{ route('recurring-bills.index') }}" class="text-xs font-bold text-[#00c078] hover:underline flex items-center gap-1">
                        <span>Kelola</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </a>
                </div>

                @if ($upcomingBills->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($upcomingBills as $uBill)
                            @php
                                $uDays = $uBill->days_until_due;
                                $uOverdue = $uBill->isOverdue();
                            @endphp
                            <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50/80 hover:bg-gray-100/70 transition-colors">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $uOverdue ? 'bg-red-100 text-red-600' : ($uDays <= 3 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-gray-800 truncate">{{ $uBill->name }}</p>
                                        <p class="text-[10px] {{ $uOverdue ? 'text-red-500 font-bold' : ($uDays <= 3 ? 'text-amber-600 font-bold' : 'text-gray-400') }}">
                                            @if ($uOverdue)
                                                {{ __('Overdue by :days days', ['days' => abs($uDays)]) }}
                                            @elseif ($uDays === 0)
                                                {{ __('Due today') }}
                                            @elseif ($uDays <= 3)
                                                {{ __(':days days left (Day :day)', ['days' => $uDays, 'day' => $uBill->due_day]) }}
                                            @else
                                                {{ __('Due on day :day', ['day' => $uBill->due_day]) }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-xs font-black text-gray-900">@currency($uBill->amount)</p>
                                    <a href="{{ route('recurring-bills.index') }}" class="text-[10px] text-[#00c078] font-bold hover:underline">{{ __('Pay') }}</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-5 text-center text-gray-400 text-xs">
                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ __('All bills for this cycle are paid.') }}</span>
                        <a href="{{ route('recurring-bills.index') }}" class="text-[#00c078] font-bold block mt-1">{{ __('+ Add Bill') }}</a>
                    </div>
                @endif
            </div>

            <!-- Featured Savings Goal Card (Google Stitch Style) -->
            @if ($featuredGoal)
                @php
                    $gPct = $featuredGoal->progress_percentage;
                    $gRem = $featuredGoal->remaining;
                @endphp
                <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#00c078] flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 leading-tight">{{ $featuredGoal->name }}</h4>
                                    <p class="text-[11px] text-gray-400 mt-0.5">High Priority • Tech Equipment</p>
                                </div>
                            </div>
                            <span class="text-base font-black text-[#00c078]">{{ round($gPct) }}%</span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mt-5">
                            <div>
                                <p class="text-[10px] uppercase font-bold tracking-wider text-gray-400">Current Balance</p>
                                <p class="text-lg font-black text-gray-900 mt-0.5">@currency($featuredGoal->current_amount)</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] uppercase font-bold tracking-wider text-gray-400">Target Goal</p>
                                <p class="text-sm font-bold text-gray-500 mt-0.5">@currency($featuredGoal->target_amount)</p>
                            </div>
                        </div>

                        <div class="w-full bg-cyan-50 rounded-full h-2.5 overflow-hidden mt-3">
                            <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-[#00c078] transition-all duration-300"
                                 style="width: {{ min(100, $gPct) }}%"></div>
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-t border-gray-50 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-1.5 text-xs text-gray-500">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>@currency($gRem) left • Approx. 3 months away</span>
                        </div>

                        <a href="{{ route('savings.index') }}"
                           class="px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-800 text-xs font-bold rounded-xl transition-colors">
                            Add Funds
                        </a>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-gray-100 p-6 shadow-2xs text-center">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#00c078] flex items-center justify-center mx-auto mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900">Savings Target</h4>
                    <p class="text-xs text-gray-400 mt-1">Set a new goal for gadgets, vehicle or emergency fund.</p>
                    <a href="{{ route('savings.create') }}" class="mt-3 inline-block px-4 py-2 bg-[#00c078] hover:bg-emerald-600 text-white text-xs font-bold rounded-xl transition-colors">
                        Create Goal
                    </a>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        const currencySymbol = '{{ currency_symbol() }}';
        const userLocale = '{{ (auth()->user()->currency ?? "IDR") === "IDR" ? "id-ID" : "en-US" }}';

        // Income vs. Expenses Line Chart (Google Stitch Style)
        const lineCtx = document.getElementById('lineChart').getContext('2d');

        // Linear gradients
        const incGrad = lineCtx.createLinearGradient(0, 0, 0, 240);
        incGrad.addColorStop(0, 'rgba(0, 192, 120, 0.22)');
        incGrad.addColorStop(1, 'rgba(0, 192, 120, 0.0)');

        const expGrad = lineCtx.createLinearGradient(0, 0, 0, 240);
        expGrad.addColorStop(0, 'rgba(255, 115, 96, 0.18)');
        expGrad.addColorStop(1, 'rgba(255, 115, 96, 0.0)');

        const isDarkTheme = () => document.documentElement.classList.contains('dark');
        const chartPointBorder = () => isDarkTheme() ? '#121215' : '#ffffff';
        const chartGridColor = () => isDarkTheme() ? '#232329' : '#f3f4f6';
        const chartTickColor = () => isDarkTheme() ? '#71717a' : '#9ca3af';

        const finoraLineChart = new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [
                    {
                        label: 'Income',
                        data: {!! json_encode($chartIncome) !!},
                        borderColor: '#00c078',
                        backgroundColor: incGrad,
                        borderWidth: 3,
                        pointBackgroundColor: '#00c078',
                        pointBorderColor: chartPointBorder(),
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.38,
                        fill: true,
                    },
                    {
                        label: 'Expenses',
                        data: {!! json_encode($chartExpense) !!},
                        borderColor: '#ff7360',
                        backgroundColor: expGrad,
                        borderWidth: 3,
                        pointBackgroundColor: '#ff7360',
                        pointBorderColor: chartPointBorder(),
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.38,
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#18181c',
                        borderColor: '#27272e',
                        borderWidth: 1,
                        titleFont: { size: 11, weight: 'bold' },
                        bodyFont: { size: 11 },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(ctx) {
                                return ctx.dataset.label + ': ' + currencySymbol + ' ' + Number(ctx.raw).toLocaleString(userLocale);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11, weight: '600' }, color: chartTickColor() }
                    },
                    y: {
                        grid: { color: chartGridColor() },
                        ticks: {
                            font: { size: 10 },
                            color: chartTickColor(),
                            callback: v => currencySymbol + ' ' + Number(v).toLocaleString(userLocale)
                        }
                    }
                }
            }
        });

        window.addEventListener('finora-theme-changed', () => {
            finoraLineChart.data.datasets[0].pointBorderColor = chartPointBorder();
            finoraLineChart.data.datasets[1].pointBorderColor = chartPointBorder();
            finoraLineChart.options.scales.x.ticks.color = chartTickColor();
            finoraLineChart.options.scales.y.ticks.color = chartTickColor();
            finoraLineChart.options.scales.y.grid.color = chartGridColor();
            finoraLineChart.update();
        });

        @if ($spendingByCategory->isNotEmpty())
        // Spending by Category Donut Chart
        const donutCtx = document.getElementById('donutChart').getContext('2d');
        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($spendingByCategory->pluck('name')) !!},
                datasets: [{
                    data: {!! json_encode($spendingByCategory->pluck('total')) !!},
                    backgroundColor: {!! json_encode($spendingByCategory->pluck('color')) !!},
                    borderWidth: 0,
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.label + ': ' + currencySymbol + ' ' + Number(ctx.raw).toLocaleString(userLocale)
                        }
                    }
                }
            }
        });
        @endif
    </script>
    @endpush
</x-app-layout>
