<x-app-layout>
    @php
        $score = $audit['overall_score'];
        $pillars = $audit['pillars'];
        $metrics = $audit['metrics'];
        $rule = $audit['rule_50_30_20'];
        $recommendations = $audit['recommendations'];

        // SVG Radar Chart Geometry (340x320 viewBox, center 170,160, max radius 100)
        $cx = 170;
        $cy = 160;
        $maxR = 100;
        $angles = [-90, -18, 54, 126, 198]; // 5 vertices of pentagon in degrees

        $buildPolygon = function (array $radii) use ($cx, $cy, $angles) {
            $pts = [];
            foreach ($angles as $idx => $deg) {
                $rad = deg2rad($deg);
                $r = $radii[$idx] ?? 0;
                $x = round($cx + $r * cos($rad), 1);
                $y = round($cy + $r * sin($rad), 1);
                $pts[] = "{$x},{$y}";
            }
            return implode(' ', $pts);
        };

        $userRadii = [];
        $vertices = [];
        foreach ($pillars as $idx => $p) {
            $r = ($p['score'] / 100) * $maxR;
            $userRadii[] = $r;
            $rad = deg2rad($angles[$idx]);
            $vertices[] = [
                'x' => round($cx + $r * cos($rad), 1),
                'y' => round($cy + $r * sin($rad), 1),
                'lx' => round($cx + ($maxR + 28) * cos($rad), 1),
                'ly' => round($cy + ($maxR + 22) * sin($rad), 1),
                'axis_x' => round($cx + $maxR * cos($rad), 1),
                'axis_y' => round($cy + $maxR * sin($rad), 1),
                'name' => $p['name'],
                'score' => $p['score'],
            ];
        }

        $userPolygonPoints = $buildPolygon($userRadii);
        $benchmarkPolygonPoints = $buildPolygon([80, 80, 80, 80, 80]);

        // Circular gauge math (r = 56, circumference = 351.86)
        $circumference = 2 * M_PI * 56;
        $dashOffset = round($circumference * (1 - ($score / 100)), 2);
    @endphp

    <div class="space-y-6">
        <!-- Top Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider uppercase text-[#00c078] mb-1">
                    <span>{{ __('CFP DIAGNOSTIC ENGINE') }}</span>
                    <span class="text-gray-300">•</span>
                    <span class="text-gray-500 font-medium capitalize">{{ __('5-Pillar Financial Audit') }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">{{ __('Financial Health Score & Audit Radar') }}</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">
                    {{ __('Real-time evaluation of your savings rate, emergency runway, debt immunity, budget discipline, and fixed cost load.') }}
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('reports.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold transition shadow-sm">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('Detailed Reports') }}
                </a>
                <a href="{{ route('transactions.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-bold transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Add Transaction') }}
                </a>
            </div>
        </div>

        <!-- Hero Section: Score Gauge + 5-Axis Audit Radar Chart -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Overall Score & Executive Diagnosis (5 cols) -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-gray-100 p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('OVERALL HEALTH SCORE') }}</span>
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold
                            {{ $score >= 85 ? 'bg-[#e6f9f1] text-[#00995f]' : ($score >= 70 ? 'bg-teal-50 text-teal-700' : ($score >= 50 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-600')) }}">
                            {{ $audit['tier_label'] }}
                        </span>
                    </div>

                    <!-- Circular Score Gauge -->
                    <div class="flex items-center gap-6 my-4">
                        <div class="relative w-36 h-36 shrink-0 flex items-center justify-center">
                            <svg class="w-36 h-36 -rotate-90" viewBox="0 0 132 132">
                                <circle cx="66" cy="66" r="56" stroke="#f3f4f6" stroke-width="11" fill="none" class="gauge-track-circle"/>
                                <circle cx="66" cy="66" r="56"
                                        stroke="{{ $score >= 70 ? '#00c078' : ($score >= 50 ? '#f59e0b' : '#ef4444') }}"
                                        stroke-width="11"
                                        stroke-dasharray="{{ round($circumference, 2) }}"
                                        stroke-dashoffset="{{ $dashOffset }}"
                                        stroke-linecap="round"
                                        fill="none"/>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-3xl font-black text-gray-900">{{ $score }}</span>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">/ 100</span>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h2 class="text-lg font-extrabold text-gray-900">{{ $audit['tier_label'] }}</h2>
                            <p class="text-xs text-gray-500 leading-relaxed">
                                {{ $audit['tier_summary'] }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Key Snapshot Metrics Footer -->
                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-gray-100 mt-4">
                    <div class="p-3 rounded-xl bg-gray-50">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Estimated Net Worth') }}</span>
                        <p class="text-sm font-extrabold text-gray-900 mt-0.5">
                            {{ auth()->user()->formatCurrency($metrics['net_worth']) }}
                        </p>
                    </div>
                    <div class="p-3 rounded-xl bg-gray-50">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Emergency Runway') }}</span>
                        <p class="text-sm font-extrabold text-[#00c078] mt-0.5">
                            {{ $metrics['runway_months'] }} {{ __('Months') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right: 5-Axis Audit Radar Chart (7 cols) -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-gray-100 p-6 shadow-sm flex flex-col justify-between">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('5-AXIS FINANCIAL AUDIT RADAR') }}</span>
                        <h3 class="text-base font-extrabold text-gray-900">{{ __('Multi-Pillar Balance vs. CFP Target') }}</h3>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-semibold">
                        <span class="flex items-center gap-1.5 text-gray-700">
                            <span class="w-3 h-3 rounded-sm bg-[#00c078]/30 border-2 border-[#00c078]"></span>
                            {{ __('Your Score') }}
                        </span>
                        <span class="flex items-center gap-1.5 text-gray-400">
                            <span class="w-3 h-0.5 border-b border-dashed border-gray-400"></span>
                            {{ __('Target Benchmark (80+)') }}
                        </span>
                    </div>
                </div>

                <!-- SVG Pentagon Radar -->
                <div class="flex items-center justify-center">
                    <svg viewBox="0 0 340 310" class="w-full max-w-md h-auto overflow-visible">
                        <!-- Concentric Grid Pentagons (25%, 50%, 75%, 100%) -->
                        @foreach ([25, 50, 75, 100] as $ring)
                            <polygon points="{{ $buildPolygon([$ring, $ring, $ring, $ring, $ring]) }}"
                                     fill="none"
                                     stroke="#e5e7eb"
                                     stroke-width="1"
                                     class="radar-grid-line"/>
                        @endforeach

                        <!-- Benchmark 80% Dashed Pentagon -->
                        <polygon points="{{ $benchmarkPolygonPoints }}"
                                 fill="none"
                                 stroke="#71717a"
                                 stroke-width="1.2"
                                 stroke-dasharray="4 4"/>

                        <!-- 5 Axis Spokes -->
                        @foreach ($vertices as $v)
                            <line x1="{{ $cx }}" y1="{{ $cy }}"
                                  x2="{{ $v['axis_x'] }}" y2="{{ $v['axis_y'] }}"
                                  stroke="#e5e7eb" stroke-width="1"
                                  class="radar-grid-line"/>
                        @endforeach

                        <!-- User Actual Radar Polygon -->
                        <polygon points="{{ $userPolygonPoints }}"
                                 fill="rgba(0, 192, 120, 0.22)"
                                 stroke="#00c078"
                                 stroke-width="2.5"/>

                        <!-- Data Vertex Dots & Labels -->
                        @foreach ($vertices as $idx => $v)
                            <circle cx="{{ $v['x'] }}" cy="{{ $v['y'] }}" r="4.5"
                                    fill="#00c078" stroke="#ffffff" stroke-width="2"
                                    class="radar-vertex-dot"/>

                            <text x="{{ $v['lx'] }}" y="{{ $v['ly'] }}"
                                  text-anchor="{{ $idx === 0 ? 'middle' : ($idx <= 2 ? 'start' : 'end') }}"
                                  class="text-[10px] font-bold fill-gray-700">
                                {{ $v['name'] }} ({{ $v['score'] }})
                            </text>
                        @endforeach
                    </svg>
                </div>
            </div>
        </div>

        <!-- 5 Pillar Scorecards Grid -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-extrabold uppercase tracking-wider text-gray-900">{{ __('5 Pillar Diagnostic Breakdown') }}</h2>
                <span class="text-xs text-gray-400">{{ __('Weighted CFP Scoring Model') }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach ($pillars as $p)
                    <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">
                                    {{ __('Weight') }} {{ $p['weight'] }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold
                                    {{ $p['score'] >= 80 ? 'bg-[#e6f9f1] text-[#00995f]' : ($p['score'] >= 55 ? 'bg-amber-50 text-amber-700' : 'bg-red-50 text-red-600') }}">
                                    {{ $p['status'] }}
                                </span>
                            </div>

                            <h3 class="text-sm font-extrabold text-gray-900">{{ $p['name'] }}</h3>
                            <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">{{ $p['description'] }}</p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-100 space-y-2">
                            <div class="flex items-baseline justify-between">
                                <span class="text-xs font-bold text-gray-700">{{ $p['metric_value'] }}</span>
                                <span class="text-[10px] text-gray-400">{{ __('Target') }}: {{ $p['benchmark'] }}</span>
                            </div>

                            <div class="w-full h-2 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full rounded-full {{ $p['score'] >= 80 ? 'bg-[#00c078]' : ($p['score'] >= 55 ? 'bg-amber-500' : 'bg-red-500') }}"
                                     style="width: {{ $p['score'] }}%"></div>
                            </div>

                            <div class="flex items-center justify-between text-[10px] font-bold text-gray-400">
                                <span>{{ __('Pillar Score') }}</span>
                                <span class="text-gray-900">{{ $p['score'] }} / 100</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Bottom Section: 50/30/20 Rule Analyzer + Actionable Audit Recommendations -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- 50/30/20 Allocation Rule (5 cols) -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-gray-100 p-6 shadow-sm space-y-4">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#00c078]">{{ __('CASH FLOW ALLOCATION') }}</span>
                    <h3 class="text-base font-extrabold text-gray-900 mt-0.5">{{ __('50 / 30 / 20 Rule Analyzer') }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Compare your current monthly cash flow against the standard 50% Needs, 30% Wants, and 20% Savings guideline.') }}</p>
                </div>

                <div class="space-y-3.5 pt-2">
                    <!-- Needs (50%) -->
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-bold text-gray-800">{{ __('Essential Needs & Bills') }} ({{ __('Target') }} ≤ 50%)</span>
                            <span class="font-extrabold text-gray-900">{{ $rule['needs_pct'] }}% ({{ auth()->user()->formatCurrency($rule['needs_amount']) }})</span>
                        </div>
                        <div class="w-full h-2.5 rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full bg-blue-600" style="width: {{ min(100, $rule['needs_pct']) }}%"></div>
                        </div>
                    </div>

                    <!-- Wants (30%) -->
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-bold text-gray-800">{{ __('Lifestyle & Flexible Spending') }} ({{ __('Target') }} ≤ 30%)</span>
                            <span class="font-extrabold text-gray-900">{{ $rule['wants_pct'] }}% ({{ auth()->user()->formatCurrency($rule['wants_amount']) }})</span>
                        </div>
                        <div class="w-full h-2.5 rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full bg-amber-500" style="width: {{ min(100, $rule['wants_pct']) }}%"></div>
                        </div>
                    </div>

                    <!-- Savings (20%) -->
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="font-bold text-gray-800">{{ __('Net Savings & Reserves') }} ({{ __('Target') }} ≥ 20%)</span>
                            <span class="font-extrabold text-[#00c078]">{{ $rule['savings_pct'] }}% ({{ auth()->user()->formatCurrency($rule['savings_amount']) }})</span>
                        </div>
                        <div class="w-full h-2.5 rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full rounded-full bg-[#00c078]" style="width: {{ min(100, $rule['savings_pct']) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actionable Audit Recommendations (7 cols) -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-gray-100 p-6 shadow-sm space-y-4">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#00c078]">{{ __('PRIORITY ACTION PLAN') }}</span>
                    <h3 class="text-base font-extrabold text-gray-900 mt-0.5">{{ __('Personalized Audit Recommendations') }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Concrete next steps generated from your weakest financial pillars.') }}</p>
                </div>

                <div class="space-y-3">
                    @foreach ($recommendations as $rec)
                        <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase
                                        {{ $rec['priority'] === 'high' ? 'bg-red-50 text-red-600' : ($rec['priority'] === 'medium' ? 'bg-amber-50 text-amber-700' : 'bg-[#e6f9f1] text-[#00995f]') }}">
                                        {{ $rec['priority'] === 'high' ? __('High Impact') : ($rec['priority'] === 'medium' ? __('Recommended') : __('Optimized')) }}
                                    </span>
                                    <h4 class="text-xs sm:text-sm font-extrabold text-gray-900">{{ $rec['title'] }}</h4>
                                </div>
                                <p class="text-xs text-gray-500 leading-relaxed">{{ $rec['detail'] }}</p>
                            </div>

                            <a href="{{ $rec['action_url'] }}"
                               class="shrink-0 inline-flex items-center justify-center px-3.5 py-2 rounded-xl bg-white border border-gray-200 hover:border-[#00c078] hover:text-[#00995f] text-xs font-bold text-gray-700 transition shadow-2xs">
                                {{ $rec['action_label'] }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

