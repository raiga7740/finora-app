<x-app-layout>
    <x-slot name="title">Financial Reports</x-slot>

    <!-- Header & Export Action -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Financial Reports</h1>
            <p class="text-sm text-gray-500 mt-0.5">Comprehensive analytics, cash flow trajectories & exportable statements</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.export.csv', ['year' => $year, 'month' => $month]) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export CSV
            </a>
            <a href="{{ route('reports.export.pdf', ['year' => $year, 'month' => $month]) }}" target="_blank"
               class="inline-flex items-center gap-2 px-3.5 py-2 bg-gray-900 hover:bg-black text-white text-xs font-semibold rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print / Save PDF
            </a>
        </div>
    </div>

    <!-- Month/Year Filter -->
    <div class="bg-white rounded-xl border border-gray-100 p-4 mb-6 shadow-sm">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-3">
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Report Period:</span>
            <select name="month" class="pl-3 pr-8 py-1.5 border border-gray-200 rounded-lg text-xs font-medium focus:ring-2 focus:ring-emerald-500 bg-white">
                @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ sprintf('%02d', $m) }}" {{ (int)$month === $m ? 'selected' : '' }}>
                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                    </option>
                @endfor
            </select>
            <select name="year" class="pl-3 pr-8 py-1.5 min-w-[5.5rem] border border-gray-200 rounded-lg text-xs font-medium focus:ring-2 focus:ring-emerald-500 bg-white">
                @for ($y = date('Y') - 3; $y <= date('Y') + 1; $y++)
                    <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors">
                Apply Filter
            </button>
        </form>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Total Inflow / Income</p>
            <p class="text-2xl font-bold text-emerald-600">@currency($totalIncome)</p>
            <p class="text-xs text-gray-400 mt-1">{{ $startDate->format('M Y') }} statement</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Total Spend / Outflow</p>
            <p class="text-2xl font-bold text-red-500">@currency($totalExpense)</p>
            <p class="text-xs text-gray-400 mt-1">{{ count($transactions->where('type', 'expense')) }} expense entries</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Net Savings</p>
            <p class="text-2xl font-bold {{ $netSavings >= 0 ? 'text-gray-900' : 'text-red-600' }}">
                @currency($netSavings)
            </p>
            <p class="text-xs text-gray-400 mt-1">Inflow minus outflow</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Savings Rate</p>
            <p class="text-2xl font-bold {{ $savingsRate >= 20 ? 'text-emerald-600' : 'text-orange-500' }}">{{ $savingsRate }}%</p>
            <p class="text-xs text-gray-400 mt-1">Target recommended: ≥ 20%</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-6">
        <!-- 12-Month Trajectory Chart -->
        <div class="lg:col-span-3 bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">12-Month Financial Trajectory</h3>
                    <p class="text-xs text-gray-400">Cash-flow trends over the last 12 rolling months</p>
                </div>
                <div class="flex items-center gap-3 text-xs text-gray-500">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>Income</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-400 inline-block"></span>Expense</span>
                </div>
            </div>
            <div class="h-64">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- Expense by Category Distribution -->
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 p-5 shadow-sm flex flex-col">
            <h3 class="text-base font-semibold text-gray-900">Expense Allocation</h3>
            <p class="text-xs text-gray-400 mb-4">{{ $startDate->format('F Y') }} category breakdown</p>

            @if ($categoryBreakdown->isNotEmpty())
                <div class="h-44 flex items-center justify-center mb-4">
                    <canvas id="categoryChart"></canvas>
                </div>
                <div class="space-y-2 mt-auto">
                    @foreach ($categoryBreakdown->take(5) as $cat)
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-700 truncate max-w-xs">{{ $cat['name'] }} ({{ $cat['percentage'] }}%)</span>
                            <span class="font-semibold text-gray-900">@currency($cat['total'])</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center text-gray-400 text-xs py-10">
                    <p>No expense data for this selected period.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Detailed Ledger Table -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">Statement Entries ({{ $transactions->count() }})</h3>
            <span class="text-xs text-gray-400">{{ format_user_date($startDate) }} - {{ format_user_date($endDate) }}</span>
        </div>
        @if ($transactions->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-xs min-w-[550px]">
                    <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-left border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Title / Description</th>
                            <th class="px-5 py-3">Category</th>
                            <th class="px-5 py-3">Payment Method</th>
                            <th class="px-5 py-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($transactions as $t)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-5 py-3 text-gray-500 whitespace-nowrap">{{ $t->transaction_date ? format_user_date($t->transaction_date) : '—' }}</td>
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $t->title }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $t->category->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $t->payment_method ?? '—' }}</td>
                                <td class="px-5 py-3 text-right font-bold {{ $t->type === 'income' ? 'text-emerald-600' : 'text-gray-900' }}">
                                    {{ $t->type === 'income' ? '+' : '-' }}@currency($t->amount)
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12 text-gray-400 text-xs">
                No ledger entries found for {{ $startDate->format('F Y') }}.
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        // 12-Month Trajectory Chart
        const isDarkReport = () => document.documentElement.classList.contains('dark');
        const reportGridColor = () => isDarkReport() ? '#262a33' : '#e5e7eb';
        const reportTickColor = () => isDarkReport() ? '#86948a' : '#64748b';

        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const finoraTrendChart = new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($monthlyTrends->pluck('label')) !!},
                datasets: [
                    {
                        label: 'Income',
                        data: {!! json_encode($monthlyTrends->pluck('income')) !!},
                        backgroundColor: '#10b981',
                        borderRadius: 4,
                    },
                    {
                        label: 'Expense',
                        data: {!! json_encode($monthlyTrends->pluck('expense')) !!},
                        backgroundColor: '#f87171',
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 }, color: reportTickColor() } },
                    y: {
                        grid: { color: reportGridColor() },
                        ticks: {
                            color: reportTickColor(),
                            callback: v => '{{ currency_symbol() }} ' + Number(v).toLocaleString('{{ (auth()->user()->currency ?? "IDR") === "IDR" ? "id" : "en" }}'),
                            font: { size: 9 }
                        }
                    }
                }
            }
        });

        window.addEventListener('finora-theme-changed', () => {
            finoraTrendChart.options.scales.x.ticks.color = reportTickColor();
            finoraTrendChart.options.scales.y.ticks.color = reportTickColor();
            finoraTrendChart.options.scales.y.grid.color = reportGridColor();
            finoraTrendChart.update();
        });

        @if ($categoryBreakdown->isNotEmpty())
        // Category Donut Chart
        const catCtx = document.getElementById('categoryChart').getContext('2d');
        const colors = ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#ec4899', '#64748b'];
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($categoryBreakdown->pluck('name')) !!},
                datasets: [{
                    data: {!! json_encode($categoryBreakdown->pluck('total')) !!},
                    backgroundColor: colors,
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.label + ': {{ currency_symbol() }} ' + Number(ctx.raw).toLocaleString('{{ (auth()->user()->currency ?? "IDR") === "IDR" ? "id" : "en" }}')
                        }
                    }
                },
                cutout: '70%',
            }
        });
        @endif
    </script>
    @endpush
</x-app-layout>

