<x-app-layout>
    <x-slot name="title">{{ __('Smart Financial Calculator') }}</x-slot>

    <div x-data="smartCalculator({
        currencySymbol: '{{ $currencySymbol }}',
        userBalance: {{ $totalBalance }},
        userIncome: {{ $monthlyIncome > 0 ? $monthlyIncome : 5000000 }},
        userExpense: {{ $monthlyExpense }},
        userDebt: {{ $totalDebt }},
        userLocale: '{{ $userLocale ?? "id" }}'
    })" class="space-y-6 pb-12">

        <!-- Top Header & Banner -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-700 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
            <div class="absolute right-0 top-0 translate-x-8 -translate-y-8 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-2.5 mb-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wide uppercase bg-white/20 backdrop-blur-md text-white border border-white/30">
                        <svg class="w-3.5 h-3.5 text-amber-300" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                        <span>Finora Smart Lab</span>
                    </span>
                    <span class="text-emerald-100 text-xs font-medium" x-text="t('banner_badge')"></span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight" x-text="t('banner_title')">Kalkulator Finansial Pintar</h1>
                <p class="text-emerald-50 text-xs sm:text-sm mt-1 max-w-2xl leading-relaxed" x-text="t('banner_subtitle')">
                    Eksplorasi kalkulasi dengan coretan teks alami (ala Soulver), hitung harga barang dalam jam kerja riil, simulasikan cicilan utang, dan lihat dampak kebiasaan kecil dalam jangka panjang.
                </p>
            </div>

            <!-- Quick Data Integration Badges & Language Switcher -->
            <div class="relative z-10 flex flex-wrap md:flex-col items-start md:items-end gap-2 shrink-0">
                <!-- Dual Language Selector (ID / EN) -->
                <div class="flex items-center gap-1 bg-white/20 backdrop-blur-md border border-white/30 p-0.5 rounded-xl text-xs font-bold">
                    <button type="button" @click="setLocale('id')"
                            :class="locale === 'id' ? 'bg-white text-emerald-800 shadow-xs' : 'text-white/80 hover:text-white'"
                            class="px-2.5 py-1 rounded-lg transition text-[11px]">
                        ID
                    </button>
                    <button type="button" @click="setLocale('en')"
                            :class="locale === 'en' ? 'bg-white text-emerald-800 shadow-xs' : 'text-white/80 hover:text-white'"
                            class="px-2.5 py-1 rounded-lg transition text-[11px]">
                        EN
                    </button>
                </div>

                <div class="flex items-center gap-2 bg-white/15 backdrop-blur-md border border-white/20 rounded-xl px-3 py-1.5 text-xs">
                    <div class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></div>
                    <span class="text-white/80" x-text="t('your_balance')">Saldo Anda:</span>
                    <span class="font-bold text-white">{{ $currencySymbol }} {{ number_format($totalBalance, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center gap-2 bg-white/15 backdrop-blur-md border border-white/20 rounded-xl px-3 py-1.5 text-xs">
                    <div class="w-2 h-2 rounded-full bg-cyan-300"></div>
                    <span class="text-white/80" x-text="t('monthly_inflow')">Pemasukan Bulan Ini:</span>
                    <span class="font-bold text-white">{{ $currencySymbol }} {{ number_format($monthlyIncome, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-1.5 border border-gray-100 dark:border-gray-700 shadow-xs flex flex-wrap gap-1">
            <button @click="switchTab('scratchpad')"
                    :class="activeTab === 'scratchpad' 
                        ? 'bg-emerald-600 text-white shadow-xs' 
                        : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs font-semibold transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span x-text="t('tab_scratchpad')">Smart Scratchpad</span>
                <span class="text-[10px] px-1.5 py-0.2 rounded font-bold uppercase tracking-wider"
                      :class="activeTab === 'scratchpad' ? 'bg-white/25 text-white' : 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300'">
                    Live
                </span>
            </button>

            <button @click="switchTab('impulse')"
                    :class="activeTab === 'impulse' 
                        ? 'bg-emerald-600 text-white shadow-xs' 
                        : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs font-semibold transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-text="t('tab_impulse')">Jam Kerja & Belanja Impulsif</span>
            </button>

            <button @click="switchTab('loan')"
                    :class="activeTab === 'loan' 
                        ? 'bg-emerald-600 text-white shadow-xs' 
                        : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs font-semibold transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span x-text="t('tab_loan')">Simulasi Cicilan & Bunga</span>
            </button>

            <button @click="switchTab('compounding')"
                    :class="activeTab === 'compounding' 
                        ? 'bg-emerald-600 text-white shadow-xs' 
                        : 'text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-700/50'"
                    class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs font-semibold transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                <span x-text="t('tab_compounding')">Efek Kopi (Compounding)</span>
            </button>
        </div>

        <!-- ============================================================== -->
        <!-- TAB 1: SMART SCRATCHPAD (LIVE TEXT-BASED CALCULATOR)           -->
        <!-- ============================================================== -->
        <div x-show="activeTab === 'scratchpad'" x-cloak class="space-y-4">
            <!-- Toolbar & Presets -->
            <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-xs flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="flex items-center flex-wrap gap-1.5">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mr-1" x-text="t('quick_presets')">Preset Cepat:</span>
                    <button type="button" @click="loadPreset('budget_503020')" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-gray-600 transition">
                        <svg class="w-3.5 h-3.5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span x-text="t('preset_budget')">Budget 50/30/20</span>
                    </button>
                    <button type="button" @click="loadPreset('monthly_cashflow')" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-gray-600 transition">
                        <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span x-text="t('preset_cashflow')">Arus Kas Bulanan</span>
                    </button>
                    <button type="button" @click="loadPreset('vacation_plan')" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-gray-600 transition">
                        <svg class="w-3.5 h-3.5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <span x-text="t('preset_vacation')">Biaya Liburan</span>
                    </button>
                    <button type="button" @click="loadPreset('split_bill')" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-gray-600 transition">
                        <svg class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span x-text="t('preset_split_bill')">Split Bill & Pajak</span>
                    </button>
                </div>

                <!-- Shortcuts to insert Finora variables (Bilingual) -->
                <div class="flex items-center flex-wrap gap-1 text-xs">
                    <span class="text-gray-400 dark:text-gray-500 font-medium mr-1" x-text="t('insert_data')">Sisipkan Data:</span>
                    <button type="button" @click="insertText(locale === 'en' ? '@balance' : '@saldo')" class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 font-mono hover:bg-emerald-100" x-text="locale === 'en' ? '@balance' : '@saldo'"></button>
                    <button type="button" @click="insertText(locale === 'en' ? '@income' : '@pemasukan')" class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 font-mono hover:bg-blue-100" x-text="locale === 'en' ? '@income' : '@pemasukan'"></button>
                    <button type="button" @click="insertText(locale === 'en' ? '@expense' : '@pengeluaran')" class="px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 font-mono hover:bg-amber-100" x-text="locale === 'en' ? '@expense' : '@pengeluaran'"></button>
                    <button type="button" @click="insertText(locale === 'en' ? '@debt' : '@utang')" class="px-2 py-0.5 rounded bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 font-mono hover:bg-rose-100" x-text="locale === 'en' ? '@debt' : '@utang'"></button>
                </div>
            </div>

            <!-- Main Editor + Output Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                <!-- Left: Interactive Text Editor -->
                <div class="lg:col-span-8 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs flex flex-col overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50/75 dark:bg-gray-800/80 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-400 inline-block"></span>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-200 ml-2" x-text="t('editor_title')">Editor Coretan Cerdas</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="clearScratchpad()" class="text-xs text-gray-500 hover:text-rose-600 dark:hover:text-rose-400 font-medium transition" x-text="t('clear')">
                                Bersihkan
                            </button>
                            <span class="text-gray-300 dark:text-gray-600">|</span>
                            <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span x-text="t('auto_saved')">Tersimpan Otomatis</span>
                            </span>
                        </div>
                    </div>

                    <div class="relative flex-1 min-h-[360px] p-4 flex gap-3 font-mono text-sm">
                        <!-- Line Numbers -->
                        <div class="select-none text-right text-gray-300 dark:text-gray-600 pr-2 border-r border-gray-100 dark:border-gray-700 font-mono text-xs pt-1 space-y-2" style="line-height: 1.75rem;">
                            <template x-for="(line, idx) in lineCount" :key="idx">
                                <div x-text="idx + 1"></div>
                            </template>
                        </div>

                        <!-- Textarea Area -->
                        <div class="relative flex-1">
                            <textarea
                                id="scratchpad-input"
                                x-model="scratchpadText"
                                @input="evaluateScratchpad()"
                                rows="14"
                                :placeholder="t('editor_placeholder')"
                                class="w-full h-full bg-transparent border-0 focus:ring-0 p-0 text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 resize-none font-mono text-sm leading-7 outline-none"
                                spellcheck="false"
                            ></textarea>
                        </div>
                    </div>

                    <!-- Bottom Cheatsheet Hint -->
                    <div class="px-4 py-2.5 bg-gray-50/50 dark:bg-gray-800/40 border-t border-gray-100 dark:border-gray-700/60 text-[11px] text-gray-500 dark:text-gray-400 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="t('format_tips') + ':'">Tips Format:</span>
                            <span x-text="t('format_hint')">`50k`, `1.5jt` / `1.5m`, `20% of 500k`, `x` / `*`.</span>
                        </div>
                        <div>
                            <span x-text="t('comment_hint')">Komentar dengan `# Catatan`</span>
                        </div>
                    </div>
                </div>

                <!-- Right: Live Result Column & Actions -->
                <div class="lg:col-span-4 flex flex-col gap-4">
                    <!-- Results Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs flex-1 flex flex-col overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50/75 dark:bg-gray-800/80 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-200" x-text="t('realtime_results')">Hasil Evaluasi Real-Time</span>
                            <span class="text-[11px] font-semibold text-gray-400" x-text="results.filter(r => r.numericValue !== null).length + ' ' + t('computations')"></span>
                        </div>

                        <div class="p-3 flex-1 overflow-y-auto max-h-[380px] space-y-1.5">
                            <template x-for="(item, idx) in results" :key="idx">
                                <div class="group flex items-center justify-between p-2 rounded-lg text-xs transition"
                                     :class="item.isComment ? 'opacity-40 text-gray-400' : (item.numericValue !== null ? 'hover:bg-emerald-50/60 dark:hover:bg-emerald-950/30' : '')">
                                    <div class="flex items-center gap-2 truncate pr-2">
                                        <span class="text-[10px] font-mono text-gray-300 dark:text-gray-600" x-text="'L' + (idx + 1)"></span>
                                        <span class="truncate font-medium text-gray-600 dark:text-gray-300" x-text="item.label || item.raw"></span>
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <template x-if="item.numericValue !== null">
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400"
                                                      x-text="formatCurrency(item.numericValue)"></span>

                                                <!-- Quick Add to Finora Transaction Button -->
                                                <button type="button"
                                                        @click="openQuickTransactionModal(item)"
                                                        :title="t('record_to_finora_tooltip')"
                                                        class="opacity-0 group-hover:opacity-100 p-1 rounded hover:bg-emerald-100 dark:hover:bg-emerald-900 text-emerald-700 dark:text-emerald-300 transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                        <template x-if="item.numericValue === null && !item.isComment && item.raw.trim().length > 0">
                                            <span class="text-[10px] text-gray-400 italic">—</span>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="results.length === 0">
                                <div class="py-12 text-center text-gray-400 text-xs" x-text="t('empty_scratchpad')">
                                    Ketik di editor untuk melihat hasil kalkulasi instan.
                                </div>
                            </template>
                        </div>

                        <!-- Grand Summary Strip -->
                        <div class="p-4 bg-emerald-50/70 dark:bg-emerald-950/40 border-t border-emerald-100 dark:border-emerald-900/50">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-emerald-800 dark:text-emerald-300 font-semibold" x-text="t('final_result')">Hasil Akhir (Baris Terakhir):</span>
                                <span class="text-xs font-mono font-black text-emerald-700 dark:text-emerald-400 text-right"
                                      x-text="lastValidResult ? formatCurrency(lastValidResult.numericValue) : formatCurrency(0)"></span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-500 dark:text-gray-400 font-medium" x-text="t('grand_total')">Akumulasi Total:</span>
                                <span class="text-xs font-mono font-bold text-gray-700 dark:text-gray-200 text-right"
                                      x-text="formatCurrency(grandSum)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-xs space-y-2">
                        <button type="button" @click="copySummaryToClipboard()"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-900 dark:bg-gray-700 hover:bg-black dark:hover:bg-gray-600 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <span x-text="copied ? t('copied') : t('copy_summary')">Salin Rangkuman Teks</span>
                        </button>

                        <button type="button" @click="downloadSummaryTxt()"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-semibold transition">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span x-text="t('download_txt')">Unduh File Catatan (.txt)</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- TAB 2: WORK-HOURS & IMPULSE BUY CHECKER                        -->
        <!-- ============================================================== -->
        <div x-show="activeTab === 'impulse'" x-cloak class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Input Form -->
                <div class="lg:col-span-5 bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-xs space-y-4">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <div class="p-1.5 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <span x-text="t('work_hours_title')">Kalkulator Jam Kerja & Belanja Impulsif</span>
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="t('work_hours_subtitle')">
                            Berapa jam kerja keringat kamu yang harus ditukar untuk membeli barang ini?
                        </p>
                    </div>

                    <!-- Item Name -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1" x-text="t('item_name')">Nama Barang / Keinginan</label>
                        <input type="text" x-model="impulse.itemName" :placeholder="t('item_name_placeholder')"
                               class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none text-gray-800 dark:text-white">
                    </div>

                    <!-- Item Price -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1" x-text="t('item_price') + ' (' + currencySymbol + ')'">Harga Barang</label>
                        <input type="number" x-model.number="impulse.itemPrice" min="0" step="50000" placeholder="1500000"
                               class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none text-gray-800 dark:text-white font-mono">
                    </div>

                    <!-- Monthly Income -->
                    <div class="pt-2 border-t border-gray-100 dark:border-gray-700 space-y-3">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-semibold text-gray-700 dark:text-gray-300" x-text="t('monthly_income') + ' (' + currencySymbol + ')'">Gaji Bersih Bulanan</label>
                                <button type="button" @click="impulse.monthlyIncome = userIncome" class="text-[11px] text-emerald-600 dark:text-emerald-400 hover:underline" x-text="t('use_finora_data')">
                                    Pakai Data Finora
                                </button>
                            </div>
                            <input type="number" x-model.number="impulse.monthlyIncome" min="100000" step="100000"
                                   class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none text-gray-800 dark:text-white font-mono">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1" x-text="t('working_days')">Hari Kerja / Bulan</label>
                                <input type="number" x-model.number="impulse.workingDays" min="1" max="31"
                                       class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none text-gray-800 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1" x-text="t('working_hours')">Jam Kerja / Hari</label>
                                <input type="number" x-model.number="impulse.hoursPerDay" min="1" max="24"
                                       class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none text-gray-800 dark:text-white">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Analysis & Psychological Verdict -->
                <div class="lg:col-span-7 space-y-4">
                    <!-- Stat Highlights -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs">
                            <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1" x-text="t('hourly_worth')">Nilai Waktu Kerja Anda</span>
                            <span class="text-lg font-black text-gray-900 dark:text-white" x-text="formatCurrency(impulseHourlyRate) + ' / ' + t('hour_unit')"></span>
                            <span class="text-[11px] text-gray-400 block mt-0.5" x-text="formatCurrency(impulseDailyRate) + ' / ' + t('day_unit')"></span>
                        </div>

                        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs">
                            <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1" x-text="t('required_hours')">Waktu Kerja Dibutuhkan</span>
                            <span class="text-lg font-black text-emerald-600 dark:text-emerald-400" x-text="impulseWorkHours.toFixed(1) + ' ' + t('hours_unit')"></span>
                            <span class="text-[11px] text-gray-400 block mt-0.5" x-text="t('equivalent_to') + ' ' + impulseWorkDays.toFixed(1) + ' ' + t('days_unit')"></span>
                        </div>

                        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs">
                            <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1" x-text="t('income_share')">Porsi Gaji Bulanan</span>
                            <span class="text-lg font-black text-amber-600 dark:text-amber-400" x-text="impulseIncomeShare.toFixed(1) + '%'"></span>
                            <span class="text-[11px] text-gray-400 block mt-0.5" x-text="t('of_total_income')">dari total pemasukan</span>
                        </div>
                    </div>

                    <!-- Verdict Card -->
                    <div class="p-6 rounded-2xl border transition-all"
                         :class="{
                             'bg-emerald-50/80 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200': impulseVerdict.level === 'safe',
                             'bg-amber-50/80 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200': impulseVerdict.level === 'warning',
                             'bg-rose-50/80 dark:bg-rose-950/30 border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-200': impulseVerdict.level === 'danger'
                         }">
                        <div class="flex items-start gap-4">
                            <template x-if="impulseVerdict.level === 'safe'">
                                <div class="p-2.5 rounded-xl bg-emerald-100 dark:bg-emerald-900/60 text-emerald-600 dark:text-emerald-300 shrink-0">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                            </template>
                            <template x-if="impulseVerdict.level === 'warning'">
                                <div class="p-2.5 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-600 dark:text-amber-300 shrink-0">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                </div>
                            </template>
                            <template x-if="impulseVerdict.level === 'danger'">
                                <div class="p-2.5 rounded-xl bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-300 shrink-0">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                            </template>
                            <div class="space-y-1">
                                <h3 class="text-sm font-bold tracking-tight uppercase" x-text="impulseVerdict.title"></h3>
                                <p class="text-xs leading-relaxed" x-text="impulseVerdict.description"></p>
                            </div>
                        </div>

                        <!-- 48-Hour Cooling Off Timer Feature -->
                        <div class="mt-4 pt-4 border-t border-current/15 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="text-xs">
                                <span class="font-bold" x-text="t('cooling_rule_title')">Aturan 48 Jam (Cooling-off Rule):</span>
                                <span class="block text-[11px] opacity-80" x-text="t('cooling_rule_desc')">Tunda checkout selama 48 jam. Jika setelah 2 hari masih terasa butuh, baru beli.</span>
                            </div>

                            <template x-if="!coolingCountdown.active">
                                <button type="button" @click="startCoolingTimer()"
                                        class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-white dark:bg-gray-800 text-gray-800 dark:text-white shadow-xs hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span x-text="t('start_48h')">Mulai Tahan 48 Jam</span>
                                </button>
                            </template>
                            <template x-if="coolingCountdown.active">
                                <div class="shrink-0 flex items-center gap-2 bg-white/70 dark:bg-gray-800/80 px-3 py-1.5 rounded-xl border border-current/20 text-xs font-mono font-bold">
                                    <span class="animate-pulse text-rose-500">●</span>
                                    <span x-text="coolingCountdown.remainingText"></span>
                                    <button type="button" @click="cancelCoolingTimer()" class="text-gray-400 hover:text-rose-600 ml-1 text-xs">✕</button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Opportunity Cost Comparison -->
                    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                <span x-text="t('opp_cost_title')">Opportunity Cost (Jika Uang Ini Diinvestasikan)</span>
                            </h4>
                            <span class="text-[11px] text-gray-400 font-medium" x-text="t('opp_cost_assumption')">Asumsi return 8% / tahun</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                            <div class="p-3 bg-gray-50 dark:bg-gray-900/60 rounded-xl">
                                <span class="text-gray-400 block text-[11px]" x-text="t('in_1_year')">Dalam 1 Tahun</span>
                                <span class="font-mono font-bold text-gray-800 dark:text-gray-200 text-sm" x-text="formatCurrency(impulse.itemPrice * 1.08)"></span>
                            </div>
                            <div class="p-3 bg-gray-50 dark:bg-gray-900/60 rounded-xl">
                                <span class="text-gray-400 block text-[11px]" x-text="t('in_3_years')">Dalam 3 Tahun</span>
                                <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400 text-sm" x-text="formatCurrency(impulse.itemPrice * Math.pow(1.08, 3))"></span>
                            </div>
                            <div class="p-3 bg-gray-50 dark:bg-gray-900/60 rounded-xl">
                                <span class="text-gray-400 block text-[11px]" x-text="t('in_5_years')">Dalam 5 Tahun</span>
                                <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400 text-sm" x-text="formatCurrency(impulse.itemPrice * Math.pow(1.08, 5))"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- TAB 3: LOAN & DEBT AMORTIZATION SIMULATOR                     -->
        <!-- ============================================================== -->
        <div x-show="activeTab === 'loan'" x-cloak class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Loan Parameters Form -->
                <div class="lg:col-span-5 bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-xs space-y-4">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <div class="p-1.5 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                            </div>
                            <span x-text="t('loan_title')">Simulasi Angsuran & Bunga Utang</span>
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="t('loan_subtitle')">
                            Bandingkan skema anuitas efektif (KPR/Bank) vs bunga flat (KTA/Kendaraan/Paylater).
                        </p>
                    </div>

                    <!-- Principal -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1" x-text="t('loan_principal') + ' (' + currencySymbol + ')'">Plafon Pinjaman (Pokok)</label>
                        <input type="number" x-model.number="loan.principal" min="100000" step="500000"
                               class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none text-gray-800 dark:text-white font-mono">
                    </div>

                    <!-- Interest & Tenor -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1" x-text="t('loan_interest')">Bunga / Tahun (%)</label>
                            <input type="number" x-model.number="loan.rateAnnual" min="0" max="100" step="0.1"
                                   class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none text-gray-800 dark:text-white font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1" x-text="t('loan_tenor')">Tenor (Bulan)</label>
                            <input type="number" x-model.number="loan.tenorMonths" min="1" max="360"
                                   class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none text-gray-800 dark:text-white font-mono">
                        </div>
                    </div>

                    <!-- Loan Method Switch -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5" x-text="t('interest_method')">Metode Perhitungan Bunga</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="loan.method = 'annuity'"
                                    :class="loan.method === 'annuity' ? 'bg-emerald-600 text-white font-bold' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium'"
                                    class="py-2 px-3 rounded-xl text-xs transition" x-text="t('annuity')">
                                Anuitas (Efektif Bank)
                            </button>
                            <button type="button" @click="loan.method = 'flat'"
                                    :class="loan.method === 'flat' ? 'bg-emerald-600 text-white font-bold' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium'"
                                    class="py-2 px-3 rounded-xl text-xs transition" x-text="t('flat')">
                                Bunga Flat (Leasing)
                            </button>
                        </div>
                    </div>

                    <!-- Monthly Income for DSR -->
                    <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1" x-text="t('income_for_dsr')">Gaji Anda untuk Cek Beban Cicilan (DSR)</label>
                        <input type="number" x-model.number="loan.userIncome" min="100000" step="100000"
                               class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none text-gray-800 dark:text-white font-mono">
                    </div>
                </div>

                <!-- Calculation Results & Amortization -->
                <div class="lg:col-span-7 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs">
                            <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1" x-text="t('monthly_payment')">Cicilan per Bulan</span>
                            <span class="text-lg font-black text-emerald-600 dark:text-emerald-400" x-text="formatCurrency(loanMonthlyPayment)"></span>
                            <span class="text-[11px] text-gray-400 block mt-0.5" x-text="loan.tenorMonths + ' ' + t('installments_count')"></span>
                        </div>

                        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs">
                            <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1" x-text="t('total_interest')">Total Bunga</span>
                            <span class="text-lg font-black text-rose-600 dark:text-rose-400" x-text="formatCurrency(loanTotalInterest)"></span>
                            <span class="text-[11px] text-gray-400 block mt-0.5" x-text="((loanTotalInterest / (loan.principal || 1)) * 100).toFixed(1) + '% ' + t('of_principal')"></span>
                        </div>

                        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs">
                            <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1" x-text="t('total_payment')">Total Pengembalian</span>
                            <span class="text-lg font-black text-gray-900 dark:text-white" x-text="formatCurrency(loanTotalPayment)"></span>
                            <span class="text-[11px] text-gray-400 block mt-0.5" x-text="t('principal_plus_interest')">Pokok + Total Bunga</span>
                        </div>
                    </div>

                    <!-- DSR (Debt Service Ratio) Meter -->
                    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-gray-800 dark:text-gray-200" x-text="t('dsr_title')">Rasio Beban Cicilan (Debt Service Ratio):</span>
                            <span class="font-mono font-bold"
                                  :class="loanDsr > 35 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'"
                                  x-text="loanDsr.toFixed(1) + '% ' + t('of_salary')"></span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full h-2.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-300"
                                 :class="loanDsr > 35 ? 'bg-rose-500' : (loanDsr > 25 ? 'bg-amber-500' : 'bg-emerald-500')"
                                 :style="'width: ' + Math.min(loanDsr, 100) + '%'"></div>
                        </div>

                        <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-relaxed flex items-center gap-1.5">
                            <template x-if="loanDsr <= 30">
                                <span class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-medium">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span x-html="t('safe_dsr_desc')"></span>
                                </span>
                            </template>
                            <template x-if="loanDsr > 30">
                                <span class="flex items-center gap-1 text-rose-600 dark:text-rose-400 font-medium">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    <span x-html="t('risky_dsr_desc')"></span>
                                </span>
                            </template>
                        </p>
                    </div>

                    <!-- Amortization Schedule Preview -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs overflow-hidden">
                        <div class="px-5 py-3.5 bg-gray-50/75 dark:bg-gray-800/80 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                            <h4 class="text-xs font-bold text-gray-800 dark:text-gray-200" x-text="t('amortization_title')">Pratinjau Jadwal Angsuran</h4>
                            <span class="text-[11px] text-gray-400" x-text="t('months_1_to_6')">Bulan 1 s/d 6</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-900/60 text-gray-500 dark:text-gray-400 font-semibold border-b border-gray-100 dark:border-gray-700">
                                        <th class="p-3" x-text="t('month')">Bulan</th>
                                        <th class="p-3" x-text="t('installment')">Angsuran</th>
                                        <th class="p-3" x-text="t('principal_part')">Porsi Pokok</th>
                                        <th class="p-3" x-text="t('interest_part')">Porsi Bunga</th>
                                        <th class="p-3" x-text="t('remaining_balance')">Sisa Pokok</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-mono text-[11px]">
                                    <template x-for="row in loanAmortizationPreview" :key="row.month">
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30">
                                            <td class="p-3 font-semibold text-gray-700 dark:text-gray-300" x-text="t('month') + ' ' + row.month"></td>
                                            <td class="p-3 text-gray-900 dark:text-white font-bold" x-text="formatCurrency(row.payment)"></td>
                                            <td class="p-3 text-emerald-600 dark:text-emerald-400" x-text="formatCurrency(row.principalPart)"></td>
                                            <td class="p-3 text-rose-500 dark:text-rose-400" x-text="formatCurrency(row.interestPart)"></td>
                                            <td class="p-3 text-gray-500 dark:text-gray-400" x-text="formatCurrency(row.remaining)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- TAB 4: COMPOUNDING HABITS & SUBSCRIPTIONS                      -->
        <!-- ============================================================== -->
        <div x-show="activeTab === 'compounding'" x-cloak class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Controls Form -->
                <div class="lg:col-span-5 bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-xs space-y-4">
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <div class="p-1.5 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            </div>
                            <span x-text="t('compounding_title')">Kalkulator Efek Kopi & Kebiasaan Mikro</span>
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-text="t('compounding_subtitle')">
                            Berapa nilai uang pengeluaran rutin Anda jika dialihkan ke instrumen investasi majemuk?
                        </p>
                    </div>

                    <!-- Dynamic Habits List & Management -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                <span x-text="t('habit_examples')">Contoh Kebiasaan & Langganan</span>
                                <span class="text-gray-400 font-normal" x-text="'(' + habits.length + ' item)'"></span>
                            </label>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="resetHabitsToDefault()" class="text-[11px] text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition" x-text="t('reset')">
                                    Reset
                                </button>
                                <button type="button" @click="openAddHabit()" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span x-text="'+ ' + t('add_habit')">+ Tambah</span>
                                </button>
                            </div>
                        </div>

                        <!-- Inline Add/Edit Habit Form (Now with Daily vs Monthly Frequency!) -->
                        <div x-show="isEditingHabit" x-cloak class="mb-3 p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-emerald-200 dark:border-emerald-800/50 space-y-2.5">
                            <div class="flex items-center justify-between text-xs font-bold text-gray-800 dark:text-gray-200">
                                <span x-text="habitForm.id ? t('edit_habit') : t('add_new_habit')"></span>
                                <button type="button" @click="isEditingHabit = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">✕</button>
                            </div>
                            <div class="space-y-2">
                                <input type="text" x-model="habitForm.name" :placeholder="t('habit_name_placeholder')"
                                       class="w-full px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none text-gray-800 dark:text-white">
                                
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="flex items-center bg-gray-200 dark:bg-gray-800 p-0.5 rounded-lg text-xs">
                                        <button type="button" @click="habitForm.frequency = 'daily'"
                                                :class="habitForm.frequency === 'daily' ? 'bg-white dark:bg-gray-700 text-emerald-600 dark:text-emerald-400 font-bold shadow-xs' : 'text-gray-500 dark:text-gray-400'"
                                                class="flex-1 py-1 rounded-md text-[11px] transition text-center" x-text="t('daily')">
                                            Harian
                                        </button>
                                        <button type="button" @click="habitForm.frequency = 'monthly'"
                                                :class="habitForm.frequency === 'monthly' ? 'bg-white dark:bg-gray-700 text-emerald-600 dark:text-emerald-400 font-bold shadow-xs' : 'text-gray-500 dark:text-gray-400'"
                                                class="flex-1 py-1 rounded-md text-[11px] transition text-center" x-text="t('monthly')">
                                            Bulanan
                                        </button>
                                    </div>

                                    <div class="relative">
                                        <span class="absolute left-2.5 top-1.5 text-xs text-gray-400">{{ $currencySymbol }}</span>
                                        <input type="number" x-model.number="habitForm.amount" min="1000" step="1000" :placeholder="habitForm.frequency === 'daily' ? t('amount_per_day') : t('amount_per_month')"
                                               class="w-full pl-8 pr-2.5 py-1.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none text-gray-800 dark:text-white font-mono">
                                    </div>
                                </div>

                                <div class="flex justify-end pt-1">
                                    <button type="button" @click="saveHabitForm()"
                                            class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition">
                                        <span x-text="t('save')">Simpan</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Habit Cards Grid -->
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <template x-for="item in habits" :key="item.id">
                                <div @click="selectHabit(item)"
                                     :class="selectedHabitId === item.id 
                                         ? 'border-emerald-500 bg-emerald-50/70 dark:bg-emerald-950/40 ring-1 ring-emerald-500' 
                                         : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800/90'"
                                     class="group relative p-2.5 rounded-xl border text-left cursor-pointer transition flex flex-col justify-between">
                                    
                                    <div class="flex items-start justify-between gap-1 mb-1">
                                        <div class="flex items-center gap-1.5 truncate">
                                            <div class="w-1.5 h-1.5 rounded-full shrink-0"
                                                 :class="selectedHabitId === item.id ? 'bg-emerald-500' : 'bg-gray-300 dark:bg-gray-600'"></div>
                                            <span class="font-bold text-gray-800 dark:text-white truncate text-[11px]" x-text="item.name"></span>
                                        </div>

                                        <!-- Edit & Delete Action Buttons -->
                                        <div class="opacity-0 group-hover:opacity-100 flex items-center gap-0.5 shrink-0 transition" @click.stop>
                                            <button type="button" @click.stop="openEditHabit(item)" :title="t('edit')"
                                                    class="p-0.5 text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </button>
                                            <button type="button" @click.stop="deleteHabit(item.id)" :title="t('delete')"
                                                    class="p-0.5 text-gray-400 hover:text-rose-600 dark:hover:text-rose-400">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between text-[10px]">
                                        <span class="font-mono text-gray-500 dark:text-gray-400"
                                              x-text="formatCurrency(item.amount) + ' / ' + (item.frequency === 'monthly' ? t('month_short') : t('day_short'))"></span>
                                        <span class="px-1.5 py-0.2 rounded font-semibold text-[9px] uppercase tracking-wider"
                                              :class="item.frequency === 'monthly' ? 'bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"
                                              x-text="item.frequency === 'monthly' ? t('monthly') : t('daily')"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <template x-if="habits.length === 0">
                            <div class="py-4 text-center text-xs text-gray-400 border border-dashed border-gray-200 dark:border-gray-700 rounded-xl" x-text="t('empty_habits')">
                                Belum ada contoh kebiasaan. Klik <strong>+ Tambah</strong> untuk menambahkan.
                            </div>
                        </template>
                    </div>

                    <!-- Frequency & Amount Input (Supports Daily OR Monthly) -->
                    <div class="pt-2 border-t border-gray-100 dark:border-gray-700 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300">
                                <span x-text="compound.frequency === 'monthly' ? t('amount_per_month') : t('amount_per_day')">Pengeluaran</span>
                                <span x-text="'(' + currencySymbol + ')'"></span>
                            </label>

                            <!-- Frequency Switcher [ Daily | Monthly ] -->
                            <div class="flex items-center bg-gray-100 dark:bg-gray-700 p-0.5 rounded-lg text-xs">
                                <button type="button" @click="setCompoundFrequency('daily')"
                                        :class="compound.frequency === 'daily' ? 'bg-white dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 font-bold shadow-xs' : 'text-gray-500 dark:text-gray-400'"
                                        class="px-2.5 py-0.5 rounded-md text-[11px] transition" x-text="t('daily')">
                                    Harian
                                </button>
                                <button type="button" @click="setCompoundFrequency('monthly')"
                                        :class="compound.frequency === 'monthly' ? 'bg-white dark:bg-gray-800 text-emerald-600 dark:text-emerald-400 font-bold shadow-xs' : 'text-gray-500 dark:text-gray-400'"
                                        class="px-2.5 py-0.5 rounded-md text-[11px] transition" x-text="t('monthly')">
                                    Bulanan
                                </button>
                            </div>
                        </div>

                        <input type="number" x-model.number="compound.amount" @input="renderCompoundChart()" min="1000" step="5000"
                               class="w-full px-3.5 py-2.5 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none text-gray-800 dark:text-white font-mono">
                        
                        <!-- Real-time Equivalence Display -->
                        <div class="text-[11px] text-gray-400 flex items-center justify-between">
                            <template x-if="compound.frequency === 'daily'">
                                <span x-text="t('equivalent_to') + ' ' + formatCurrency(compound.amount * 30) + ' / ' + t('month_short')"></span>
                            </template>
                            <template x-if="compound.frequency === 'monthly'">
                                <span x-text="t('equivalent_to') + ' ~' + formatCurrency(compound.amount / 30) + ' / ' + t('day_short')"></span>
                            </template>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400" x-text="formatCurrency(compoundMonthlyContribution) + ' / ' + t('month_short')"></span>
                        </div>
                    </div>

                    <!-- Horizon Sliders -->
                    <div class="space-y-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <label class="font-semibold text-gray-700 dark:text-gray-300" x-text="t('investment_duration')">Durasi Investasi:</label>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="compound.years + ' ' + t('years_unit')"></span>
                            </div>
                            <input type="range" x-model.number="compound.years" @input="renderCompoundChart()" min="1" max="20" step="1"
                                   class="w-full accent-emerald-600">
                        </div>

                        <div>
                            <div class="flex items-center justify-between text-xs mb-1">
                                <label class="font-semibold text-gray-700 dark:text-gray-300" x-text="t('annual_return')">Estimasi Imbal Hasil (Return / thn):</label>
                                <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="compound.annualReturn + '%'"></span>
                            </div>
                            <input type="range" x-model.number="compound.annualReturn" @input="renderCompoundChart()" min="3" max="15" step="0.5"
                                   class="w-full accent-emerald-600">
                        </div>
                    </div>
                </div>

                <!-- Chart & Highlights -->
                <div class="lg:col-span-7 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs">
                            <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1" x-text="t('money_spent')">Uang Yang Dihabiskan</span>
                            <span class="text-lg font-black text-rose-500 dark:text-rose-400" x-text="formatCurrency(compoundSpentTotal)"></span>
                            <span class="text-[11px] text-gray-400 block mt-0.5" x-text="t('spent_desc')">Jika terus dikonsumsi</span>
                        </div>

                        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs">
                            <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1" x-text="t('compound_result')">Hasil Investasi Majemuk</span>
                            <span class="text-lg font-black text-emerald-600 dark:text-emerald-400" x-text="formatCurrency(compoundInvestedTotal)"></span>
                            <span class="text-[11px] text-gray-400 block mt-0.5" x-text="t('compound_desc')">Pokok + Bunga majemuk</span>
                        </div>

                        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs">
                            <span class="text-xs text-gray-500 dark:text-gray-400 block mb-1" x-text="t('net_profit')">Keuntungan Bersih (Profit)</span>
                            <span class="text-lg font-black text-teal-600 dark:text-teal-400" x-text="formatCurrency(compoundProfitTotal)"></span>
                            <span class="text-[11px] text-gray-400 block mt-0.5" x-text="t('profit_desc')">Uang yang bekerja untuk Anda</span>
                        </div>
                    </div>

                    <!-- Visual Comparison Chart -->
                    <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-xs">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-xs font-bold text-gray-800 dark:text-gray-200" x-text="t('growth_chart')">Grafik Pertumbuhan Akumulasi</h4>
                            <span class="text-[11px] text-gray-400">
                                <span x-text="t('year_1_to')">Tahun ke-1 s/d Tahun ke-</span><span x-text="compound.years"></span>
                            </span>
                        </div>
                        <div class="h-64 relative w-full">
                            <canvas id="compoundChart" class="w-full h-full"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- MODAL: QUICK RECORD TO FINORA TRANSACTION                      -->
        <!-- ============================================================== -->
        <div x-show="quickTxModal.open" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            <div @click.away="quickTxModal.open = false"
                 class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-100 dark:border-gray-700 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <div class="p-1.5 bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-lg">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/></svg>
                        </div>
                        <span x-text="t('record_tx_title')">Catat ke Transaksi Finora</span>
                    </h3>
                    <button type="button" @click="quickTxModal.open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="t('record_tx_desc')">
                    Nominal hasil kalkulator akan otomatis tersimpan langsung ke pembukuan Finora Anda.
                </p>

                <div class="space-y-3">
                    <!-- Title -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1" x-text="t('tx_description')">Deskripsi Transaksi</label>
                        <input type="text" x-model="quickTxModal.title" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none text-gray-800 dark:text-white">
                    </div>

                    <!-- Amount & Type -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1" x-text="t('tx_amount')">Nominal</label>
                            <input type="number" x-model.number="quickTxModal.amount" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none text-gray-800 dark:text-white font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1" x-text="t('tx_type')">Tipe</label>
                            <select x-model="quickTxModal.type" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none text-gray-800 dark:text-white">
                                <option value="expense" x-text="t('tx_expense')">Pengeluaran (Expense)</option>
                                <option value="income" x-text="t('tx_income')">Pemasukan (Income)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Wallet & Category -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1" x-text="t('tx_wallet')">Dompet / Akun</label>
                            <select x-model="quickTxModal.wallet_id" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none text-gray-800 dark:text-white">
                                @foreach ($wallets as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->currency ?? 'IDR' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1" x-text="t('tx_category')">Kategori</label>
                            <select x-model="quickTxModal.category_id" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none text-gray-800 dark:text-white">
                                <option value="" x-text="t('no_category')">Tanpa Kategori</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Transaction Date -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1" x-text="t('tx_date')">Tanggal Transaksi</label>
                        <input type="date" x-model="quickTxModal.transaction_date" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none text-gray-800 dark:text-white">
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-end gap-2">
                    <button type="button" @click="quickTxModal.open = false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition" x-text="t('cancel')">
                        Batal
                    </button>
                    <button type="button" @click="submitQuickTransaction()" :disabled="quickTxModal.submitting"
                            class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition flex items-center gap-1.5">
                        <span x-text="quickTxModal.submitting ? t('saving') : t('save_tx')">Simpan Transaksi</span>
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Smart Calculator Engine Script (Alpine.js) -->
    <script>
        // Standalone Chart.js instance outside Alpine reactivity
        let finoraCompoundChart = null;

        document.addEventListener('alpine:init', () => {
            Alpine.data('smartCalculator', (config) => ({
                activeTab: 'scratchpad',
                currencySymbol: config.currencySymbol || 'Rp',
                userBalance: config.userBalance || 0,
                userIncome: config.userIncome || 5000000,
                userExpense: config.userExpense || 0,
                userDebt: config.userDebt || 0,
                locale: localStorage.getItem('finora_calculator_locale') || config.userLocale || 'id',

                // Scratchpad State
                scratchpadText: '',
                results: [],
                lineCount: 14,
                copied: false,

                // Impulse Buy State
                impulse: {
                    itemName: '',
                    itemPrice: 1500000,
                    monthlyIncome: config.userIncome || 5000000,
                    workingDays: 21,
                    hoursPerDay: 8
                },
                coolingCountdown: {
                    active: false,
                    targetTime: null,
                    remainingText: '',
                    intervalId: null
                },

                // Loan State
                loan: {
                    principal: 50000000,
                    rateAnnual: 9.5,
                    tenorMonths: 24,
                    method: 'annuity',
                    userIncome: config.userIncome || 5000000
                },

                // Compounding State & Dynamic Habits (Frequency: daily or monthly)
                compound: {
                    frequency: 'daily',
                    amount: 35000,
                    years: 5,
                    annualReturn: 8
                },
                selectedHabitId: 1,
                isEditingHabit: false,
                habitForm: {
                    id: null,
                    name: '',
                    amount: 25000,
                    frequency: 'daily'
                },
                habits: [],

                // Quick Transaction Modal
                quickTxModal: {
                    open: false,
                    submitting: false,
                    title: '',
                    amount: 0,
                    type: 'expense',
                    wallet_id: '{{ $wallets->first()?->id ?? "" }}',
                    category_id: '',
                    transaction_date: new Date().toISOString().split('T')[0]
                },

                // Multilingual Dictionary (Indonesian & English)
                translations: {
                    id: {
                        banner_badge: 'Bukan Sekadar Kalkulator Biasa',
                        banner_title: 'Kalkulator Finansial Pintar',
                        banner_subtitle: 'Eksplorasi kalkulasi dengan coretan teks alami (ala Soulver), hitung harga barang dalam jam kerja riil, simulasikan cicilan utang, dan lihat dampak kebiasaan kecil dalam jangka panjang.',
                        your_balance: 'Saldo Anda:',
                        monthly_inflow: 'Pemasukan Bulan Ini:',
                        tab_scratchpad: 'Smart Scratchpad',
                        tab_impulse: 'Jam Kerja & Belanja Impulsif',
                        tab_loan: 'Simulasi Cicilan & Bunga',
                        tab_compounding: 'Efek Kopi (Compounding)',
                        quick_presets: 'Preset Cepat:',
                        preset_budget: 'Budget 50/30/20',
                        preset_cashflow: 'Arus Kas Bulanan',
                        preset_vacation: 'Biaya Liburan',
                        preset_split_bill: 'Split Bill & Pajak',
                        insert_data: 'Sisipkan Data:',
                        editor_title: 'Editor Coretan Cerdas',
                        clear: 'Bersihkan',
                        auto_saved: 'Tersimpan Otomatis',
                        editor_placeholder: "Tuliskan catatan keuangan Anda di sini...\nContoh:\nGaji Pokok = 8.5jt\nBonus = 1.2jt\nTotal Pemasukan = Gaji Pokok + Bonus\n\nSewa Kos = 1.75jt\nMakan 30 hari x 45k\nSisa Uang = Total Pemasukan - (Sewa Kos + Makan)",
                        format_tips: 'Tips Format',
                        format_hint: '`50k` (ribu), `1.5jt` / `1.5m`, `20% dari 500k`, `x` atau `*`.',
                        comment_hint: 'Komentar dengan `# Catatan`',
                        realtime_results: 'Hasil Evaluasi Real-Time',
                        computations: 'hitungan',
                        record_to_finora_tooltip: 'Catat baris ini ke Transaksi Finora',
                        empty_scratchpad: 'Ketik di editor untuk melihat hasil kalkulasi instan.',
                        final_result: 'Hasil Akhir (Baris Terakhir):',
                        grand_total: 'Akumulasi Total:',
                        copy_summary: 'Salin Rangkuman Teks',
                        copied: 'Tersalin ke Clipboard!',
                        download_txt: 'Unduh File Catatan (.txt)',
                        work_hours_title: 'Kalkulator Jam Kerja & Belanja Impulsif',
                        work_hours_subtitle: 'Berapa jam kerja keringat kamu yang harus ditukar untuk membeli barang ini?',
                        item_name: 'Nama Barang / Keinginan',
                        item_name_placeholder: 'cth: Smartphone Baru, Sepatu Sneakers',
                        item_price: 'Harga Barang',
                        monthly_income: 'Gaji Bersih Bulanan',
                        use_finora_data: 'Pakai Data Finora',
                        working_days: 'Hari Kerja / Bulan',
                        working_hours: 'Jam Kerja / Hari',
                        hourly_worth: 'Nilai Waktu Kerja Anda',
                        hour_unit: 'jam',
                        day_unit: 'hari',
                        required_hours: 'Waktu Kerja Dibutuhkan',
                        hours_unit: 'Jam',
                        days_unit: 'hari kerja penuh',
                        equivalent_to: 'Setara',
                        income_share: 'Porsi Gaji Bulanan',
                        of_total_income: 'dari total pemasukan',
                        cooling_rule_title: 'Aturan 48 Jam (Cooling-off Rule):',
                        cooling_rule_desc: 'Tunda checkout selama 48 jam. Jika setelah 2 hari masih terasa butuh, baru beli.',
                        start_48h: 'Mulai Tahan 48 Jam',
                        opp_cost_title: 'Opportunity Cost (Jika Uang Ini Diinvestasikan)',
                        opp_cost_assumption: 'Asumsi return 8% / tahun',
                        in_1_year: 'Dalam 1 Tahun',
                        in_3_years: 'Dalam 3 Tahun',
                        in_5_years: 'Dalam 5 Tahun',
                        loan_title: 'Simulasi Angsuran & Bunga Utang',
                        loan_subtitle: 'Bandingkan skema anuitas efektif (KPR/Bank) vs bunga flat (KTA/Kendaraan/Paylater).',
                        loan_principal: 'Plafon Pinjaman (Pokok)',
                        loan_interest: 'Bunga / Tahun (%)',
                        loan_tenor: 'Tenor (Bulan)',
                        interest_method: 'Metode Perhitungan Bunga',
                        annuity: 'Anuitas (Efektif Bank)',
                        flat: 'Bunga Flat (Leasing)',
                        income_for_dsr: 'Gaji Anda untuk Cek Beban Cicilan (DSR)',
                        monthly_payment: 'Cicilan per Bulan',
                        installments_count: 'kali angsuran',
                        total_interest: 'Total Bunga',
                        of_principal: 'dari pokok pinjaman',
                        total_payment: 'Total Pengembalian',
                        principal_plus_interest: 'Pokok + Total Bunga',
                        dsr_title: 'Rasio Beban Cicilan (Debt Service Ratio):',
                        of_salary: 'dari Gaji',
                        safe_dsr_desc: '<strong>Batas Aman:</strong> DSR di bawah 30% tergolong sehat menurut standar perencana keuangan.',
                        risky_dsr_desc: '<strong>Peringatan Risiko:</strong> DSR melebihi 30% berisiko mengganggu tabungan dana darurat & kebutuhan pokok.',
                        amortization_title: 'Pratinjau Jadwal Angsuran',
                        months_1_to_6: 'Bulan 1 s/d 6',
                        month: 'Bulan',
                        installment: 'Angsuran',
                        principal_part: 'Porsi Pokok',
                        interest_part: 'Porsi Bunga',
                        remaining_balance: 'Sisa Pokok',
                        compounding_title: 'Kalkulator Efek Kopi & Kebiasaan Mikro',
                        compounding_subtitle: 'Berapa nilai uang pengeluaran rutin Anda jika dialihkan ke instrumen investasi majemuk?',
                        habit_examples: 'Contoh Kebiasaan & Langganan',
                        reset: 'Reset',
                        add_habit: 'Tambah',
                        add_new_habit: 'Tambah Kebiasaan Baru',
                        edit_habit: 'Edit Kebiasaan',
                        habit_name_placeholder: 'Nama kebiasaan / langganan (mis: Kopi, Netflix)',
                        daily: 'Harian',
                        monthly: 'Bulanan',
                        day_short: 'hari',
                        month_short: 'bln',
                        amount_per_day: 'Nominal per hari',
                        amount_per_month: 'Nominal per bulan',
                        save: 'Simpan',
                        edit: 'Edit',
                        delete: 'Hapus',
                        empty_habits: 'Belum ada contoh kebiasaan. Klik + Tambah untuk menambahkan.',
                        investment_duration: 'Durasi Investasi:',
                        years_unit: 'Tahun',
                        annual_return: 'Estimasi Imbal Hasil (Return / thn):',
                        money_spent: 'Uang Yang Dihabiskan',
                        spent_desc: 'Jika terus dikonsumsi',
                        compound_result: 'Hasil Investasi Majemuk',
                        compound_desc: 'Pokok + Bunga majemuk',
                        net_profit: 'Keuntungan Bersih (Profit)',
                        profit_desc: 'Uang yang bekerja untuk Anda',
                        growth_chart: 'Grafik Pertumbuhan Akumulasi',
                        year_1_to: 'Tahun ke-1 s/d Tahun ke-',
                        record_tx_title: 'Catat ke Transaksi Finora',
                        record_tx_desc: 'Nominal hasil kalkulator akan otomatis tersimpan langsung ke pembukuan Finora Anda.',
                        tx_description: 'Deskripsi Transaksi',
                        tx_amount: 'Nominal',
                        tx_type: 'Tipe',
                        tx_expense: 'Pengeluaran (Expense)',
                        tx_income: 'Pemasukan (Income)',
                        tx_wallet: 'Dompet / Akun',
                        tx_category: 'Kategori',
                        no_category: 'Tanpa Kategori',
                        tx_date: 'Tanggal Transaksi',
                        cancel: 'Batal',
                        save_tx: 'Simpan Transaksi',
                        saving: 'Menyimpan...'
                    },
                    en: {
                        banner_badge: 'More than an ordinary calculator',
                        banner_title: 'Smart Financial Calculator',
                        banner_subtitle: 'Explore intuitive natural language calculations (Soulver-style), analyze opportunity costs in real work-hours, simulate loan amortizations, and project compound habits.',
                        your_balance: 'Your Balance:',
                        monthly_inflow: 'This Month\'s Inflow:',
                        tab_scratchpad: 'Smart Scratchpad',
                        tab_impulse: 'Work-Hours & Impulse Buy',
                        tab_loan: 'Loan & Debt Simulation',
                        tab_compounding: 'Habit Compounding',
                        quick_presets: 'Quick Presets:',
                        preset_budget: '50/30/20 Budget',
                        preset_cashflow: 'Monthly Cash Flow',
                        preset_vacation: 'Vacation Budget',
                        preset_split_bill: 'Split Bill & Tax',
                        insert_data: 'Insert Variables:',
                        editor_title: 'Smart Scratchpad Editor',
                        clear: 'Clear',
                        auto_saved: 'Auto-saved',
                        editor_placeholder: "Type your financial notes here naturally...\nExample:\nBase Salary = 8.5m\nBonus = 1.2m\nTotal Income = Base Salary + Bonus\n\nApartment Rent = 1.75m\nLunch 30 days x 45k\nSurplus = Total Income - (Apartment Rent + Lunch)",
                        format_tips: 'Syntax Tips',
                        format_hint: '`50k` (thousand), `1.5m` (million), `20% of 500k`, `x` or `*`.',
                        comment_hint: 'Comment with `# Note`',
                        realtime_results: 'Real-Time Evaluation',
                        computations: 'calculations',
                        record_to_finora_tooltip: 'Record this line to Finora Transactions',
                        empty_scratchpad: 'Type in the editor to see instant real-time results.',
                        final_result: 'Final Value (Last Line):',
                        grand_total: 'Accumulated Total:',
                        copy_summary: 'Copy Summary Text',
                        copied: 'Copied to Clipboard!',
                        download_txt: 'Download Note (.txt)',
                        work_hours_title: 'Work-Hours & Impulse Buy Checker',
                        work_hours_subtitle: 'How many hours of your hard work are traded to afford this purchase?',
                        item_name: 'Item / Wishlist Name',
                        item_name_placeholder: 'e.g. New Smartphone, Designer Sneakers',
                        item_price: 'Item Price',
                        monthly_income: 'Net Monthly Income',
                        use_finora_data: 'Use Finora Data',
                        working_days: 'Working Days / Month',
                        working_hours: 'Working Hours / Day',
                        hourly_worth: 'Your Hourly Worth',
                        hour_unit: 'hour',
                        day_unit: 'day',
                        required_hours: 'Work-Hours Required',
                        hours_unit: 'Hours',
                        days_unit: 'full working days',
                        equivalent_to: 'Equivalent to',
                        income_share: 'Share of Monthly Income',
                        of_total_income: 'of total monthly income',
                        cooling_rule_title: '48-Hour Cooling-Off Rule:',
                        cooling_rule_desc: 'Delay checkout for 48 hours. If you still genuinely need it after 2 days, buy it with confidence.',
                        start_48h: 'Start 48-Hour Delay',
                        opp_cost_title: 'Opportunity Cost (If Invested Instead)',
                        opp_cost_assumption: 'Assuming 8% annual return',
                        in_1_year: 'In 1 Year',
                        in_3_years: 'In 3 Years',
                        in_5_years: 'In 5 Years',
                        loan_title: 'Loan Payment & Interest Simulation',
                        loan_subtitle: 'Compare effective annuity (Bank/Mortgage) vs flat interest (Auto loan/Personal loan).',
                        loan_principal: 'Loan Principal',
                        loan_interest: 'Annual Interest Rate (%)',
                        loan_tenor: 'Tenure (Months)',
                        interest_method: 'Interest Calculation Method',
                        annuity: 'Annuity (Effective Bank)',
                        flat: 'Flat Interest (Leasing)',
                        income_for_dsr: 'Your Monthly Income for DSR Check',
                        monthly_payment: 'Monthly Payment',
                        installments_count: 'installments',
                        total_interest: 'Total Interest',
                        of_principal: 'of principal',
                        total_payment: 'Total Repayment',
                        principal_plus_interest: 'Principal + Total Interest',
                        dsr_title: 'Debt Service Ratio (DSR):',
                        of_salary: 'of Income',
                        safe_dsr_desc: '<strong>Safe Zone:</strong> DSR below 30% is considered healthy by financial planning benchmarks.',
                        risky_dsr_desc: '<strong>Risk Alert:</strong> DSR exceeding 30% risks compromising your emergency fund and essential needs.',
                        amortization_title: 'Amortization Schedule Preview',
                        months_1_to_6: 'Month 1 to 6',
                        month: 'Month',
                        installment: 'Payment',
                        principal_part: 'Principal',
                        interest_part: 'Interest',
                        remaining_balance: 'Remaining',
                        compounding_title: 'Habit Compounding & Coffee Effect',
                        compounding_subtitle: 'How much would your recurring minor expenses grow into if invested with compound interest?',
                        habit_examples: 'Habits & Subscriptions',
                        reset: 'Reset',
                        add_habit: 'Add',
                        add_new_habit: 'Add New Habit / Subscription',
                        edit_habit: 'Edit Habit',
                        habit_name_placeholder: 'Habit / subscription name (e.g. Daily Coffee, Netflix)',
                        daily: 'Daily',
                        monthly: 'Monthly',
                        day_short: 'day',
                        month_short: 'mo',
                        amount_per_day: 'Amount per day',
                        amount_per_month: 'Amount per month',
                        save: 'Save',
                        edit: 'Edit',
                        delete: 'Delete',
                        empty_habits: 'No habits configured yet. Click + Add to create one.',
                        investment_duration: 'Investment Horizon:',
                        years_unit: 'Years',
                        annual_return: 'Estimated Annual Return (%):',
                        money_spent: 'Total Money Spent',
                        spent_desc: 'If consumed continuously',
                        compound_result: 'Compounded Future Value',
                        compound_desc: 'Principal + Compound returns',
                        net_profit: 'Net Gain (Profit)',
                        profit_desc: 'Money generated by your capital',
                        growth_chart: 'Cumulative Growth Trajectory',
                        year_1_to: 'Year 1 to Year ',
                        record_tx_title: 'Record to Finora Transactions',
                        record_tx_desc: 'This evaluated amount will be instantly recorded into your Finora accounts.',
                        tx_description: 'Transaction Description',
                        tx_amount: 'Amount',
                        tx_type: 'Type',
                        tx_expense: 'Expense',
                        tx_income: 'Income',
                        tx_wallet: 'Wallet / Account',
                        tx_category: 'Category',
                        no_category: 'No Category',
                        tx_date: 'Transaction Date',
                        cancel: 'Cancel',
                        save_tx: 'Save Transaction',
                        saving: 'Saving...'
                    }
                },

                t(key) {
                    if (this.translations[this.locale] && this.translations[this.locale][key]) {
                        return this.translations[this.locale][key];
                    }
                    if (this.translations['id'] && this.translations['id'][key]) {
                        return this.translations['id'][key];
                    }
                    return key;
                },

                setLocale(loc) {
                    this.locale = loc;
                    localStorage.setItem('finora_calculator_locale', loc);
                    // Re-render chart so tooltip & legend update labels
                    this.renderCompoundChart();

                    // Dynamically update the sidebar label if present
                    const sidebarLabel = document.getElementById('sidebar-calculator-label');
                    if (sidebarLabel) {
                        sidebarLabel.textContent = loc === 'en' ? 'Smart Calculator' : 'Kalkulator Pintar';
                    }
                },

                init() {
                    // Sync sidebar label with current locale
                    const sidebarLabel = document.getElementById('sidebar-calculator-label');
                    if (sidebarLabel) {
                        sidebarLabel.textContent = this.locale === 'en' ? 'Smart Calculator' : 'Kalkulator Pintar';
                    }

                    // Restore Scratchpad from localStorage
                    const saved = localStorage.getItem('finora_smart_scratchpad');
                    if (saved) {
                        this.scratchpadText = saved;
                    } else {
                        this.loadPreset('budget_503020');
                    }
                    this.evaluateScratchpad();

                    // Restore Cooling timer if running
                    const savedTimer = localStorage.getItem('finora_cooling_timer');
                    if (savedTimer) {
                        const target = parseInt(savedTimer, 10);
                        if (target > Date.now()) {
                            this.coolingCountdown.active = true;
                            this.coolingCountdown.targetTime = target;
                            this.tickCoolingTimer();
                            this.coolingCountdown.intervalId = setInterval(() => this.tickCoolingTimer(), 1000);
                        } else {
                            localStorage.removeItem('finora_cooling_timer');
                        }
                    }

                    // Load dynamic compounding habits
                    this.loadHabits();

                    // Listen to theme switch to gracefully re-color chart
                    window.addEventListener('finora-theme-changed', () => {
                        if (this.activeTab === 'compounding') {
                            this.renderCompoundChart();
                        }
                    });

                    // Listen to window resize to resize chart
                    window.addEventListener('resize', () => {
                        if (this.activeTab === 'compounding' && finoraCompoundChart) {
                            finoraCompoundChart.resize();
                        }
                    });
                },

                switchTab(tab) {
                    this.activeTab = tab;

                    const mainEl = document.querySelector('main');
                    if (mainEl) {
                        mainEl.scrollTop = 0;
                    }
                    window.scrollTo({ top: 0, behavior: 'instant' });

                    if (tab === 'compounding') {
                        this.$nextTick(() => {
                            setTimeout(() => {
                                this.renderCompoundChart();
                                if (finoraCompoundChart) {
                                    finoraCompoundChart.resize();
                                }
                            }, 50);
                        });
                    }
                },

                formatCurrency(val) {
                    if (val === null || val === undefined || isNaN(val)) return '—';
                    const num = Math.round(Number(val));
                    const loc = this.locale === 'en' ? 'en-US' : 'id-ID';
                    return this.currencySymbol + ' ' + num.toLocaleString(loc);
                },

                // -------------------------------------------------------------
                // SMART SCRATCHPAD PARSER & EVALUATOR ENGINE (EN & ID SUPPORT)
                // -------------------------------------------------------------
                evaluateScratchpad() {
                    const text = this.scratchpadText;
                    localStorage.setItem('finora_smart_scratchpad', text);

                    const lines = text.split('\n');
                    this.lineCount = Math.max(lines.length, 14);

                    const scope = {
                        'saldo': this.userBalance,
                        'balance': this.userBalance,
                        'pemasukan': this.userIncome,
                        'income': this.userIncome,
                        'inflow': this.userIncome,
                        'pengeluaran': this.userExpense,
                        'expense': this.userExpense,
                        'outflow': this.userExpense,
                        'utang': this.userDebt,
                        'debt': this.userDebt,
                        'gaji': this.userIncome,
                        'salary': this.userIncome
                    };

                    const results = [];

                    for (let i = 0; i < lines.length; i++) {
                        const raw = lines[i];
                        const trimmed = raw.trim();

                        if (!trimmed) {
                            results.push({ raw, label: '', numericValue: null, isComment: false });
                            continue;
                        }

                        // Check comment
                        if (trimmed.startsWith('#') || trimmed.startsWith('//')) {
                            results.push({ raw, label: trimmed.replace(/^[#/]+\s*/, ''), numericValue: null, isComment: true });
                            continue;
                        }

                        // Try to evaluate this line
                        try {
                            const evalResult = this.parseLine(trimmed, scope);
                            results.push({
                                raw,
                                label: evalResult.label || trimmed,
                                numericValue: evalResult.value,
                                isComment: false
                            });
                        } catch (e) {
                            results.push({ raw, label: trimmed, numericValue: null, isComment: false });
                        }
                    }

                    this.results = results;
                },

                parseLine(line, scope) {
                    let expression = line;
                    let label = '';

                    // Check assignment: Label = Expression
                    if (line.includes('=')) {
                        const parts = line.split('=');
                        label = parts[0].trim();
                        expression = parts.slice(1).join('=').trim();
                    } else if (line.includes(':')) {
                        const parts = line.split(':');
                        label = parts[0].trim();
                        expression = parts.slice(1).join(':').trim();
                    }

                    let expr = expression;

                    // Replace @finora tags (Supports both Indonesian & English)
                    expr = expr.replace(/@(saldo|balance)/gi, scope['balance'] || 0);
                    expr = expr.replace(/@(pemasukan|income|inflow)/gi, scope['income'] || 0);
                    expr = expr.replace(/@(pengeluaran|expense|outflow)/gi, scope['expense'] || 0);
                    expr = expr.replace(/@(utang|debt)/gi, scope['debt'] || 0);
                    expr = expr.replace(/@(gaji|salary)/gi, scope['salary'] || 0);

                    // Replace "hari x" / "days *" / "months *" with *
                    expr = expr.replace(/\s*(?:hari|days?|months?|bulan)\s*[x*]\s*/gi, ' * ');
                    expr = expr.replace(/\s*[xX]\s*/g, ' * ');
                    expr = expr.replace(/\s*(?:times|kali)\s*/gi, ' * ');

                    // Replace "dari" / "of" / "off" with *
                    expr = expr.replace(/\s*(?:dari|of)\s*/gi, ' * ');

                    // Convert Billions: 2.5b / 2.5miliar -> * 1000000000
                    expr = expr.replace(/(\d+(?:[.,]\d+)?)\s*(?:b|bn|billion|miliar)\b/gi, (match, n) => {
                        return (parseFloat(n.replace(',', '.')) * 1000000000);
                    });

                    // Convert Millions: 1.5m / 1.5mil / 1.5million / 1.5jt / 1.5juta -> * 1000000
                    expr = expr.replace(/(\d+(?:[.,]\d+)?)\s*(?:m|mil|million|jt|juta)\b/gi, (match, n) => {
                        return (parseFloat(n.replace(',', '.')) * 1000000);
                    });

                    // Convert Thousands: 50k / 50grand / 50thousand / 50rb / 50ribu -> * 1000
                    expr = expr.replace(/(\d+(?:[.,]\d+)?)\s*(?:k|grand|thousand|rb|ribu)\b/gi, (match, n) => {
                        return (parseFloat(n.replace(',', '.')) * 1000);
                    });

                    // Percentages: 20% -> (20/100)
                    expr = expr.replace(/(\d+(?:[.,]\d+)?)\s*%/g, (match, p) => {
                        return '(' + parseFloat(p.replace(',', '.')) + '/100)';
                    });

                    // Clean currency symbols ($ / Rp / IDR / USD)
                    expr = expr.replace(/(?:rp|idr|\$|usd|eur|gbp)\s*/gi, '');

                    // Replace recognized variable names from scope
                    const varKeys = Object.keys(scope).sort((a, b) => b.length - a.length);
                    for (const vk of varKeys) {
                        const regex = new RegExp('\\b' + vk.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\b', 'gi');
                        expr = expr.replace(regex, '(' + scope[vk] + ')');
                    }

                    // Remove descriptive text before first math token
                    expr = expr.replace(/^[a-zA-Z\s]+(?=[0-9(])/i, '');

                    // Remove thousand dots like 1.500.000 -> 1500000
                    expr = expr.replace(/(\d{1,3})\.(\d{3})/g, '$1$2');

                    // Sanitize expression: only allow numbers, math operators, parens, spaces
                    const sanitized = expr.replace(/[^0-9+\-*/().\s]/g, '');

                    if (!sanitized.trim()) {
                        return { label, value: null };
                    }

                    let evaluated = null;
                    try {
                        const calcFn = new Function('return (' + sanitized + ')');
                        const res = calcFn();
                        if (typeof res === 'number' && !isNaN(res) && isFinite(res)) {
                            evaluated = res;
                        }
                    } catch (e) {
                        evaluated = null;
                    }

                    if (label && evaluated !== null) {
                        scope[label.toLowerCase()] = evaluated;
                    }

                    return { label, value: evaluated };
                },

                get grandSum() {
                    return this.results
                        .filter(r => r.numericValue !== null)
                        .reduce((acc, curr) => acc + curr.numericValue, 0);
                },

                get lastValidResult() {
                    const valid = this.results.filter(r => r.numericValue !== null);
                    return valid.length > 0 ? valid[valid.length - 1] : null;
                },

                insertText(snippet) {
                    const el = document.getElementById('scratchpad-input');
                    if (!el) return;
                    const start = el.selectionStart;
                    const end = el.selectionEnd;
                    const val = this.scratchpadText;
                    this.scratchpadText = val.substring(0, start) + snippet + val.substring(end);
                    this.$nextTick(() => {
                        el.focus();
                        el.setSelectionRange(start + snippet.length, start + snippet.length);
                        this.evaluateScratchpad();
                    });
                },

                loadPreset(type) {
                    const isEn = this.locale === 'en';

                    if (type === 'budget_503020') {
                        this.scratchpadText = isEn
? `# 50/30/20 Budgeting Rule
Net Salary = @income
Bonus = 500k
Total Inflow = Net Salary + Bonus

# 1. Essential Needs (50%)
Needs Limit = Total Inflow * 50%
Rent & Housing = 1.8m
Electricity and Wifi = 450k
Groceries and Food = 40k * 30 days
Total Needs = Rent & Housing + Electricity and Wifi + Groceries and Food

# 2. Wants & Leisure (30%)
Wants Limit = Total Inflow * 30%
Dining Out and Hobbies = 600k
Shopping = 400k

# 3. Savings and Investments (20%)
Savings Target = Total Inflow * 20%
Unallocated Surplus = Total Inflow - (Total Needs + Dining Out and Hobbies + Shopping + Savings Target)`
: `# Rencana Budget Bulanan (Metode 50/30/20)
Gaji Bersih = @pemasukan
Bonus = 500k
Total Pemasukan = Gaji Bersih + Bonus

# 1. Kebutuhan Pokok (50%)
Limit Kebutuhan = Total Pemasukan * 50%
Sewa Tempat Tinggal = 1.8jt
Listrik dan Wifi = 450k
Makan Sebulan = 40k * 30 hari
Total Kebutuhan = Sewa Tempat Tinggal + Listrik dan Wifi + Makan Sebulan

# 2. Keinginan & Hiburan (30%)
Limit Keinginan = Total Pemasukan * 30%
Nongkrong dan Hobi = 600k
Belanja Baju = 400k

# 3. Tabungan & Investasi (20%)
Target Tabungan = Total Pemasukan * 20%
Sisa Dana Bebas = Total Pemasukan - (Total Kebutuhan + Nongkrong dan Hobi + Belanja Baju + Target Tabungan)`;
                    } else if (type === 'monthly_cashflow') {
                        this.scratchpadText = isEn
? `# Monthly Cash Flow Simulation
Opening Balance = @balance
Expected Salary = @income
Monthly Bills = 1.2m
Groceries = 1.5m
Daily Meals = 35k * 30

Disposable Surplus = Opening Balance + Expected Salary - (Monthly Bills + Groceries + Daily Meals)`
: `# Simulasi Arus Kas Real-Time Finora
Saldo Awal Finora = @saldo
Estimasi Gaji = @pemasukan
Tagihan Bulanan = 1.2jt
Belanja Dapur = 1.5jt
Makan Harian = 35k * 30

Sisa Uang Dingin = Saldo Awal Finora + Estimasi Gaji - (Tagihan Bulanan + Belanja Dapur + Makan Harian)`;
                    } else if (type === 'vacation_plan') {
                        this.scratchpadText = isEn
? `# Vacation Budget Plan (2 Persons)
Flight Tickets Roundtrip = 1.25m * 2
Hotel 3 Nights = 450k * 3
Food and Dining = 200k * 4 days
Scooter Rental = 80k * 3
Attractions and Tickets = 500k

Total Vacation Cost = Flight Tickets Roundtrip + Hotel 3 Nights + Food and Dining + Scooter Rental + Attractions and Tickets
Savings per Month for 4 Months = Total Vacation Cost / 4`
: `# Rencana Liburan Akhir Tahun (2 Orang)
Tiket Pesawat PP = 1.25jt * 2
Hotel 3 Malam = 450k * 3
Budget Kuliner = 200k * 4 hari
Sewa Motor = 80k * 3
Biaya Wisata dan Tiket = 500k

Total Estimasi Liburan = Tiket Pesawat PP + Hotel 3 Malam + Budget Kuliner + Sewa Motor + Biaya Wisata dan Tiket
Tabungan Selama 4 Bulan = Total Estimasi Liburan / 4`;
                    } else if (type === 'split_bill') {
                        this.scratchpadText = isEn
? `# Restaurant Split Bill
Order Alex = 65k + 22k
Order Sarah = 45k + 18k
Order Me = 80k + 25k

Subtotal Food = Order Alex + Order Sarah + Order Me
Sales Tax = Subtotal Food * 10%
Service Charge = Subtotal Food * 5%
Grand Total Restaurant = Subtotal Food + Sales Tax + Service Charge

Tax and Service Factor = 1 + (15 / 100)
Share Alex = Order Alex * Tax and Service Factor
Share Sarah = Order Sarah * Tax and Service Factor
My Share = Order Me * Tax and Service Factor`
: `# Split Bill Makan Bersama
Pesanan Budi = 65k + 22k
Pesanan Siti = 45k + 18k
Pesanan Saya = 80k + 25k

Subtotal Makanan = Pesanan Budi + Pesanan Siti + Pesanan Saya
Pajak PB1 = Subtotal Makanan * 10%
Service Charge = Subtotal Makanan * 5%
Grand Total Resto = Subtotal Makanan + Pajak PB1 + Service Charge

Faktor Pajak dan Servis = 1 + (15 / 100)
Tagihan Budi = Pesanan Budi * Faktor Pajak dan Servis
Tagihan Siti = Pesanan Siti * Faktor Pajak dan Servis
Bagian Saya = Pesanan Saya * Faktor Pajak dan Servis`;
                    }
                    this.evaluateScratchpad();
                },

                clearScratchpad() {
                    const msg = this.locale === 'en' ? 'Clear all notes in the editor?' : 'Bersihkan seluruh catatan di editor?';
                    if (confirm(msg)) {
                        this.scratchpadText = '';
                        this.evaluateScratchpad();
                    }
                },

                copySummaryToClipboard() {
                    let summary = `=== FINORA SMART CALCULATOR (${this.locale.toUpperCase()}) ===\n\n`;
                    for (const r of this.results) {
                        if (r.isComment) {
                            summary += `[${r.label}]\n`;
                        } else if (r.numericValue !== null) {
                            summary += `${r.label || r.raw}: ${this.formatCurrency(r.numericValue)}\n`;
                        }
                    }
                    summary += `\n----------------------------------\n`;
                    summary += `${this.t('final_result').toUpperCase()} ${this.lastValidResult ? this.formatCurrency(this.lastValidResult.numericValue) : this.formatCurrency(0)}\n`;
                    summary += `${this.t('grand_total').toUpperCase()} ${this.formatCurrency(this.grandSum)}\n`;

                    navigator.clipboard.writeText(summary).then(() => {
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2500);
                    });
                },

                downloadSummaryTxt() {
                    let summary = `=== FINORA FINANCIAL SCRATCHPAD ===\n\n`;
                    for (const r of this.results) {
                        if (r.isComment) {
                            summary += `# ${r.label}\n`;
                        } else if (r.numericValue !== null) {
                            summary += `${r.label || r.raw} = ${this.formatCurrency(r.numericValue)}\n`;
                        }
                    }
                    const blob = new Blob([summary], { type: 'text/plain;charset=utf-8' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `finora-scratchpad-${new Date().toISOString().split('T')[0]}.txt`;
                    a.click();
                    URL.revokeObjectURL(url);
                },

                // -------------------------------------------------------------
                // QUICK RECORD MODAL (SCRATCHPAD TO FINORA TRANSACTION)
                // -------------------------------------------------------------
                openQuickTransactionModal(item) {
                    this.quickTxModal.title = item.label || item.raw.replace(/=/g, '').trim();
                    this.quickTxModal.amount = Math.abs(Math.round(item.numericValue || 0));
                    this.quickTxModal.type = 'expense';
                    this.quickTxModal.open = true;
                },

                submitQuickTransaction() {
                    if (!this.quickTxModal.title || !this.quickTxModal.amount) {
                        alert(this.locale === 'en' ? 'Please provide description and amount.' : 'Silakan lengkapi deskripsi dan nominal.');
                        return;
                    }

                    this.quickTxModal.submitting = true;
                    fetch('{{ route("calculator.transaction") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            title: this.quickTxModal.title,
                            amount: this.quickTxModal.amount,
                            type: this.quickTxModal.type,
                            wallet_id: this.quickTxModal.wallet_id || null,
                            category_id: this.quickTxModal.category_id || null,
                            transaction_date: this.quickTxModal.transaction_date,
                            notes: 'Recorded via Finora Smart Calculator'
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.quickTxModal.submitting = false;
                        if (data.success) {
                            this.quickTxModal.open = false;
                            alert(this.locale === 'en' ? 'Transaction recorded to Finora successfully!' : 'Transaksi berhasil dicatat ke Finora!');
                        } else {
                            alert(this.locale === 'en' ? 'Failed to record transaction.' : 'Gagal mencatat transaksi.');
                        }
                    })
                    .catch(err => {
                        this.quickTxModal.submitting = false;
                        alert(this.locale === 'en' ? 'Network error occurred.' : 'Terjadi kesalahan koneksi.');
                    });
                },

                // -------------------------------------------------------------
                // IMPULSE & WORK HOURS COMPUTATIONS
                // -------------------------------------------------------------
                get impulseHourlyRate() {
                    const income = this.impulse.monthlyIncome || 1;
                    const days = this.impulse.workingDays || 21;
                    const hours = this.impulse.hoursPerDay || 8;
                    return income / (days * hours);
                },

                get impulseDailyRate() {
                    const income = this.impulse.monthlyIncome || 1;
                    const days = this.impulse.workingDays || 21;
                    return income / days;
                },

                get impulseWorkHours() {
                    const price = this.impulse.itemPrice || 0;
                    return price / (this.impulseHourlyRate || 1);
                },

                get impulseWorkDays() {
                    const price = this.impulse.itemPrice || 0;
                    return price / (this.impulseDailyRate || 1);
                },

                get impulseIncomeShare() {
                    const price = this.impulse.itemPrice || 0;
                    const income = this.impulse.monthlyIncome || 1;
                    return (price / income) * 100;
                },

                get impulseVerdict() {
                    const share = this.impulseIncomeShare;
                    const isEn = this.locale === 'en';

                    if (share <= 5) {
                        return {
                            level: 'safe',
                            title: isEn ? 'Light Expense (< 5% of Income)' : 'Porsi Belanja Ringan (< 5% Gaji)',
                            description: isEn 
                                ? 'This purchase is relatively modest and safe from disrupting your monthly cash flow.' 
                                : 'Barang ini masih tergolong wajar dan relatif aman dari risiko mengganggu arus kas bulanan Anda.'
                        };
                    } else if (share <= 20) {
                        return {
                            level: 'warning',
                            title: isEn ? 'Consider Carefully (5% - 20% of Income)' : 'Perlu Pertimbangan Matang (5% - 20% Gaji)',
                            description: isEn 
                                ? 'This represents several full days of your work. Ensure essential needs and savings are funded first.' 
                                : 'Nilainya setara beberapa hari kerja Anda. Pastikan pos kebutuhan pokok dan tabungan bulan ini sudah aman sebelum membeli.'
                        };
                    } else {
                        return {
                            level: 'danger',
                            title: isEn ? 'High Impulse Warning (> 20% of Income)' : 'Peringatan Impulsif Tinggi (> 20% Gaji)',
                            description: isEn 
                                ? 'Significant commitment! You must work over a week just to fund this. The 48-hour rule is strongly advised.' 
                                : 'Sangat signifikan! Anda harus bekerja lebih dari 1 minggu penuh hanya untuk mendanai barang ini. Sangat disarankan gunakan aturan 48 jam.'
                        };
                    }
                },

                startCoolingTimer() {
                    const target = Date.now() + (48 * 60 * 60 * 1000);
                    localStorage.setItem('finora_cooling_timer', target.toString());
                    this.coolingCountdown.active = true;
                    this.coolingCountdown.targetTime = target;
                    this.tickCoolingTimer();
                    this.coolingCountdown.intervalId = setInterval(() => this.tickCoolingTimer(), 1000);
                },

                cancelCoolingTimer() {
                    clearInterval(this.coolingCountdown.intervalId);
                    localStorage.removeItem('finora_cooling_timer');
                    this.coolingCountdown.active = false;
                    this.coolingCountdown.targetTime = null;
                },

                tickCoolingTimer() {
                    if (!this.coolingCountdown.targetTime) return;
                    const diff = this.coolingCountdown.targetTime - Date.now();
                    if (diff <= 0) {
                        this.cancelCoolingTimer();
                        alert(this.locale === 'en' 
                            ? '48-Hour waiting period completed! Re-evaluate: do you still genuinely need this item?' 
                            : 'Waktu 48 Jam telah selesai! Evaluasi kembali: apakah Anda benar-benar masih membutuhkan barang ini?');
                        return;
                    }
                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const secs = Math.floor((diff % (1000 * 60)) / 1000);
                    const suffix = this.locale === 'en' ? 'remaining' : 'lagi';
                    this.coolingCountdown.remainingText = `${hours}h ${mins}m ${secs}s ${suffix}`;
                },

                // -------------------------------------------------------------
                // LOAN SIMULATION COMPUTATIONS
                // -------------------------------------------------------------
                get loanMonthlyPayment() {
                    const P = this.loan.principal || 0;
                    const r = (this.loan.rateAnnual || 0) / 100 / 12;
                    const n = this.loan.tenorMonths || 1;

                    if (this.loan.method === 'flat') {
                        const monthlyInterest = (P * ((this.loan.rateAnnual || 0) / 100)) / 12;
                        return (P / n) + monthlyInterest;
                    }

                    if (r === 0) return P / n;
                    return P * (r * Math.pow(1 + r, n)) / (Math.pow(1 + r, n) - 1);
                },

                get loanTotalPayment() {
                    return this.loanMonthlyPayment * (this.loan.tenorMonths || 1);
                },

                get loanTotalInterest() {
                    return Math.max(0, this.loanTotalPayment - (this.loan.principal || 0));
                },

                get loanDsr() {
                    const income = this.loan.userIncome || 1;
                    return (this.loanMonthlyPayment / income) * 100;
                },

                get loanAmortizationPreview() {
                    const rows = [];
                    const P = this.loan.principal || 0;
                    const r = (this.loan.rateAnnual || 0) / 100 / 12;
                    const n = this.loan.tenorMonths || 1;
                    const payment = this.loanMonthlyPayment;
                    const count = Math.min(6, n);

                    let remaining = P;

                    for (let m = 1; m <= count; m++) {
                        let interestPart = 0;
                        let principalPart = 0;

                        if (this.loan.method === 'flat') {
                            interestPart = (P * ((this.loan.rateAnnual || 0) / 100)) / 12;
                            principalPart = P / n;
                            remaining = Math.max(0, remaining - principalPart);
                        } else {
                            interestPart = remaining * r;
                            principalPart = payment - interestPart;
                            remaining = Math.max(0, remaining - principalPart);
                        }

                        rows.push({
                            month: m,
                            payment: payment,
                            principalPart: principalPart,
                            interestPart: interestPart,
                            remaining: remaining
                        });
                    }

                    return rows;
                },

                // -------------------------------------------------------------
                // COMPOUNDING HABITS COMPUTATIONS & DYNAMIC SUBSCRIPTIONS
                // -------------------------------------------------------------
                loadHabits() {
                    const saved = localStorage.getItem('finora_compound_habits');
                    if (saved) {
                        try {
                            const parsed = JSON.parse(saved);
                            if (Array.isArray(parsed) && parsed.length > 0) {
                                this.habits = parsed;
                                this.selectedHabitId = this.habits[0].id;
                                this.compound.amount = Number(this.habits[0].amount);
                                this.compound.frequency = this.habits[0].frequency || 'daily';
                                return;
                            }
                        } catch (e) {
                            console.warn('Error reading saved habits:', e);
                        }
                    }
                    this.resetHabitsToDefault();
                },

                saveHabits() {
                    localStorage.setItem('finora_compound_habits', JSON.stringify(this.habits));
                },

                resetHabitsToDefault() {
                    const isEn = this.locale === 'en';
                    this.habits = [
                        { id: 1, name: isEn ? 'Specialty Coffee' : 'Kopi Kafe Kekinian', amount: 35000, frequency: 'daily' },
                        { id: 2, name: isEn ? 'Cigarettes / Vape' : 'Rokok / Vape', amount: 32000, frequency: 'daily' },
                        { id: 3, name: isEn ? 'Boba / Fast Snacks' : 'Jajan Boba / Camilan', amount: 25000, frequency: 'daily' },
                        { id: 4, name: isEn ? 'Streaming Subscriptions' : 'Langganan Netflix / Spotify', amount: 186000, frequency: 'monthly' },
                        { id: 5, name: isEn ? 'Gym & Fitness Membership' : 'Keanggotaan Gym / Olahraga', amount: 450000, frequency: 'monthly' }
                    ];
                    this.selectedHabitId = 1;
                    this.compound.amount = 35000;
                    this.compound.frequency = 'daily';
                    this.saveHabits();
                    this.$nextTick(() => this.renderCompoundChart());
                },

                selectHabit(habit) {
                    this.selectedHabitId = habit.id;
                    this.compound.amount = Number(habit.amount);
                    this.compound.frequency = habit.frequency || 'daily';
                    this.renderCompoundChart();
                },

                setCompoundFrequency(freq) {
                    this.compound.frequency = freq;
                    this.renderCompoundChart();
                },

                openAddHabit() {
                    this.habitForm = {
                        id: null,
                        name: '',
                        amount: 25000,
                        frequency: 'daily'
                    };
                    this.isEditingHabit = true;
                },

                openEditHabit(habit) {
                    this.habitForm = {
                        id: habit.id,
                        name: habit.name,
                        amount: habit.amount,
                        frequency: habit.frequency || 'daily'
                    };
                    this.isEditingHabit = true;
                },

                saveHabitForm() {
                    if (!this.habitForm.name.trim() || !this.habitForm.amount || this.habitForm.amount <= 0) {
                        alert(this.locale === 'en' ? 'Please provide a valid habit name and amount.' : 'Silakan masukkan nama dan nominal yang valid.');
                        return;
                    }

                    if (this.habitForm.id) {
                        const idx = this.habits.findIndex(h => h.id === this.habitForm.id);
                        if (idx !== -1) {
                            this.habits[idx].name = this.habitForm.name.trim();
                            this.habits[idx].amount = Number(this.habitForm.amount);
                            this.habits[idx].frequency = this.habitForm.frequency || 'daily';

                            if (this.selectedHabitId === this.habitForm.id) {
                                this.compound.amount = Number(this.habitForm.amount);
                                this.compound.frequency = this.habitForm.frequency || 'daily';
                            }
                        }
                    } else {
                        const newHabit = {
                            id: Date.now(),
                            name: this.habitForm.name.trim(),
                            amount: Number(this.habitForm.amount),
                            frequency: this.habitForm.frequency || 'daily'
                        };
                        this.habits.push(newHabit);
                        this.selectHabit(newHabit);
                    }

                    this.saveHabits();
                    this.isEditingHabit = false;
                    this.renderCompoundChart();
                },

                deleteHabit(id) {
                    const msg = this.locale === 'en' ? 'Delete this habit from list?' : 'Hapus kebiasaan ini dari daftar?';
                    if (confirm(msg)) {
                        this.habits = this.habits.filter(h => h.id !== id);
                        this.saveHabits();
                        if (this.selectedHabitId === id) {
                            if (this.habits.length > 0) {
                                this.selectHabit(this.habits[0]);
                            } else {
                                this.selectedHabitId = null;
                            }
                        }
                        this.renderCompoundChart();
                    }
                },

                // Calculates Monthly Inflow based on frequency (Daily x 30, or Monthly as-is)
                get compoundMonthlyContribution() {
                    const amt = Number(this.compound.amount) || 0;
                    if (this.compound.frequency === 'monthly') {
                        return amt;
                    }
                    return amt * 30;
                },

                get compoundSpentTotal() {
                    return this.compoundMonthlyContribution * 12 * (Number(this.compound.years) || 1);
                },

                get compoundInvestedTotal() {
                    const PMT = this.compoundMonthlyContribution;
                    const r = (Number(this.compound.annualReturn) || 0) / 100 / 12;
                    const n = (Number(this.compound.years) || 1) * 12;

                    if (r === 0) return PMT * n;
                    return PMT * ((Math.pow(1 + r, n) - 1) / r);
                },

                get compoundProfitTotal() {
                    return Math.max(0, this.compoundInvestedTotal - this.compoundSpentTotal);
                },

                renderCompoundChart() {
                    if (this.activeTab !== 'compounding') return;
                    const canvas = document.getElementById('compoundChart');
                    if (!canvas) return;

                    const years = Math.max(1, Math.min(20, Number(this.compound.years) || 5));
                    const PMT = this.compoundMonthlyContribution;
                    const r = (Number(this.compound.annualReturn) || 0) / 100 / 12;

                    const labels = [];
                    const spentData = [];
                    const investedData = [];

                    const yearPrefix = this.locale === 'en' ? 'Yr ' : 'Thn ';

                    for (let y = 1; y <= years; y++) {
                        labels.push(yearPrefix + y);
                        const n = y * 12;
                        const spent = PMT * n;
                        spentData.push(Math.round(spent));

                        const fv = r === 0 ? spent : PMT * ((Math.pow(1 + r, n) - 1) / r);
                        investedData.push(Math.round(fv));
                    }

                    const isDark = document.documentElement.classList.contains('dark');
                    const isEn = this.locale === 'en';

                    const investedLabel = isEn ? 'Future Value If Invested (Compounding)' : 'Nilai Jika Diinvestasikan (Compounding)';
                    const spentLabel = isEn ? 'Money Lost If Consumed' : 'Uang Hangus Jika Dikonsumsi Terus';

                    const existingChart = finoraCompoundChart || (window.Chart ? Chart.getChart(canvas) : null);

                    if (existingChart) {
                        try {
                            existingChart.data.labels = labels;
                            existingChart.data.datasets[0].label = investedLabel;
                            existingChart.data.datasets[0].data = investedData;
                            existingChart.data.datasets[1].label = spentLabel;
                            existingChart.data.datasets[1].data = spentData;

                            if (existingChart.options.plugins?.legend?.labels) {
                                existingChart.options.plugins.legend.labels.color = isDark ? '#cbd5e1' : '#475569';
                            }
                            if (existingChart.options.scales?.y?.ticks) {
                                existingChart.options.scales.y.ticks.color = isDark ? '#94a3b8' : '#64748b';
                                existingChart.options.scales.y.grid.color = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
                            }
                            if (existingChart.options.scales?.x?.ticks) {
                                existingChart.options.scales.x.ticks.color = isDark ? '#94a3b8' : '#64748b';
                            }

                            existingChart.update('none');
                            finoraCompoundChart = existingChart;
                            return;
                        } catch (err) {
                            console.warn('In-place chart update warning:', err);
                        }
                    }

                    if (window.Chart && Chart.getChart(canvas)) {
                        try {
                            Chart.getChart(canvas).destroy();
                        } catch (_) {}
                    }

                    try {
                        finoraCompoundChart = new Chart(canvas, {
                            type: 'line',
                            data: {
                                labels: labels,
                                datasets: [
                                    {
                                        label: investedLabel,
                                        data: investedData,
                                        borderColor: '#059669',
                                        backgroundColor: 'rgba(5, 150, 105, 0.12)',
                                        fill: true,
                                        tension: 0.35,
                                        borderWidth: 2.5,
                                        pointRadius: 4
                                    },
                                    {
                                        label: spentLabel,
                                        data: spentData,
                                        borderColor: '#f43f5e',
                                        backgroundColor: 'transparent',
                                        borderDash: [5, 5],
                                        borderWidth: 2,
                                        pointRadius: 3
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: false,
                                interaction: { intersect: false, mode: 'index' },
                                plugins: {
                                    legend: {
                                        position: 'top',
                                        labels: {
                                            color: isDark ? '#cbd5e1' : '#475569',
                                            font: { size: 11, weight: '600' }
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: (context) => {
                                                return `${context.dataset.label}: ${this.formatCurrency(context.raw)}`;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        grid: { color: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)' },
                                        ticks: {
                                            color: isDark ? '#94a3b8' : '#64748b',
                                            callback: (val) => {
                                                const kSuffix = isEn ? 'k' : 'rb';
                                                const mSuffix = isEn ? 'm' : 'jt';
                                                if (val >= 1000000) return (val / 1000000) + mSuffix;
                                                if (val >= 1000) return (val / 1000) + kSuffix;
                                                return val;
                                            }
                                        }
                                    },
                                    x: {
                                        grid: { display: false },
                                        ticks: { color: isDark ? '#94a3b8' : '#64748b' }
                                    }
                                }
                            }
                        });
                    } catch (e) {
                        console.error('Failed to instantiate Chart.js:', e);
                    }
                }
            }));
        });
    </script>
</x-app-layout>
