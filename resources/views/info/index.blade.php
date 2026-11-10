<x-app-layout>
    <x-slot name="title">{{ __('Info & Versi') }}</x-slot>

    <div x-data="{
        searchQuery: '',
        activeCategory: 'all',
        selectedVersion: 'all',
        showWhatsNewModal: false,
        copiedSpecs: false,

        copySystemSpecs() {
            const specs = `Finora System Info:
Version: {{ $appMeta['version'] }} ({{ $appMeta['codename'] }})
Release Date: {{ $appMeta['release_date'] }}
Framework: {{ $systemSpecs['Framework'] }}
PHP: {{ $systemSpecs['PHP Runtime'] }}
Database: {{ $systemSpecs['Database Driver'] }}
Timezone: {{ $systemSpecs['Timezone'] }}
Environment: {{ $systemSpecs['App Environment'] }}`;
            navigator.clipboard.writeText(specs).then(() => {
                this.copiedSpecs = true;
                setTimeout(() => this.copiedSpecs = false, 3000);
            });
        },

        matchItem(itemCategory, itemType, itemText) {
            const matchesCat = this.activeCategory === 'all' || this.activeCategory === itemType;
            if (!matchesCat) return false;
            if (!this.searchQuery.trim()) return true;
            const q = this.searchQuery.toLowerCase();
            return itemText.toLowerCase().includes(q);
        }
    }" class="space-y-10 max-w-6xl mx-auto pb-16">

        <!-- ============================================================== -->
        <!-- HEADER & ACTIONS                                              -->
        <!-- ============================================================== -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-5 pb-1">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800">
                        {{ __('Release Hub') }}
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-semibold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#00c078] animate-pulse"></span>
                        <span>{{ $appMeta['status'] }}</span>
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-gray-100 tracking-tight">
                    {{ __('Info & Versi Aplikasi') }}
                </h1>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1.5 max-w-2xl leading-relaxed">
                    {{ __('Pantau informasi rilis, riwayat pembaruan (changelog), spesifikasi sistem, serta peta jalan fitur mendatang di website Finora.') }}
                </p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex items-center gap-3 shrink-0 flex-wrap">
                <!-- Quick Language Selector -->
                <div class="flex items-center bg-white dark:bg-gray-800 p-1 rounded-xl text-xs font-bold border border-gray-200 dark:border-gray-700 shadow-xs gap-1">
                    <a href="{{ route('locale.switch', 'id') }}"
                       class="px-2.5 py-1.5 rounded-lg transition-all text-xs {{ app()->getLocale() === 'id' ? 'bg-[#00c078] text-white shadow-xs font-black' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}"
                       title="{{ __('Ganti ke Bahasa Indonesia') }}">
                        ID
                    </a>
                    <a href="{{ route('locale.switch', 'en') }}"
                       class="px-2.5 py-1.5 rounded-lg transition-all text-xs {{ app()->getLocale() === 'en' ? 'bg-[#00c078] text-white shadow-xs font-black' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}"
                       title="{{ __('Switch to English') }}">
                        EN
                    </a>
                </div>

                <button type="button"
                        @click="showWhatsNewModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00a868] text-white text-xs font-bold transition-all shadow-sm shadow-[#00c078]/25 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                    <span>{{ __('Apa yang Baru di v2.5.0') }}</span>
                </button>

                <a href="{{ route('tutorials.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 text-xs font-bold transition-all shadow-xs">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span>{{ __('Buku Panduan') }}</span>
                </a>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- HERO OVERVIEW CARD                                             -->
        <!-- ============================================================== -->
        <div class="relative overflow-hidden rounded-3xl bg-[#0b1320] text-white p-6 sm:p-8 lg:p-10 shadow-xl border border-gray-800">
            <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">
                <!-- Left: App Identity -->
                <div class="lg:col-span-7 space-y-5">
                    <div class="flex items-center gap-4">
                        <!-- Finora Logo with Distinct Glowing Emerald Border Frame -->
                        <div class="relative w-14 h-14 rounded-2xl p-1 bg-white/10 border-2 border-emerald-400/90 shadow-md ring-4 ring-emerald-500/30 flex items-center justify-center shrink-0">
                            <img src="{{ asset('images/logo.jpg') }}" alt="Finora" width="46" height="46" class="w-full h-full rounded-xl object-cover border border-white/25">
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5">
                                <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-white">Finora</h2>
                                <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-[#00c078] text-white shadow-xs">
                                    {{ $appMeta['version'] }}
                                </span>
                            </div>
                            <p class="text-xs text-emerald-400 font-semibold mt-1">
                                {{ $appMeta['codename'] }} &bull; {{ $appMeta['release_date'] }}
                            </p>
                        </div>
                    </div>

                    <p class="text-xs sm:text-sm text-gray-300 leading-relaxed max-w-xl">
                        {{ $appMeta['description'] }}
                    </p>

                    <!-- Status Tags -->
                    <div class="flex flex-wrap items-center gap-2.5 pt-1 text-[11px]">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white/10 text-gray-200 border border-white/10 font-semibold shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-[#00c078]"></span>
                            <span>{{ __('Status:') }} {{ $appMeta['status'] }}</span>
                        </span>
                        <span class="px-3 py-1.5 rounded-xl bg-white/10 text-gray-200 border border-white/10 font-semibold shadow-2xs">
                            {{ $systemSpecs['Framework'] }}
                        </span>
                        <span class="px-3 py-1.5 rounded-xl bg-white/10 text-gray-200 border border-white/10 font-semibold shadow-2xs">
                            {{ $systemSpecs['PHP Runtime'] }}
                        </span>
                        <span class="px-3 py-1.5 rounded-xl bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-semibold shadow-2xs">
                            {{ $appMeta['uptime'] }} {{ __('Uptime SLA') }}
                        </span>
                    </div>
                </div>

                <!-- Right: Stats Grid -->
                <div class="lg:col-span-5 grid grid-cols-2 gap-4">
                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-800/80 border border-slate-700/80 flex flex-col justify-between shadow-xs">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Versi Aktif') }}</p>
                        <p class="text-2xl sm:text-3xl font-black text-white mt-1.5">{{ $appMeta['version'] }}</p>
                        <p class="text-[10px] text-emerald-400 font-semibold mt-1">{{ __('Stabil & Siap Pakai') }}</p>
                    </div>

                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-800/80 border border-slate-700/80 flex flex-col justify-between shadow-xs">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Total Pembaruan') }}</p>
                        <p class="text-2xl sm:text-3xl font-black text-white mt-1.5">{{ count($releases) }} {{ __('Rilis') }}</p>
                        <p class="text-[10px] text-gray-400 font-semibold mt-1">{{ __('Sejak Peluncuran v1.0') }}</p>
                    </div>

                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-800/80 border border-slate-700/80 flex flex-col justify-between shadow-xs">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Modul Fitur Inti') }}</p>
                        <p class="text-2xl sm:text-3xl font-black text-white mt-1.5">{{ $stats['features_count'] }}+</p>
                        <p class="text-[10px] text-gray-400 font-semibold mt-1 truncate" title="{{ __('AI, Ledger, Audit, Kalkulator') }}">{{ __('AI, Ledger, Audit, Kalkulator') }}</p>
                    </div>

                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-800/80 border border-slate-700/80 flex flex-col justify-between shadow-xs">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ __('Multi-Mata Uang') }}</p>
                        <p class="text-2xl sm:text-3xl font-black text-white mt-1.5">{{ $stats['currencies_supported'] }} FX</p>
                        <p class="text-[10px] text-gray-400 font-semibold mt-1 truncate" title="{{ __('IDR, USD, EUR, SGD, dll') }}">{{ __('IDR, USD, EUR, SGD, dll') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- FILTER & INSTANT SEARCH TOOLBAR                                -->
        <!-- ============================================================== -->
        <div class="p-5 sm:p-6 bg-white dark:bg-[#181c24] border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm space-y-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Search Input -->
                <div class="relative flex-1 max-w-lg">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           x-model="searchQuery"
                           placeholder="{{ __('Cari fitur, pembaruan, atau perbaikan bug...') }}"
                           class="w-full pl-10 pr-8 py-2.5 bg-gray-50 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 rounded-xl text-xs text-gray-800 dark:text-gray-100 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#00c078] focus:border-transparent transition-all">
                    <button type="button"
                            x-show="searchQuery.length > 0"
                            @click="searchQuery = ''"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xs p-0.5">
                        &times;
                    </button>
                </div>

                <!-- Version Selector Dropdown -->
                <div class="flex items-center gap-2.5 text-xs">
                    <span class="text-gray-500 dark:text-gray-400 font-bold shrink-0">{{ __('Filter Versi:') }}</span>
                    <select x-model="selectedVersion"
                            class="px-3.5 py-2.5 bg-gray-50 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#00c078] cursor-pointer">
                        <option value="all">{{ __('Semua Versi') }} (v1.0 - v2.5)</option>
                        @foreach ($releases as $rel)
                            <option value="{{ $rel['version'] }}">{{ $rel['version'] }} &bull; {{ $rel['codename'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Category Pills -->
            <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-gray-100 dark:border-gray-800">
                <button type="button"
                        @click="activeCategory = 'all'"
                        :class="activeCategory === 'all' ? 'bg-gray-900 text-white shadow-xs' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer">
                    {{ __('Semua Update') }}
                </button>

                <button type="button"
                        @click="activeCategory = 'feature'"
                        :class="activeCategory === 'feature' ? 'bg-[#00c078] text-white shadow-xs' : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60'"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span>{{ __('Fitur Baru') }}</span>
                </button>

                <button type="button"
                        @click="activeCategory = 'ai'"
                        :class="activeCategory === 'ai' ? 'bg-purple-600 text-white shadow-xs' : 'bg-purple-50 dark:bg-purple-950/40 text-purple-800 dark:text-purple-300 hover:bg-purple-100 dark:hover:bg-purple-900/60'"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>{{ __('AI & Otomasi') }}</span>
                </button>

                <button type="button"
                        @click="activeCategory = 'improvement'"
                        :class="activeCategory === 'improvement' ? 'bg-blue-600 text-white shadow-xs' : 'bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-900/60'"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <span>{{ __('Peningkatan') }}</span>
                </button>

                <button type="button"
                        @click="activeCategory = 'fix'"
                        :class="activeCategory === 'fix' ? 'bg-amber-600 text-white shadow-xs' : 'bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/60'"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    </svg>
                    <span>{{ __('Perbaikan Bug') }}</span>
                </button>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- TIMELINE OF RELEASES & CHANGELOG                               -->
        <!-- ============================================================== -->
        <div class="w-full">
            @foreach ($releases as $release)
                <div x-show="(selectedVersion === 'all' || selectedVersion === '{{ $release['version'] }}')"
                     x-transition
                     class="release-card mb-10 sm:mb-14 last:mb-0 bg-white dark:bg-[#181c24] border {{ $release['is_latest'] ? 'border-emerald-400/80 dark:border-emerald-500/40 ring-4 ring-emerald-500/10' : 'border-gray-200 dark:border-gray-800' }} rounded-3xl shadow-sm overflow-hidden">

                    <!-- Release Header Card -->
                    <div class="p-6 sm:p-8 {{ $release['is_latest'] ? 'bg-emerald-50/70 dark:bg-emerald-950/30' : 'bg-gray-50/60 dark:bg-gray-800/40' }} border-b border-gray-200 dark:border-gray-800">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-center gap-3.5">
                                <span class="px-3 py-1.5 rounded-xl text-xs font-black {{ $release['is_latest'] ? 'bg-[#00c078] text-white shadow-xs' : 'bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-gray-200' }}">
                                    {{ $release['version'] }}
                                </span>
                                <div>
                                    <h3 class="text-base sm:text-xl font-extrabold text-gray-900 dark:text-gray-100 tracking-tight">
                                        {{ $release['name'] }}
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">
                                        <span class="font-bold text-gray-700 dark:text-gray-300">{{ $release['codename'] }}</span> &bull; {{ $release['date'] }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                @if ($release['is_latest'])
                                    <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300 text-xs font-extrabold shadow-2xs border border-emerald-300 dark:border-emerald-700/60">
                                        <span class="w-2 h-2 rounded-full bg-[#00c078] animate-pulse"></span>
                                        {{ __('Rilis Terbaru') }}
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-xs font-bold border border-gray-200 dark:border-gray-700">
                                        {{ $release['badge'] }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Summary -->
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 leading-relaxed mt-4 max-w-4xl">
                            {{ $release['summary'] }}
                        </p>

                        <!-- Highlights List -->
                        @if (!empty($release['highlights']))
                            <div class="mt-5 pt-4.5 border-t border-gray-200/80 dark:border-gray-800/80 grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                                @foreach ($release['highlights'] as $highlight)
                                    <div class="flex items-start gap-2.5 text-gray-700 dark:text-gray-300">
                                        <svg class="w-4 h-4 text-[#00c078] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="leading-relaxed">{{ $highlight }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Changes Items List with Spacious Dedicated Cards -->
                    <div class="p-5 sm:p-7 space-y-3.5">
                        @foreach ($release['changes'] as $change)
                            <div x-show="matchItem('{{ $change['category'] }}', '{{ $change['type'] }}', '{{ addslashes($change['title'] . ' ' . $change['description'] . ' ' . $change['tag']) }}')"
                                 x-transition
                                 class="p-4 sm:p-5 rounded-2xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-200/80 dark:border-gray-800 hover:border-emerald-300 dark:hover:border-emerald-600/60 hover:bg-white dark:hover:bg-gray-800/80 transition-all shadow-2xs flex flex-col sm:flex-row sm:items-start justify-between gap-4">

                                <div class="flex items-start gap-3.5 flex-1 min-w-0">
                                    <!-- Type Icon Badge -->
                                    <div class="shrink-0 mt-0.5">
                                        @if ($change['type'] === 'feature')
                                            <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-[#00c078] flex items-center justify-center font-bold border border-emerald-200/60 dark:border-emerald-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                            </div>
                                        @elseif ($change['type'] === 'ai')
                                            <div class="w-9 h-9 rounded-xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-300 flex items-center justify-center font-bold border border-purple-200/60 dark:border-purple-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                </svg>
                                            </div>
                                        @elseif ($change['type'] === 'improvement')
                                            <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-300 flex items-center justify-center font-bold border border-blue-200/60 dark:border-blue-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-300 flex items-center justify-center font-bold border border-amber-200/60 dark:border-amber-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="text-xs sm:text-sm font-extrabold text-gray-900 dark:text-gray-100">{{ $change['title'] }}</p>
                                            <span class="text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md
                                                {{ $change['type'] === 'feature' ? 'bg-emerald-100/80 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300' :
                                                   ($change['type'] === 'ai' ? 'bg-purple-100/80 dark:bg-purple-950/40 text-purple-800 dark:text-purple-300' :
                                                   ($change['type'] === 'improvement' ? 'bg-blue-100/80 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300' : 'bg-amber-100/80 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300')) }}">
                                                {{ $change['category'] }}
                                            </span>
                                            <span class="text-[9px] font-semibold text-gray-500 dark:text-gray-400 bg-gray-200/70 dark:bg-gray-700/60 px-2 py-0.5 rounded-md">
                                                #{{ $change['tag'] }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed mt-1.5">
                                            {{ $change['description'] }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Action Button if Available -->
                                @if (!empty($change['action_route']) && !empty($change['action_label']))
                                    <div class="shrink-0 self-start sm:self-center">
                                        <a href="{{ $change['action_route'] }}"
                                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-gray-800 hover:bg-[#00c078] text-gray-700 dark:text-gray-200 hover:text-white dark:hover:text-white border border-gray-200 dark:border-gray-700 text-xs font-bold transition-all shadow-2xs group">
                                            <span>{{ $change['action_label'] }}</span>
                                            <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <!-- ============================================================== -->
        <!-- UPCOMING ROADMAP (WHAT'S COMING NEXT)                          -->
        <!-- ============================================================== -->
        <div class="p-6 sm:p-8 bg-white dark:bg-[#181c24] border border-gray-200 dark:border-gray-800 rounded-3xl shadow-sm space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg sm:text-xl font-extrabold text-gray-900 dark:text-gray-100 tracking-tight flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        <span>{{ __('Peta Jalan Fitur Mendatang (Roadmap)') }}</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('Fitur dan inovasi yang sedang dipersiapkan tim Finora untuk pembaruan berikutnya.') }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($roadmap as $item)
                    <div class="p-5 sm:p-6 rounded-2xl bg-gray-50/70 dark:bg-gray-800/40 border border-gray-200 dark:border-gray-800 hover:border-emerald-300 dark:hover:border-emerald-600/60 transition-all flex flex-col justify-between shadow-2xs">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="px-2.5 py-1 rounded-md text-[9px] font-black uppercase tracking-wider
                                    {{ $item['status_color'] === 'emerald' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300' :
                                       ($item['status_color'] === 'purple' ? 'bg-purple-100 text-purple-800 dark:bg-purple-950/40 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300') }}">
                                    {{ $item['status_label'] }}
                                </span>
                                <span class="text-[10px] font-bold text-gray-400">
                                    {{ $item['eta'] }}
                                </span>
                            </div>
                            <h4 class="text-sm font-extrabold text-gray-900 dark:text-gray-100 mb-1.5">{{ $item['title'] }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">{{ $item['description'] }}</p>
                        </div>

                        <div class="mt-5 pt-3.5 border-t border-gray-200/60 dark:border-gray-700/60 flex items-center justify-between text-[11px] text-gray-400">
                            <span>Finora Labs</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ __('Terjadwal') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- SYSTEM DIAGNOSTICS & TECH STACK TRANSPARENCY                   -->
        <!-- ============================================================== -->
        <div class="p-6 sm:p-8 bg-white dark:bg-[#181c24] border border-gray-200 dark:border-gray-800 rounded-3xl shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg sm:text-xl font-extrabold text-gray-900 dark:text-gray-100 tracking-tight flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        </svg>
                        <span>{{ __('Diagnostik Sistem & Spesifikasi Teknis') }}</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ __('Informasi runtime lingkungan server, engine database, dan protokol keamanan aktif.') }}
                    </p>
                </div>

                <button type="button"
                        @click="copySystemSpecs()"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 text-xs font-bold transition cursor-pointer self-start sm:self-auto border border-gray-200 dark:border-gray-700">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                    </svg>
                    <span x-text="copiedSpecs ? '{{ __('Tersalin ke Clipboard!') }}' : '{{ __('Salin Info Sistem') }}'"></span>
                </button>
            </div>

            <!-- Specs Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                @foreach ($systemSpecs as $label => $val)
                    <div class="p-4 rounded-2xl bg-gray-50/80 dark:bg-gray-800/40 border border-gray-200/70 dark:border-gray-800">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $label }}</p>
                        <p class="text-xs font-bold text-gray-900 dark:text-gray-100 mt-1 truncate" title="{{ $val }}">{{ $val }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- WHAT'S NEW MODAL DIALOG (ALPINE.JS)                            -->
        <!-- ============================================================== -->
        <div x-show="showWhatsNewModal"
             x-transition.opacity
             style="display: none;"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">

            <div @click.outside="showWhatsNewModal = false"
                 class="relative bg-white dark:bg-[#181c24] rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-gray-200 dark:border-gray-800 overflow-hidden transform transition-all">

                <!-- Header Decor -->
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-500 text-white flex items-center justify-center font-black shadow-md shadow-emerald-500/25">
                            v2.5
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-gray-100">{{ __('Sorotan Rilis v2.5.0') }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Aurora Zenith &bull; Pembaruan Pintar Finora') }}</p>
                        </div>
                    </div>

                    <button type="button"
                            @click="showWhatsNewModal = false"
                            class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Highlights List -->
                <div class="py-5 space-y-4">
                    <div class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/40">
                        <div class="w-9 h-9 rounded-xl bg-[#00c078] text-white flex items-center justify-center shrink-0 mt-0.5 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-extrabold text-gray-900 dark:text-gray-100">{{ __('Pusat Info & Versi Aplikasi') }}</h4>
                            <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5 leading-relaxed">
                                {{ __('Cek versi aplikasi aktif, log perubahan sistem, roadmap masa depan, dan status server kapan pun Anda butuhkan.') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-teal-50/70 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-900/40">
                        <div class="w-9 h-9 rounded-xl bg-teal-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-extrabold text-gray-900 dark:text-gray-100">{{ __('Smart Financial Calculator') }}</h4>
                            <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5 leading-relaxed">
                                {{ __('Kalkulator finansial serbaguna dengan konversi jam kerja, simulasi cicilan pinjaman, dan efek bunga majemuk kopi.') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-900/40">
                        <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-extrabold text-gray-900 dark:text-gray-100">{{ __('Pusat Panduan & Tur Onboarding') }}</h4>
                            <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5 leading-relaxed">
                                {{ __('Panduan langkah demi langkah serta tur onboarding 6 langkah untuk membantu Anda memaksimalkan setiap modul finansial.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer Modal -->
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-end gap-2.5">
                    <button type="button"
                            @click="showWhatsNewModal = false"
                            class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-bold transition cursor-pointer">
                        {{ __('Tutup') }}
                    </button>
                    <a href="{{ route('calculator.index') }}"
                       class="px-4 py-2 rounded-xl bg-[#00c078] hover:bg-[#00a868] text-white text-xs font-bold transition shadow-xs cursor-pointer">
                        {{ __('Jelajahi Fitur') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
