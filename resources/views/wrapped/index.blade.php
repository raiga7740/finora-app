<x-app-layout>
    @php
        $user = auth()->user();
        $shareSummary = "*FINORA WRAPPED — " . strtoupper($wrap['period_label']) . "*\n"
            . "Persona Finansial: *" . $wrap['persona_title'] . "*\n"
            . "--------------------------------\n"
            . "• Total Pemasukan: " . $user->formatCurrency($wrap['total_income']) . "\n"
            . "• Total Pengeluaran: " . $user->formatCurrency($wrap['total_expense']) . "\n"
            . "• Rasio Tabungan: *" . $wrap['savings_rate'] . "%*\n"
            . "• Kategori Terbesar: *" . $wrap['top_category']['name'] . "* (" . $wrap['top_category']['percentage'] . "%)\n"
            . "• Hari Paling Aktif Belanja: *" . $wrap['peak_day_name'] . "*\n"
            . "• Hari Tanpa Belanja (No-Spend Days): *" . $wrap['zero_spend_days'] . " Hari*\n"
            . "--------------------------------\n"
            . "Dicatat otomatis di Finora Financial Command Center.";
    @endphp

    <div x-data="{
        currentSlide: 0,
        totalSlides: 5,
        copied: false,
        shareText: @js($shareSummary),
        nextSlide() {
            this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
        },
        prevSlide() {
            this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
        },
        copyWrappedText() {
            navigator.clipboard.writeText(this.shareText);
            this.copied = true;
            setTimeout(() => { this.copied = false; }, 2500);
        }
    }"
    @keydown.window.arrow-right="nextSlide()"
    @keydown.window.arrow-left="prevSlide()"
    class="space-y-6">

        <!-- Page Header & Month Selector -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider uppercase text-[#00c078] mb-1">
                    <span>{{ __('MONTHLY FINANCIAL STORY') }}</span>
                    <span class="text-gray-300">•</span>
                    <span class="text-gray-500 font-medium capitalize">{{ $wrap['period_label'] }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">{{ __('Financial Wrapped & Infographic') }}</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                    {{ __('Your interactive monthly financial recap, spending habits, zero-spend milestones, and financial persona.') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Month Picker Form -->
                <form method="GET" action="{{ route('wrapped.index') }}" class="inline-flex items-center">
                    <select name="month"
                            onchange="this.form.submit()"
                            class="rounded-xl border-gray-200 bg-white text-xs font-bold text-gray-700 py-2.5 pl-3.5 pr-8 shadow-2xs focus:border-[#00c078] focus:ring-[#00c078] cursor-pointer">
                        @foreach ($availableMonths as $m)
                            <option value="{{ $m['month'] }}"
                                    data-year="{{ $m['year'] }}"
                                    {{ ($wrap['month'] == $m['month'] && $wrap['year'] == $m['year']) ? 'selected' : '' }}>
                                {{ $m['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="year" value="{{ $wrap['year'] }}">
                </form>

                <button type="button"
                        @click="copyWrappedText()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold transition shadow-2xs cursor-pointer">
                    <svg class="w-4 h-4 text-[#00c078]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    <span x-text="copied ? '{{ __('Copied!') }}' : '{{ __('Copy Story Summary') }}'"></span>
                </button>

                <a :href="'https://wa.me/?text=' + encodeURIComponent(shareText)"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-bold transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                    </svg>
                    {{ __('Share Wrapped to WA') }}
                </a>
            </div>
        </div>

        <!-- INTERACTIVE STORY PLAYER (5 SLIDES) -->
        <div class="bg-gray-950 text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden border border-gray-800">
            <!-- Top Story Progress Bars -->
            <div class="grid grid-cols-5 gap-2 mb-6">
                <template x-for="idx in [0, 1, 2, 3, 4]" :key="idx">
                    <button type="button"
                            @click="currentSlide = idx"
                            class="h-1.5 rounded-full overflow-hidden bg-white/15 cursor-pointer transition-all">
                        <div class="h-full transition-all duration-300"
                             :class="currentSlide >= idx ? 'bg-[#00c078] w-full' : 'w-0'"></div>
                    </button>
                </template>
            </div>

            <!-- Story Slide Header Info -->
            <div class="flex items-center justify-between text-xs text-gray-400 mb-6">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full bg-[#00c078] text-white text-[10px] font-extrabold uppercase tracking-wider">
                        FINORA WRAPPED
                    </span>
                    <span class="font-semibold text-gray-300">{{ $wrap['period_label'] }}</span>
                </div>
                <span class="font-mono text-[11px]" x-text="(currentSlide + 1) + ' / ' + totalSlides"></span>
            </div>

            <!-- SLIDE 1: The Big Cash-Flow Picture -->
            <div x-show="currentSlide === 0" class="py-6 sm:py-10 text-center max-w-2xl mx-auto space-y-6">
                <span class="text-xs font-extrabold uppercase tracking-widest text-[#00c078]">{{ __('CHAPTER 1 • CASH FLOW PULSE') }}</span>
                <h2 class="text-2xl sm:text-4xl font-black tracking-tight">
                    {{ __('You recorded :count transactions in :period', ['count' => $wrap['total_transactions'], 'period' => $wrap['period_label']]) }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-400 max-w-lg mx-auto">
                    {{ __('Every transaction tells a story about how your money worked for you this month.') }}
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Total Income') }}</span>
                        <p class="text-lg sm:text-xl font-extrabold text-[#00c078] mt-1">{{ $user->formatCurrency($wrap['total_income']) }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Total Spent') }}</span>
                        <p class="text-lg sm:text-xl font-extrabold text-white mt-1">{{ $user->formatCurrency($wrap['total_expense']) }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Net Saved') }}</span>
                        <p class="text-lg sm:text-xl font-extrabold {{ $wrap['net_savings'] >= 0 ? 'text-[#00c078]' : 'text-red-400' }} mt-1">
                            {{ $user->formatCurrency($wrap['net_savings'], true) }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- SLIDE 2: Top Spending Category Podium -->
            <div x-show="currentSlide === 1" x-cloak class="py-6 sm:py-10 text-center max-w-2xl mx-auto space-y-6">
                <span class="text-xs font-extrabold uppercase tracking-widest text-[#00c078]">{{ __('CHAPTER 2 • TOP SPENDING CATEGORY') }}</span>
                <h2 class="text-2xl sm:text-4xl font-black tracking-tight">
                    {{ $wrap['top_category']['name'] }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-300">
                    {{ __('Accounted for :pct% of your total monthly spending (:amount across :count transactions).', [
                        'pct' => $wrap['top_category']['percentage'],
                        'amount' => $user->formatCurrency($wrap['top_category']['amount']),
                        'count' => $wrap['top_category']['count'],
                    ]) }}
                </p>

                @if (! empty($wrap['top_categories']))
                    <div class="space-y-2.5 text-left max-w-md mx-auto pt-2">
                        @foreach ($wrap['top_categories'] as $idx => $cat)
                            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="w-6 h-6 rounded-lg bg-[#00c078] text-white text-xs font-black flex items-center justify-center">
                                        #{{ $idx + 1 }}
                                    </span>
                                    <div>
                                        <p class="text-xs font-bold text-white">{{ $cat['name'] }}</p>
                                        <p class="text-[10px] text-gray-400">{{ $cat['count'] }} {{ __('transactions') }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-extrabold text-[#00c078]">{{ $user->formatCurrency($cat['amount']) }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $cat['percentage'] }}%</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- SLIDE 3: Spending Rhythm & Peak Day -->
            <div x-show="currentSlide === 2" x-cloak class="py-6 sm:py-10 text-center max-w-2xl mx-auto space-y-6">
                <span class="text-xs font-extrabold uppercase tracking-widest text-[#00c078]">{{ __('CHAPTER 3 • SPENDING RHYTHM') }}</span>
                <h2 class="text-2xl sm:text-4xl font-black tracking-tight">
                    {{ __('Your peak spending day was :day', ['day' => $wrap['peak_day_name']]) }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-400 max-w-lg mx-auto">
                    {{ __('On average, you spent :avg per day during :period.', [
                        'avg' => $user->formatCurrency($wrap['avg_daily_expense']),
                        'period' => $wrap['period_label'],
                    ]) }}
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 text-left">
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Biggest Single Expense') }}</span>
                        @if ($wrap['biggest_expense'])
                            <p class="text-sm font-extrabold text-white mt-1">{{ $wrap['biggest_expense']->title }}</p>
                            <p class="text-lg font-black text-[#00c078] mt-0.5">
                                {{ $user->formatCurrency($wrap['biggest_expense']->base_amount ?? $wrap['biggest_expense']->amount) }}
                            </p>
                            <p class="text-[11px] text-gray-400 mt-1">
                                {{ $user->formatUserDate($wrap['biggest_expense']->transaction_date) }}
                            </p>
                        @else
                            <p class="text-sm font-bold text-gray-400 mt-1">{{ __('No expense recorded yet') }}</p>
                        @endif
                    </div>

                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Most Active Day of Week') }}</span>
                        <p class="text-lg font-black text-white mt-1">{{ $wrap['peak_day_name'] }}</p>
                        <p class="text-sm font-extrabold text-[#00c078] mt-0.5">
                            {{ $user->formatCurrency($wrap['peak_day_amount']) }}
                        </p>
                        <p class="text-[11px] text-gray-400 mt-1">{{ __('Cumulative spending on this day of the week') }}</p>
                    </div>
                </div>
            </div>

            <!-- SLIDE 4: Zero-Spend Days & Savings Discipline -->
            <div x-show="currentSlide === 3" x-cloak class="py-6 sm:py-10 text-center max-w-2xl mx-auto space-y-6">
                <span class="text-xs font-extrabold uppercase tracking-widest text-[#00c078]">{{ __('CHAPTER 4 • MINDFUL HABITS') }}</span>
                <h2 class="text-2xl sm:text-4xl font-black tracking-tight">
                    {{ __(':days Zero-Spend Days Achieved', ['days' => $wrap['zero_spend_days']]) }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-400 max-w-lg mx-auto">
                    {{ __('Out of :elapsed days tracked this month, you had :days days with zero spending.', [
                        'elapsed' => $wrap['elapsed_days'],
                        'days' => $wrap['zero_spend_days'],
                    ]) }}
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Monthly Savings Rate') }}</span>
                        <p class="text-2xl font-black text-[#00c078] mt-1">{{ $wrap['savings_rate'] }}%</p>
                        <p class="text-[11px] text-gray-400 mt-1">{{ __('CFP Target Benchmark: ≥ 20%') }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-white/5 border border-white/10">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Go-To Payment Wallet') }}</span>
                        <p class="text-xl font-black text-white mt-1">{{ $wrap['top_method'] }}</p>
                        <p class="text-[11px] text-gray-400 mt-1">{{ __('Used in :count transactions', ['count' => $wrap['top_method_count']]) }}</p>
                    </div>
                </div>
            </div>

            <!-- SLIDE 5: Financial Persona & Shareable Card -->
            <div x-show="currentSlide === 4" x-cloak class="py-6 sm:py-10 text-center max-w-2xl mx-auto space-y-6">
                <span class="px-3 py-1 rounded-full bg-[#00c078]/20 text-[#00c078] text-[11px] font-black uppercase tracking-widest">
                    {{ $wrap['persona_badge'] }}
                </span>
                <h2 class="text-2xl sm:text-4xl font-black tracking-tight text-white">
                    {{ $wrap['persona_title'] }}
                </h2>
                <p class="text-xs sm:text-sm text-gray-300 max-w-lg mx-auto leading-relaxed">
                    {{ $wrap['persona_tagline'] }}
                </p>

                <div class="flex flex-wrap items-center justify-center gap-3 pt-3">
                    <button type="button"
                            @click="copyWrappedText()"
                            class="px-5 py-2.5 rounded-xl bg-white text-gray-900 hover:bg-gray-100 text-xs font-extrabold transition cursor-pointer">
                        <span x-text="copied ? '{{ __('Copied!') }}' : '{{ __('Copy Wrapped Summary') }}'"></span>
                    </button>
                    <a :href="'https://wa.me/?text=' + encodeURIComponent(shareText)"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="px-5 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-extrabold transition">
                        {{ __('Share on WhatsApp') }}
                    </a>
                </div>
            </div>

            <!-- Story Controls Footer -->
            <div class="mt-6 pt-4 border-t border-white/10 flex items-center justify-between">
                <button type="button"
                        @click="prevSlide()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-xs font-bold text-white transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    {{ __('Previous') }}
                </button>

                <div class="flex items-center gap-1.5">
                    <template x-for="idx in [0, 1, 2, 3, 4]" :key="idx">
                        <button type="button"
                                @click="currentSlide = idx"
                                class="w-2 h-2 rounded-full transition-all cursor-pointer"
                                :class="currentSlide === idx ? 'bg-[#00c078] w-5' : 'bg-white/30'"></button>
                    </template>
                </div>

                <button type="button"
                        @click="nextSlide()"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-xs font-bold text-white transition cursor-pointer">
                    {{ __('Next Slide') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- ALL-IN-ONE INFOGRAPHIC BENTO GRID -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-extrabold uppercase tracking-wider text-gray-900">{{ __('Complete Monthly Infographic Summary') }}</h2>
                <span class="text-xs text-gray-400">{{ $wrap['period_label'] }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Card 1: Persona -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#00c078]">{{ __('FINANCIAL PERSONA') }}</span>
                    <h3 class="text-lg font-extrabold text-gray-900 mt-1">{{ $wrap['persona_title'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ $wrap['persona_tagline'] }}</p>
                </div>

                <!-- Card 2: Top Category -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('TOP SPENDING CATEGORY') }}</span>
                    <h3 class="text-lg font-extrabold text-gray-900 mt-1">{{ $wrap['top_category']['name'] }}</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $user->formatCurrency($wrap['top_category']['amount']) }} ({{ $wrap['top_category']['percentage'] }}% {{ __('of total expenses') }})
                    </p>
                </div>

                <!-- Card 3: Zero-Spend Days -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('NO-SPEND STREAK') }}</span>
                    <h3 class="text-lg font-extrabold text-[#00c078] mt-1">{{ $wrap['zero_spend_days'] }} {{ __('Days Without Spending') }}</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ __('Average daily spend') }}: {{ $user->formatCurrency($wrap['avg_daily_expense']) }}
                    </p>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>

