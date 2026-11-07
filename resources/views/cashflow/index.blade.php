<x-app-layout>
    <x-slot name="title">{{ __('Cashflow Forecast') }}</x-slot>

    @php
        $user = auth()->user();
        $fmt = fn ($v) => $user->formatCurrency($v);
    @endphp

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <span class="text-[11px] font-black text-[#00c078] tracking-widest uppercase">{{ __('Predictive Engine') }}</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">{{ __('Cashflow Forecast & Low Balance Alert') }}</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                    {{ __('Predicts your daily balance from spending habits, recurring bills, debts, and expected income.') }}
                </p>
            </div>
            <div class="inline-flex gap-1 bg-gray-100 p-1 rounded-xl text-xs font-bold">
                @foreach ([30, 60, 90] as $d)
                    <a href="{{ route('cashflow.index', ['days' => $d]) }}"
                       class="px-3.5 py-1.5 rounded-lg transition {{ $forecast['horizon'] === $d ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-800' }}">
                        {{ $d }} {{ __('Days') }}
                    </a>
                @endforeach
            </div>
        </div>

        @if (! $forecast['has_data'])
            <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center shadow-sm">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-[#00c078] flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8m0 0h-5m5 0v5"/></svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900">{{ __('Not enough data to forecast yet') }}</h3>
                <p class="text-xs text-gray-500 max-w-md mx-auto mt-1">
                    {{ __('Add at least one wallet and record some expenses so Finora can learn your spending pattern.') }}
                </p>
                <div class="mt-4 flex items-center justify-center gap-2">
                    <a href="{{ route('wallets.index') }}" class="px-4 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-semibold">{{ __('Add Wallet') }}</a>
                    <a href="{{ route('transactions.create') }}" class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-gray-700 text-xs font-semibold">{{ __('Add Transaction') }}</a>
                </div>
            </div>
        @else
            @php
                $status = $forecast['status'];
                $banner = match ($status) {
                    'critical' => ['bg-red-50 border-red-200 text-red-700', 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'],
                    'warning' => ['bg-amber-50 border-amber-200 text-amber-700', 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z'],
                    default => ['bg-emerald-50 border-emerald-200 text-emerald-700', 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                };
            @endphp

            {{-- Status banner --}}
            <div class="rounded-2xl border p-4 sm:p-5 flex items-start gap-3.5 {{ $banner[0] }}">
                <svg class="w-6 h-6 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $banner[1] }}"/></svg>
                <div class="text-sm">
                    @if ($status === 'critical')
                        <p class="font-extrabold">
                            {{ __('Your balance is predicted to run out on :date (in :days days).', ['date' => $user->formatUserDate($forecast['zero_date']), 'days' => $forecast['days_until_zero']]) }}
                        </p>
                        <p class="text-xs mt-1 opacity-90">
                            {{ __('Lowest projected balance: :amount. Reduce daily spending to :safe or less, or postpone non-essential bills.', ['amount' => $fmt($forecast['lowest']['balance']), 'safe' => $fmt($forecast['safe_daily'])]) }}
                        </p>
                    @elseif ($status === 'warning')
                        <p class="font-extrabold">{{ __('Low balance warning: your balance may drop to :amount on :date.', ['amount' => $fmt($forecast['lowest']['balance']), 'date' => $user->formatUserDate($forecast['lowest']['date'])]) }}</p>
                        <p class="text-xs mt-1 opacity-90">
                            {{ __('That is below a one-week spending buffer (:buffer). Keep daily spending under :safe.', ['buffer' => $fmt($forecast['buffer']), 'safe' => $fmt($forecast['safe_daily'])]) }}
                        </p>
                    @else
                        <p class="font-extrabold">{{ __('Cashflow looks healthy for the next :days days.', ['days' => $forecast['horizon']]) }}</p>
                        <p class="text-xs mt-1 opacity-90">
                            {{ __('Your balance stays above a one-week spending buffer. You can spend up to :safe per day safely.', ['safe' => $fmt($forecast['safe_daily'])]) }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- KPI cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Current Liquid Balance') }}</span>
                    <p class="text-lg font-black text-gray-900 mt-1">{{ $fmt($forecast['start_balance']) }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Projected in :days days', ['days' => $forecast['horizon']]) }}</span>
                    <p class="text-lg font-black mt-1 {{ $forecast['end_balance'] < 0 ? 'text-red-500' : 'text-gray-900' }}">{{ $fmt($forecast['end_balance']) }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Lowest Point') }}</span>
                    <p class="text-lg font-black mt-1 {{ $forecast['lowest']['balance'] < 0 ? 'text-red-500' : 'text-gray-900' }}">{{ $fmt($forecast['lowest']['balance']) }}</p>
                    <p class="text-[10px] text-gray-400">{{ $user->formatUserDate($forecast['lowest']['date']) }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Safe Daily Spending') }}</span>
                    <p class="text-lg font-black text-[#00c078] mt-1">{{ $fmt($forecast['safe_daily']) }}</p>
                    <p class="text-[10px] text-gray-400">{{ __('Your avg: :amount/day', ['amount' => $fmt($forecast['daily_spend'])]) }}</p>
                </div>
            </div>

            {{-- Chart --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <h3 class="text-sm font-extrabold text-gray-900 mb-3">{{ __('Projected Daily Balance') }}</h3>
                <div class="h-72"><canvas id="forecastChart"></canvas></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Upcoming events --}}
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-extrabold text-gray-900">{{ __('Scheduled Money Movements') }}</h3>
                    </div>
                    @if (count($forecast['events']))
                        <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                            @foreach ($forecast['events'] as $e)
                                <div class="px-5 py-3 flex items-center justify-between gap-3 text-xs">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0
                                            {{ $e['kind'] === 'income' ? 'bg-emerald-50 text-emerald-700' : ($e['kind'] === 'debt' ? 'bg-purple-50 text-purple-700' : 'bg-amber-50 text-amber-700') }}">
                                            {{ $e['kind'] === 'income' ? __('Income') : ($e['kind'] === 'debt' ? __('Debt') : __('Bill')) }}
                                        </span>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-gray-900 truncate">{{ $e['label'] }}</p>
                                            <p class="text-[10px] text-gray-400">{{ $user->formatUserDate($e['date']) }}</p>
                                        </div>
                                    </div>
                                    <span class="font-black whitespace-nowrap {{ $e['amount'] > 0 ? 'text-emerald-600' : 'text-gray-900' }}">
                                        {{ $e['amount'] > 0 ? '+' : '-' }}{{ $fmt(abs($e['amount'])) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="py-10 text-center text-xs text-gray-400">{{ __('No scheduled bills, debts, or income in this period.') }}</p>
                    @endif
                </div>

                {{-- Assumptions --}}
                <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm space-y-3 text-xs">
                    <h3 class="text-sm font-extrabold text-gray-900">{{ __('How this is calculated') }}</h3>
                    <p class="text-gray-500">{{ __('Average daily spending (last 60 days, excluding bill payments): :amount', ['amount' => $fmt($forecast['daily_spend'])]) }}</p>
                    <p class="text-gray-500">{{ __('Fixed outflow (bills and debts due): :amount', ['amount' => $fmt($forecast['fixed_outflow'])]) }}</p>
                    <p class="text-gray-500">{{ __('Expected income in period: :amount', ['amount' => $fmt($forecast['expected_income'])]) }}</p>
                    @if ($forecast['potential_receivables'] > 0)
                        <p class="text-gray-500">{{ __('Money owed to you (:amount) is not counted until it is received.', ['amount' => $fmt($forecast['potential_receivables'])]) }}</p>
                    @endif
                    <p class="text-gray-400">{{ __('Investment wallets are excluded from liquid balance. This is an estimate, not financial advice.') }}</p>
                </div>
            </div>
        @endif
    </div>

    @if ($forecast['has_data'])
        @push('scripts')
        <script>
            (function () {
                const series = @json($forecast['series']);
                const dark = () => document.documentElement.classList.contains('dark');
                const grid = () => dark() ? '#262a33' : '#e5e7eb';
                const tick = () => dark() ? '#86948a' : '#64748b';
                const symbol = @json($user->currency_symbol);
                const locale = @json(($user->currency ?? 'IDR') === 'IDR' ? 'id' : 'en');
                const buffer = {{ $forecast['buffer'] }};
                const ctx = document.getElementById('forecastChart').getContext('2d');

                const chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: series.map(p => p.date.slice(5)),
                        datasets: [
                            {
                                label: @json(__('Projected balance')),
                                data: series.map(p => p.balance),
                                borderColor: '#00c078',
                                backgroundColor: 'rgba(0, 192, 120, 0.10)',
                                borderWidth: 2.5,
                                pointRadius: 0,
                                pointHoverRadius: 5,
                                tension: 0.25,
                                fill: true,
                                segment: { borderColor: c => c.p1.parsed.y < 0 ? '#fb7185' : '#00c078' },
                            },
                            {
                                label: @json(__('Safety buffer')),
                                data: series.map(() => buffer),
                                borderColor: '#f59e0b',
                                borderDash: [5, 5],
                                borderWidth: 1.2,
                                pointRadius: 0,
                                fill: false,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: c => c.dataset.label + ': ' + symbol + ' ' + Number(c.raw).toLocaleString(locale),
                                },
                            },
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: tick(), maxTicksLimit: 10, font: { size: 10 } } },
                            y: {
                                grid: { color: grid() },
                                ticks: { color: tick(), font: { size: 10 }, callback: v => symbol + ' ' + Number(v).toLocaleString(locale) },
                            },
                        },
                    },
                });

                window.addEventListener('finora-theme-changed', () => {
                    chart.options.scales.x.ticks.color = tick();
                    chart.options.scales.y.ticks.color = tick();
                    chart.options.scales.y.grid.color = grid();
                    chart.update();
                });
            })();
        </script>
        @endpush
    @endif
</x-app-layout>

