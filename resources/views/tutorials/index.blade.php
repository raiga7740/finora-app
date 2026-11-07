<x-app-layout>
    <x-slot name="title">{{ __('Panduan & Tutorial Fitur') }}</x-slot>

    @php
        $tutorialSearchIndex = [];
        $tutorialCategoriesMap = [];
        foreach ($features as $f) {
            $tutorialSearchIndex[$f['id']] = $f['title'] . ' ' . $f['summary'] . ' ' . ($f['tip'] ?? '') . ' ' . implode(' ', $f['steps'] ?? []);
            $tutorialCategoriesMap[$f['id']] = $f['category'];
        }
    @endphp

    <script>
        window.tutorialHub = function() {
            return {
                searchQuery: '',
                activeCategory: 'all',
                tourOpen: false,
                tourStep: 0,
                tourSteps: @json($tourSteps),
                expandedFeatures: {},
                searchIndex: @json($tutorialSearchIndex),
                categoriesMap: @json($tutorialCategoriesMap),
                totalCount: {{ count($features) }},
                startTour() {
                    this.tourStep = 0;
                    this.tourOpen = true;
                },
                nextTourStep() {
                    if (this.tourStep < this.tourSteps.length - 1) {
                        this.tourStep++;
                    } else {
                        this.tourOpen = false;
                    }
                },
                prevTourStep() {
                    if (this.tourStep > 0) {
                        this.tourStep--;
                    } else {
                        this.tourOpen = false;
                    }
                },
                toggleExpand(id) {
                    this.expandedFeatures[id] = !this.expandedFeatures[id];
                },
                isExpanded(id) {
                    return !!this.expandedFeatures[id];
                },
                isVisible(id, category) {
                    const matchesCat = this.activeCategory === 'all' || this.activeCategory === category;
                    if (!this.searchQuery || !this.searchQuery.trim()) return matchesCat;
                    const q = this.searchQuery.toLowerCase().trim();
                    const text = (this.searchIndex[id] || '').toLowerCase();
                    return matchesCat && text.includes(q);
                },
                get visibleCount() {
                    let count = 0;
                    const q = (this.searchQuery || '').trim().toLowerCase();
                    for (const [id, text] of Object.entries(this.searchIndex || {})) {
                        const cat = this.categoriesMap[id];
                        const matchesCat = this.activeCategory === 'all' || this.activeCategory === cat;
                        if (matchesCat && (!q || text.toLowerCase().includes(q))) {
                            count++;
                        }
                    }
                    return count;
                }
            };
        };
    </script>

    <div x-data="tutorialHub()" class="space-y-7 pb-10">

        {{-- Top Hero Banner Section --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-600 via-[#00c078] to-teal-700 p-6 sm:p-8 md:p-10 text-white shadow-xl">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
            <div class="absolute right-1/4 -top-10 w-48 h-48 rounded-full bg-teal-300/10 blur-xl pointer-events-none"></div>

            <div class="relative z-10 max-w-3xl">
                <button type="button"
                        @click="startTour()"
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-xs font-bold tracking-wider uppercase mb-3.5 hover:bg-white/25 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span>{{ __('Pusat Bantuan & Panduan Finora') }}</span>
                </button>

                <h1 class="text-2xl sm:text-3xl md:text-4xl font-black tracking-tight leading-tight">
                    {{ __('Kuasai Semua Fitur Finora dengan Mudah') }}
                </h1>
                <p class="mt-2 text-xs sm:text-sm md:text-base text-emerald-50 leading-relaxed max-w-2xl">
                    {{ __('Panduan lengkap dan tutorial langkah-demi-langkah cara mengoptimalkan seluruh fitur pintar Finora untuk mencatat, merencanakan, dan mengembangkan aset keuangan Anda.') }}
                </p>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <button type="button"
                            @click="startTour()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-emerald-800 hover:bg-emerald-50 text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition-all cursor-pointer">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ __('Mulai Tur Interaktif (6 Langkah)') }}</span>
                    </button>

                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-700/50 hover:bg-emerald-700/70 border border-white/20 text-white text-xs sm:text-sm font-bold backdrop-blur-md transition-all">
                        <span>{{ __('Kembali ke Dashboard') }}</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- Filter & Real-Time Search Bar --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-4 sm:p-5 shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                {{-- Search Box --}}
                <div class="relative flex-1 max-w-lg">
                    <svg class="w-4 h-4 text-gray-400 dark:text-zinc-500 absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="{{ __('Cari panduan fitur (contoh: split bill, scan struk, dark mode, prediksi)...') }}"
                           class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/80 text-gray-900 dark:text-zinc-100 placeholder-gray-400 dark:placeholder-zinc-500 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition">
                    <button type="button"
                            x-show="searchQuery"
                            @click="searchQuery = ''"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 text-xs cursor-pointer">
                        ✕
                    </button>
                </div>

                {{-- Count & Quick Stats --}}
                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-zinc-400 font-medium">
                    <span class="w-2 h-2 rounded-full bg-[#00c078]"></span>
                    <span><span class="font-bold text-gray-900 dark:text-zinc-100" x-text="visibleCount"></span> {{ __('Fitur Ditampilkan') }}</span>
                </div>
            </div>

            {{-- Category Tab Pills --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-bold scrollbar-none">
                <button type="button"
                        @click="activeCategory = 'all'"
                        :class="activeCategory === 'all' ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200/70 dark:hover:bg-zinc-700 text-gray-600 dark:text-zinc-300'"
                        class="px-4 py-2 rounded-xl transition cursor-pointer whitespace-nowrap">
                    {{ __('Semua Fitur') }} ({{ count($features) }})
                </button>
                <button type="button"
                        @click="activeCategory = 'basics'"
                        :class="activeCategory === 'basics' ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200/70 dark:hover:bg-zinc-700 text-gray-600 dark:text-zinc-300'"
                        class="px-4 py-2 rounded-xl transition cursor-pointer whitespace-nowrap">
                    {{ __('Dasar & Rekening') }}
                </button>
                <button type="button"
                        @click="activeCategory = 'ai'"
                        :class="activeCategory === 'ai' ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200/70 dark:hover:bg-zinc-700 text-gray-600 dark:text-zinc-300'"
                        class="px-4 py-2 rounded-xl transition cursor-pointer whitespace-nowrap">
                    {{ __('Fitur Pintar AI') }}
                </button>
                <button type="button"
                        @click="activeCategory = 'debts'"
                        :class="activeCategory === 'debts' ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200/70 dark:hover:bg-zinc-700 text-gray-600 dark:text-zinc-300'"
                        class="px-4 py-2 rounded-xl transition cursor-pointer whitespace-nowrap">
                    {{ __('Hutang & Tagihan') }}
                </button>
                <button type="button"
                        @click="activeCategory = 'analytics'"
                        :class="activeCategory === 'analytics' ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200/70 dark:hover:bg-zinc-700 text-gray-600 dark:text-zinc-300'"
                        class="px-4 py-2 rounded-xl transition cursor-pointer whitespace-nowrap">
                    {{ __('Analisis & Prediksi') }}
                </button>
            </div>
        </div>

        {{-- Features Grid (Server Rendered for instant load & Alpine filtered) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach ($features as $feature)
                <div x-show="isVisible('{{ $feature['id'] }}', '{{ $feature['category'] }}')"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-98"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 shadow-sm p-5 sm:p-6 flex flex-col justify-between hover:border-emerald-300 dark:hover:border-emerald-700 transition-all duration-200 group">

                    <div>
                        {{-- Top Metadata --}}
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider
                                {{ $feature['category'] === 'ai' ? 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300' : ($feature['category'] === 'debts' ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300' : ($feature['category'] === 'analytics' ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300' : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300')) }}">
                                {{ $feature['badge'] }}
                            </span>
                            <span class="text-[11px] text-gray-400 dark:text-zinc-500 font-medium">{{ $feature['category_name'] }}</span>
                        </div>

                        {{-- Title & Summary --}}
                        <h2 class="text-base sm:text-lg font-extrabold text-gray-900 dark:text-zinc-100 group-hover:text-[#00c078] transition-colors leading-snug">
                            {{ $feature['title'] }}
                        </h2>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-zinc-400 mt-2 leading-relaxed">
                            {{ $feature['summary'] }}
                        </p>

                        {{-- Step-by-Step Accordion --}}
                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-zinc-800">
                            <button type="button"
                                    @click="toggleExpand('{{ $feature['id'] }}')"
                                    class="w-full flex items-center justify-between text-xs font-bold text-gray-700 dark:text-zinc-300 hover:text-[#00c078] py-1 cursor-pointer transition-colors">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-[#00c078] transition-transform duration-200"
                                         :class="isExpanded('{{ $feature['id'] }}') ? 'rotate-90' : ''"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    <span x-text="isExpanded('{{ $feature['id'] }}') ? '{{ __('Sembunyikan Langkah') }}' : '{{ __('Lihat Cara Menggunakan (Langkah demi Langkah)') }}'"></span>
                                </span>
                                <span class="text-[10px] font-mono font-normal text-gray-400 dark:text-zinc-500" x-text="isExpanded('{{ $feature['id'] }}') ? '▲' : '▼'"></span>
                            </button>

                            <div x-show="isExpanded('{{ $feature['id'] }}')"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="mt-3 space-y-2 bg-gray-50/80 dark:bg-zinc-800/60 rounded-xl p-3.5 text-xs text-gray-600 dark:text-zinc-300">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-zinc-500 mb-1.5">{{ __('Langkah Penggunaan:') }}</p>
                                <ol class="list-decimal list-inside space-y-1.5 leading-relaxed font-medium">
                                    @foreach ($feature['steps'] as $step)
                                        <li class="pl-1">{{ $step }}</li>
                                    @endforeach
                                </ol>
                            </div>
                        </div>

                        {{-- Pro-Tip Box --}}
                        @if (!empty($feature['tip']))
                            <div class="mt-3.5 flex items-start gap-2.5 p-3 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40 text-xs text-emerald-800 dark:text-emerald-300">
                                <svg class="w-4 h-4 text-[#00c078] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                                <div class="min-w-0">
                                    <span class="font-bold text-[#00995f] dark:text-[#00c078]">{{ __('Pro-Tip:') }}</span>
                                    <span class="ml-1 opacity-90">{{ $feature['tip'] }}</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Action Button --}}
                    <div class="mt-5 pt-3.5 border-t border-gray-100 dark:border-zinc-800 flex items-center justify-between">
                        <span class="text-[11px] text-gray-400 dark:text-zinc-500 font-medium">{{ __('Langsung coba fitur ini:') }}</span>
                        <a href="{{ $feature['route'] }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-gray-100 dark:bg-zinc-800 hover:bg-[#00c078] dark:hover:bg-[#00c078] text-gray-700 dark:text-zinc-200 hover:text-white dark:hover:text-white text-xs font-bold transition-all shadow-2xs group-hover:bg-[#00c078] group-hover:text-white">
                            <span>{{ $feature['action_label'] }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Empty State if search matches nothing --}}
        <div x-show="visibleCount === 0"
             style="display: none;"
             class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-10 text-center shadow-sm">
            <div class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-zinc-800 text-gray-400 dark:text-zinc-500 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <h3 class="text-sm font-bold text-gray-900 dark:text-zinc-100">{{ __('Tidak ada panduan yang cocok') }}</h3>
            <p class="text-xs text-gray-500 dark:text-zinc-400 max-w-sm mx-auto mt-1">
                {{ __('Coba kata kunci lain atau pilih tab Semua Fitur untuk menjelajahi seluruh panduan.') }}
            </p>
            <button type="button"
                    @click="searchQuery = ''; activeCategory = 'all'"
                    class="mt-4 px-4 py-2 rounded-xl bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 dark:hover:bg-zinc-700 text-xs font-bold text-gray-700 dark:text-zinc-200 transition cursor-pointer">
                {{ __('Reset Pencarian') }}
            </button>
        </div>

        {{-- Keyboard Shortcuts & Productivity Quick-Reference --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 p-5 sm:p-6 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 text-[#00c078] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-extrabold text-gray-900 dark:text-zinc-100 uppercase tracking-wider">{{ __('Shortcut Keyboard & Tips Efisiensi') }}</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                <div class="p-3 rounded-xl bg-gray-50 dark:bg-zinc-800/60 flex items-center justify-between">
                    <span class="text-gray-600 dark:text-zinc-400 font-medium">{{ __('Buka Pencarian Ledger') }}</span>
                    <kbd class="px-2 py-1 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded font-mono text-[11px] font-bold text-gray-700 dark:text-zinc-300 shadow-2xs">Ctrl / Cmd + K</kbd>
                </div>
                <div class="p-3 rounded-xl bg-gray-50 dark:bg-zinc-800/60 flex items-center justify-between">
                    <span class="text-gray-600 dark:text-zinc-400 font-medium">{{ __('Tutup Modal / Popup') }}</span>
                    <kbd class="px-2 py-1 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded font-mono text-[11px] font-bold text-gray-700 dark:text-zinc-300 shadow-2xs">Esc</kbd>
                </div>
                <div class="p-3 rounded-xl bg-gray-50 dark:bg-zinc-800/60 flex items-center justify-between">
                    <span class="text-gray-600 dark:text-zinc-400 font-medium">{{ __('Ganti Tema Dark / Light') }}</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ __('Tombol di Sidebar') }}</span>
                </div>
                <div class="p-3 rounded-xl bg-gray-50 dark:bg-zinc-800/60 flex items-center justify-between">
                    <span class="text-gray-600 dark:text-zinc-400 font-medium">{{ __('Konsultasi Finora AI') }}</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ __('Header Finora AI') }}</span>
                </div>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- INTERACTIVE 6-STEP ONBOARDING TOUR MODAL                      --}}
        {{-- ============================================================== --}}
        <div x-show="tourOpen"
             x-cloak
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true"
             @keydown.escape.window="tourOpen = false">
            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-black/60 dark:bg-black/80 backdrop-blur-sm transition-opacity"
                 @click="tourOpen = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-xl rounded-3xl bg-white dark:bg-zinc-900 p-6 sm:p-8 shadow-2xl border border-gray-100 dark:border-zinc-800 transition-all text-left z-10"
                     @click.stop
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">

                    {{-- Top Tour Controls --}}
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-zinc-800 mb-5">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 text-[#00c078] text-[10px] font-black uppercase tracking-wider">
                                {{ __('Langkah') }} <span x-text="tourStep + 1"></span> {{ __('dari') }} <span x-text="tourSteps ? tourSteps.length : 6"></span>
                            </span>
                            <span class="text-xs text-gray-400 dark:text-zinc-500 font-semibold">{{ __('Tur Interaktif Finora') }}</span>
                        </div>
                        <button type="button"
                                @click="tourOpen = false"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-zinc-200 p-1 rounded-lg cursor-pointer transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Progress Bar Indicator --}}
                    <div class="w-full bg-gray-100 dark:bg-zinc-800 h-1.5 rounded-full overflow-hidden mb-6">
                        <div class="bg-[#00c078] h-full transition-all duration-300 rounded-full"
                             :style="'width: ' + (((tourStep + 1) / ((tourSteps && tourSteps.length) ? tourSteps.length : 6)) * 100) + '%'"></div>
                    </div>

                    {{-- Step Content --}}
                    <div class="space-y-4 min-h-[170px]">
                        {{-- Dynamic Step SVG Icon --}}
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-[#00c078] flex items-center justify-center mb-3">
                            <template x-if="tourSteps[tourStep] && tourSteps[tourStep].icon === 'wallet'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                            </template>
                            <template x-if="tourSteps[tourStep] && tourSteps[tourStep].icon === 'lightning'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </template>
                            <template x-if="tourSteps[tourStep] && tourSteps[tourStep].icon === 'chart'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </template>
                            <template x-if="tourSteps[tourStep] && tourSteps[tourStep].icon === 'calendar'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </template>
                            <template x-if="tourSteps[tourStep] && tourSteps[tourStep].icon === 'users'">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </template>
                            <template x-if="tourSteps[tourStep] && (!['wallet','lightning','chart','calendar','users'].includes(tourSteps[tourStep].icon))">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </template>
                        </div>

                        <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 dark:text-zinc-100 leading-snug"
                            x-text="tourSteps[tourStep] ? tourSteps[tourStep].title : ''"></h3>

                        <p class="text-xs sm:text-sm text-gray-600 dark:text-zinc-400 leading-relaxed"
                           x-text="tourSteps[tourStep] ? tourSteps[tourStep].desc : ''"></p>
                    </div>

                    {{-- Footer Navigation Controls --}}
                    <div class="mt-8 pt-4 border-t border-gray-100 dark:border-zinc-800 flex items-center justify-between gap-3">
                        <button type="button"
                                @click="prevTourStep()"
                                class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-zinc-700 text-xs font-semibold text-gray-600 dark:text-zinc-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition cursor-pointer">
                            <span x-text="tourStep > 0 ? '{{ __('Kembali') }}' : '{{ __('Tutup') }}'"></span>
                        </button>

                        <div class="flex items-center gap-2">
                            <a :href="tourSteps[tourStep] ? tourSteps[tourStep].route : '#'"
                               class="px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-zinc-800 hover:bg-gray-200 dark:hover:bg-zinc-700 text-gray-800 dark:text-zinc-200 text-xs font-bold transition">
                                <span x-text="tourSteps[tourStep] ? tourSteps[tourStep].btnText : ''"></span>
                            </a>

                            <button type="button"
                                    @click="nextTourStep()"
                                    class="px-5 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-bold shadow-sm transition cursor-pointer">
                                <span x-text="tourStep < ((tourSteps && tourSteps.length) ? tourSteps.length - 1 : 5) ? '{{ __('Lanjut →') }}' : '{{ __('Selesai & Mulai!') }}'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
