<x-app-layout>
    <x-slot name="title">Cash Flow Calendar</x-slot>

    <div x-data="{
        dayModalOpen: false,
        selectedDay: {
            date_string: '',
            day_number: '',
            formatted_date: '',
            income: 0,
            expense: 0,
            transactions: [],
            bills: []
        },
        openDay(day) {
            this.selectedDay = Object.assign({}, day);
            // Format readable date
            const d = new Date(day.date_string);
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const locale = '{{ app()->getLocale() === 'en' ? 'en-US' : 'id-ID' }}';
            this.selectedDay.formatted_date = d.toLocaleDateString(locale, options);
            this.dayModalOpen = true;
        }
    }">

        {{-- Top Header Section --}}
        <div class="mb-7 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[11px] font-black text-[#00c078] tracking-widest uppercase">{{ __('Cash Flow Radar') }}</span>
                    <span class="text-gray-300">•</span>
                    <span class="text-[11px] font-semibold text-gray-400">{{ __('Temporal Spending Velocity') }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">{{ __('Cash Flow Calendar') }}</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    {{ __('Daily cash flow map to visualize spending velocity, upcoming bill due dates, and no-spend days.') }}
                </p>
            </div>

            {{-- Month Navigator --}}
            <div class="flex items-center gap-2 bg-white p-1.5 rounded-2xl border border-gray-200 shadow-2xs">
                <a
                    href="{{ route('calendar.index', ['month' => $prevMonth, 'year' => $prevYear]) }}"
                    class="p-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                    title="{{ __('Previous Month') }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                </a>

                <div class="px-3 py-1 text-center min-w-[130px]">
                    <span class="text-sm font-extrabold text-gray-900 block leading-tight">
                        {{ $currentMonth->format('F Y') }}
                    </span>
                    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
                        {{ $currentMonth->format('M Y') }} Cycle
                    </span>
                </div>

                <a
                    href="{{ route('calendar.index', ['month' => $nextMonth, 'year' => $nextYear]) }}"
                    class="p-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                    title="{{ __('Next Month') }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </a>

                @if ($month !== now()->month || $year !== now()->year)
                    <div class="border-l border-gray-200 pl-1.5 ml-1">
                        <a
                            href="{{ route('calendar.index') }}"
                            class="px-2.5 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold transition"
                        >
                            {{ __('Today') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        {{-- 4 Top KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
            {{-- Total Income --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Total Inflow / Income') }}</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-[#00c078] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    </div>
                </div>
                <div>
                    <div class="text-xl sm:text-2xl font-black text-emerald-600 tracking-tight">
                        +@currency($totalIncome)
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">{{ __('Total cash inflow this month') }}</p>
                </div>
            </div>

            {{-- Total Expense --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Total Spend / Outflow') }}</span>
                    <div class="w-8 h-8 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/></svg>
                    </div>
                </div>
                <div>
                    <div class="text-xl sm:text-2xl font-black text-red-600 tracking-tight">
                        -@currency($totalExpense)
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">{{ __('Total spending & paid bills') }}</p>
                </div>
            </div>

            {{-- Peak Spend Day --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Peak Spend Day') }}</span>
                    <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>
                    </div>
                </div>
                <div>
                    <div class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">
                        @if ($peakSpendAmount > 0)
                            @currency($peakSpendAmount)
                        @else
                            @currency(0)
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">
                        @if ($peakSpendDate)
                            {{ __('Peak spend') }}: {{ format_user_date($peakSpendDate) }}
                        @else
                            {{ __('No expenses recorded') }}
                        @endif
                    </p>
                </div>
            </div>

            {{-- No-Spend Days --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('No-Spend Days') }}</span>
                    <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.004 0M7.5 15h9"/></svg>
                    </div>
                </div>
                <div>
                    <div class="text-xl sm:text-2xl font-black text-purple-700 tracking-tight">
                        {{ $noSpendDaysCount }} <span class="text-xs font-semibold text-gray-500">{{ __('Days') }}</span>
                    </div>
                    <p class="text-[11px] text-emerald-600 font-semibold mt-1">{{ __('No-spend achievement!') }}</p>
                </div>
            </div>
        </div>

        {{-- Calendar Grid Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-2xs overflow-hidden">
            {{-- Day Names Header --}}
            <div class="grid grid-cols-7 border-b border-gray-100 bg-gray-50/70 text-center">
                @php
                    $dayNames = app()->getLocale() === 'en'
                        ? ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN']
                        : ['SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB', 'MIN'];
                @endphp
                @foreach ($dayNames as $name)
                    <div class="py-3 text-[11px] font-bold uppercase tracking-wider {{ in_array($name, ['SAT', 'SUN', 'SAB', 'MIN']) ? 'text-amber-700 font-black' : 'text-gray-500' }}">
                        {{ $name }}
                    </div>
                @endforeach
            </div>

            {{-- Calendar Cells (7 Columns Grid) --}}
            <div class="grid grid-cols-7 divide-x divide-y divide-gray-100">
                @foreach ($days as $day)
                    @php
                        $hasTx = count($day['transactions']) > 0;
                        $hasBills = count($day['bills']) > 0;
                    @endphp
                    <div
                        @click="openDay({{ json_encode($day) }})"
                        class="min-h-[105px] sm:min-h-[120px] p-2 sm:p-2.5 transition-all cursor-pointer relative group flex flex-col justify-between
                               {{ !$day['is_current_month'] ? 'bg-gray-50/40 text-gray-300' : 'bg-white hover:bg-emerald-50/30' }}
                               {{ $day['is_today'] ? 'ring-2 ring-inset ring-[#00c078]' : '' }}"
                    >
                        {{-- Date Header in Cell --}}
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg text-xs font-bold
                                  {{ $day['is_today'] ? 'bg-[#00c078] text-white shadow-xs' : ($day['is_current_month'] ? 'text-gray-800' : 'text-gray-400') }}">
                                {{ $day['day_number'] }}
                            </span>

                            {{-- Bill Due Warning Pin --}}
                            @if ($hasBills && $day['is_current_month'])
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse" title="{{ count($day['bills']) }} tagihan jatuh tempo"></span>
                            @endif
                        </div>

                        {{-- Cash Flow Badges --}}
                        <div class="space-y-1 my-1">
                            @if ($day['income'] > 0)
                                <div class="px-1.5 py-0.5 rounded-md bg-emerald-50 border border-emerald-100/80 text-[10px] font-bold text-emerald-700 truncate" title="{{ __('Income') }}: {{ format_currency($day['income']) }}">
                                    +{{ number_format($day['income'] / 1000, 0) }}k
                                </div>
                            @endif

                            @if ($day['expense'] > 0)
                                <div class="px-1.5 py-0.5 rounded-md bg-red-50 border border-red-100/80 text-[10px] font-bold text-red-600 truncate" title="{{ __('Expense') }}: {{ format_currency($day['expense']) }}">
                                    -{{ number_format($day['expense'] / 1000, 0) }}k
                                </div>
                            @endif
                        </div>

                        {{-- Bottom Cell Micro-Indicator --}}
                        <div class="text-[9px] text-gray-400 flex items-center justify-between">
                            @if ($hasTx)
                                <span class="font-medium text-gray-500">{{ count($day['transactions']) }} tx</span>
                            @else
                                <span></span>
                            @endif

                            @if ($hasBills)
                                <span class="inline-flex items-center gap-0.5 text-amber-600 font-bold" title="{{ __('Due Bills') }}">
                                    <svg class="w-2.5 h-2.5 inline" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                                    <span>{{ __('Bill') }}</span>
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Legend Footer --}}
        <div class="mt-4 flex flex-wrap items-center justify-between gap-4 text-xs text-gray-500 px-2">
            <div class="flex items-center gap-4 flex-wrap">
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-md bg-emerald-100 border border-emerald-200"></span>
                    <span>{{ __('Income (+Inflow)') }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-md bg-red-100 border border-red-200"></span>
                    <span>{{ __('Expense (-Outflow)') }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>{{ __('Due Bills') }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-md border-2 border-[#00c078]"></span>
                    <span>Hari Ini</span>
                </div>
            </div>

            <p class="text-[11px] text-gray-400">{{ __('Click on any date to view daily cash flow and commitments.') }}</p>
        </div>

        {{-- ================= MODAL DETAIL HARIAN ================= --}}
        <div
            x-show="dayModalOpen"
            style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-day-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity" @click="dayModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div
                    class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl border border-gray-100 transition-all text-left"
                    @click.stop
                >
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#00c078] flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-gray-900" x-text="selectedDay.formatted_date"></h3>
                                <p class="text-[11px] text-gray-500">{{ __('Daily cash flow changes & upcoming commitments') }}</p>
                            </div>
                        </div>
                        <button @click="dayModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Day Net Summary Cards --}}
                    <div class="grid grid-cols-2 gap-3 mb-5">
                        <div class="bg-emerald-50/60 rounded-xl p-3 border border-emerald-100">
                            <span class="text-[10px] uppercase font-bold text-emerald-800">{{ __("Today's Income") }}</span>
                            <div class="text-base font-black text-emerald-700 mt-0.5">
                                +@currencySymbol<span x-text="Number(selectedDay.income).toLocaleString('{{ (auth()->user()->currency ?? 'IDR') === 'IDR' ? 'id-ID' : 'en-US' }}')"></span>
                            </div>
                        </div>
                        <div class="bg-red-50/60 rounded-xl p-3 border border-red-100">
                            <span class="text-[10px] uppercase font-bold text-red-800">{{ __("Today's Expenses") }}</span>
                            <div class="text-base font-black text-red-600 mt-0.5">
                                -@currencySymbol<span x-text="Number(selectedDay.expense).toLocaleString('{{ (auth()->user()->currency ?? 'IDR') === 'IDR' ? 'id-ID' : 'en-US' }}')"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Recurring Bills Warning (If Any) --}}
                    <template x-if="selectedDay.bills && selectedDay.bills.length > 0">
                        <div class="mb-5 p-3.5 rounded-xl bg-amber-50 border border-amber-200">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                <span class="text-xs font-bold text-amber-900">{{ __('Bills Due on this Date:') }}</span>
                            </div>
                            <div class="space-y-1.5">
                                <template x-for="bill in selectedDay.bills" :key="bill.id">
                                    <div class="flex items-center justify-between text-xs bg-white/80 p-2 rounded-lg">
                                        <span class="font-bold text-gray-800" x-text="bill.name"></span>
                                        <span class="font-extrabold text-amber-700">
                                            @currencySymbol<span x-text="Number(bill.amount).toLocaleString('{{ (auth()->user()->currency ?? 'IDR') === 'IDR' ? 'id-ID' : 'en-US' }}')"></span>
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Transactions List --}}
                    <div class="mb-5">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">{{ __('Transaction List:') }}</h4>
                        <div class="max-h-56 overflow-y-auto space-y-2 pr-1">
                            <template x-if="selectedDay.transactions && selectedDay.transactions.length > 0">
                                <div>
                                    <template x-for="tx in selectedDay.transactions" :key="tx.id">
                                        <div class="p-3 rounded-xl border border-gray-100 hover:bg-gray-50 flex items-center justify-between transition-colors">
                                            <div class="min-w-0 pr-3">
                                                <p class="text-xs font-bold text-gray-900 truncate" x-text="tx.title"></p>
                                                <p class="text-[10px] text-gray-400 truncate">
                                                    <span x-text="tx.category ? tx.category.name : '{{ __('General') }}'"></span> &bull;
                                                    <span x-text="tx.payment_method || '{{ __('Cash') }}'"></span>
                                                </p>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <p class="text-xs font-black" :class="tx.type === 'income' ? 'text-emerald-600' : 'text-gray-900'">
                                                    <span x-text="tx.type === 'income' ? '+' : '-'"></span>@currencySymbol<span x-text="Number(tx.amount).toLocaleString('{{ (auth()->user()->currency ?? 'IDR') === 'IDR' ? 'id-ID' : 'en-US' }}')"></span>
                                                </p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="!selectedDay.transactions || selectedDay.transactions.length === 0">
                                <div class="py-6 text-center text-gray-400 text-xs">
                                    <p class="font-medium">{{ __('No transactions recorded for this date.') }}</p>
                                    <p class="text-[11px] text-emerald-600 mt-1 font-semibold">{{ __('No-spend day achieved!') }}</p>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="pt-4 flex items-center justify-between border-t border-gray-100">
                        <button
                            type="button"
                            @click="dayModalOpen = false"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition"
                        >
                            {{ __('Close') }}
                        </button>

                        <a
                            :href="'{{ route('transactions.create') }}?transaction_date=' + selectedDay.date_string"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-bold shadow-sm transition"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            <span>{{ __('+ Add Transaction on this Date') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>

