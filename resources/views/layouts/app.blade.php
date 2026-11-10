<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>{{ config('app.name', 'Finora') }} – {{ $title ?? 'Dashboard' }}</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('images/logo-192.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js" async></script>
    <script>
        if (localStorage.getItem('finora_theme') === 'dark' || (!('finora_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="font-sans antialiased bg-[#f1f5f9] text-gray-900"
      x-data="{
          sidebarOpen: false,
          darkMode: document.documentElement.classList.contains('dark'),
          toggleTheme() {
              this.darkMode = !this.darkMode;
              if (this.darkMode) {
                  document.documentElement.classList.add('dark');
                  localStorage.setItem('finora_theme', 'dark');
              } else {
                  document.documentElement.classList.remove('dark');
                  localStorage.setItem('finora_theme', 'light');
              }
              window.dispatchEvent(new CustomEvent('finora-theme-changed', { detail: { dark: this.darkMode } }));
          }
      }">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-60 h-screen max-h-screen bg-white border-r border-gray-100 flex flex-col justify-between transform transition-transform duration-200 ease-in-out lg:static lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <div class="flex-1 overflow-y-auto min-h-0 flex flex-col scrollbar-thin">
                <!-- Brand Logo -->
                <div class="px-6 py-6 flex items-center justify-between shrink-0">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.jpg') }}" alt="Finora" width="32" height="32" style="width: 32px; height: 32px; object-fit: cover;" class="w-8 h-8 rounded-lg object-cover shadow-xs">
                        <span class="text-2xl font-black tracking-tight text-gray-900">Finora</span>
                    </a>
                    <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Navigation List -->
                <nav class="px-3 space-y-1 pb-4 flex-1">
                    <p class="px-3 mb-3 text-[11px] font-bold text-gray-400 tracking-wider uppercase">Main Navigation</p>

                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
                              {{ request()->routeIs('dashboard') ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        {{ __('Dashboard') }}
                    </a>

                    <!-- Transactions -->
                    <a href="{{ route('transactions.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
                              {{ request()->routeIs('transactions.*') ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('transactions.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        {{ __('Transactions') }}
                    </a>

                    <!-- Wallets & Accounts -->
                    <a href="{{ route('wallets.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
                              {{ request()->routeIs('wallets.*') ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('wallets.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        {{ __('Wallets & Accounts') }}
                    </a>

                    <!-- Budgets -->
                    <a href="{{ route('budgets.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
                              {{ request()->routeIs('budgets.*') ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('budgets.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        {{ __('Budgets') }}
                    </a>

                    <!-- Recurring Bills & Subscriptions -->
                    <a href="{{ route('recurring-bills.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
                              {{ request()->routeIs('recurring-bills.*') ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('recurring-bills.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        {{ __('Recurring Bills') }}
                    </a>

                    <!-- Debts & Loans -->
                    <a href="{{ route('debts.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
                              {{ request()->routeIs('debts.*') ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('debts.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        {{ __('Debts & Loans') }}
                    </a>

                    <!-- Calendar -->
                    <a href="{{ route('calendar.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
                              {{ request()->routeIs('calendar.*') ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('calendar.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ __('Calendar') }}
                    </a>

                    <!-- Reports -->
                    <a href="{{ route('reports.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
                              {{ request()->routeIs('reports.*') ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('reports.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                        </svg>
                        {{ __('Reports') }}
                    </a>

                    <!-- Financial Health Score & Audit Radar -->
                    <a href="{{ route('financial-health.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
                              {{ request()->routeIs('financial-health.*') ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('financial-health.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        {{ __('Financial Health') }}
                    </a>

                    <!-- Cashflow Forecast -->
                    <a href="{{ route('cashflow.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
                              {{ request()->routeIs('cashflow.*') ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('cashflow.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17l6-6 4 4 8-8m0 0h-5m5 0v5"/>
                        </svg>
                        {{ __('Cashflow Forecast') }}
                    </a>

                    <!-- Smart Financial Calculator -->
                    <a href="{{ route('calculator.index') }}"
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
                              {{ request()->routeIs('calculator.*') ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('calculator.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <span id="sidebar-calculator-label">{{ __('Smart Calculator') }}</span>
                        </div>
                        <span class="text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider {{ request()->routeIs('calculator.*') ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-700' }}">
                            {{ __('Smart') }}
                        </span>
                    </a>

                    <!-- Monthly Financial Wrapped -->
                    <a href="{{ route('wrapped.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
                              {{ request()->routeIs('wrapped.*') ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('wrapped.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        {{ __('Financial Wrapped') }}
                    </a>

                    <!-- Savings Goals -->
                    <a href="{{ route('savings.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
                              {{ request()->routeIs('savings.*') ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('savings.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('Savings Goals') }}
                    </a>

                    <!-- Categories -->
                    <a href="{{ route('categories.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
                              {{ request()->routeIs('categories.*') ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('categories.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        {{ __('Categories') }}
                    </a>

                    <!-- Feature Guide & Tutorials -->
                    <a href="{{ route('tutorials.index') }}"
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 cursor-pointer
                              {{ request()->routeIs('tutorials.*') ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('tutorials.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <span>{{ __('Panduan & Tutorial') }}</span>
                        </div>
                        <span class="text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider {{ request()->routeIs('tutorials.*') ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-700' }}">
                            {{ __('Info') }}
                        </span>
                    </a>

                    <!-- Info & Versi -->
                    <a href="{{ route('info.index') }}"
                       class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150 cursor-pointer
                              {{ request()->routeIs('info.*') ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 {{ request()->routeIs('info.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ __('Info & Versi') }}</span>
                        </div>
                        <span class="text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-wider {{ request()->routeIs('info.*') ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-700' }}">
                            v{{ config('app.version', '2.5.0') }}
                        </span>
                    </a>

                    <!-- Settings -->
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all duration-150
                              {{ request()->routeIs('profile.*') ? 'bg-[#00c078] text-white shadow-sm shadow-[#00c078]/25' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('profile.*') ? 'text-white' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ __('Settings') }}
                    </a>
                </nav>
            </div>

            <!-- Sidebar Footer Status & Theme Toggle -->
            <div class="px-4 py-3.5 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500 font-medium">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#00c078] animate-pulse"></span>
                    <a href="{{ route('info.index') }}" class="text-[11px] font-semibold text-gray-600 hover:text-[#00c078] transition-colors" title="{{ __('Info & Versi Aplikasi') }}">
                        Live Sync &bull; v{{ config('app.version', '2.5.0') }}
                    </a>
                </div>
                <div class="flex items-center gap-1.5">
                    <!-- Language Switcher Pill -->
                    <div class="flex items-center bg-gray-100 p-0.5 rounded-lg text-[10px] font-bold">
                        <a href="{{ route('locale.switch', 'id') }}" class="px-1.5 py-0.5 rounded {{ app()->getLocale() === 'id' ? 'bg-white text-emerald-700 font-extrabold shadow-2xs' : 'text-gray-500 hover:text-gray-800' }}">ID</a>
                        <a href="{{ route('locale.switch', 'en') }}" class="px-1.5 py-0.5 rounded {{ app()->getLocale() === 'en' ? 'bg-white text-emerald-700 font-extrabold shadow-2xs' : 'text-gray-500 hover:text-gray-800' }}">EN</a>
                    </div>

                    <!-- Dark / Light Mode Toggle Pill -->
                    <button type="button"
                            @click="toggleTheme()"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-100 hover:bg-gray-200/80 text-[10px] font-bold text-gray-700 transition cursor-pointer"
                            :title="darkMode ? '{{ __('Switch to Light Mode') }}' : '{{ __('Switch to Dark Mode') }}'">
                        <span x-show="!darkMode" class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>
                            <span>{{ __('Dark') }}</span>
                        </span>
                        <span x-show="darkMode" x-cloak class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <span>{{ __('Light') }}</span>
                        </span>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Overlay mobile -->
        <div
            class="fixed inset-0 z-40 bg-black/40 lg:hidden"
            x-show="sidebarOpen"
            @click="sidebarOpen = false"
            x-transition.opacity
        ></div>

        <!-- Main Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden w-full max-w-full">
            <!-- Top Bar Header (Google Stitch Style) -->
            <header class="relative z-30 bg-white border-b border-gray-100 px-3 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-2 sm:gap-4">
                <div class="flex items-center gap-2 sm:gap-3 flex-1 min-w-0">
                    <button @click="sidebarOpen = true" class="lg:hidden p-1.5 sm:p-2 text-gray-500 hover:text-gray-700 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>

                    <!-- Date Dropdown button with interactive popup -->
                    <div class="relative hidden sm:block shrink-0" x-data="{ dateDropdownOpen: false }">
                        <button @click="dateDropdownOpen = !dateDropdownOpen" @click.outside="dateDropdownOpen = false" type="button"
                                class="h-9 flex items-center gap-2 px-3 rounded-xl border border-gray-200 bg-white text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors cursor-pointer shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>{{ $periodLabel ?? ('This Month (' . now()->format('F') . ')') }}</span>
                            <svg class="w-3 h-3 text-gray-400 ml-1 transition-transform" :class="dateDropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <!-- Popup Dropdown -->
                        <div x-show="dateDropdownOpen" x-transition.opacity.duration.150ms
                             class="absolute left-0 mt-2 w-56 bg-white border border-gray-100 rounded-xl shadow-lg py-2 z-50 text-xs">
                            <p class="px-3 py-1 text-[10px] font-bold text-gray-400 uppercase tracking-wider">Select Statement Period</p>
                            @foreach (range(0, 5) as $i)
                                @php $m = now()->subMonths($i); @endphp
                                <a href="{{ route('dashboard', ['month' => $m->month, 'year' => $m->year]) }}"
                                   class="block px-3 py-2 text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-medium transition-colors">
                                    {{ $m->format('F Y') }}
                                </a>
                            @endforeach
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="{{ route('dashboard', ['period' => 'last_30_days']) }}" class="block px-3 py-2 text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-medium transition-colors">
                                Last 30 Days
                            </a>
                            <a href="{{ route('dashboard', ['period' => 'quarter']) }}" class="block px-3 py-2 text-gray-700 hover:bg-emerald-50 hover:text-emerald-700 font-medium transition-colors">
                                Q{{ ceil(now()->month / 3) }} {{ now()->year }}
                            </a>
                        </div>
                    </div>

                    <!-- Search Box (Responsive flex container) -->
                    <form method="GET" action="{{ route('transactions.index') }}"
                          class="h-9 w-full max-w-[170px] sm:max-w-xs md:max-w-sm lg:max-w-md flex items-center bg-gray-100/80 hover:bg-gray-100/90 focus-within:bg-white focus-within:ring-2 focus-within:ring-[#00c078] rounded-xl px-2.5 sm:px-3 transition-all shadow-2xs">
                        <svg class="w-4 h-4 text-gray-400 shrink-0 mr-1.5 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            type="text"
                            id="globalSearchInput"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search ledger..."
                            class="w-full bg-transparent border-0 p-0 text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0 leading-none min-w-0"
                        >
                        <kbd class="shrink-0 ml-1.5 px-1.5 py-0.5 bg-white border border-gray-200 text-[10px] text-gray-400 rounded font-mono hidden sm:inline-flex items-center gap-0.5 shadow-2xs">
                            <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 3a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3 3 3 0 0 0 3-3 3 3 0 0 0-3-3H6a3 3 0 0 0-3 3 3 3 0 0 0 3 3 3 3 0 0 0 3-3V6a3 3 0 0 0-3-3 3 3 0 0 0-3 3 3 3 0 0 0 3 3h12a3 3 0 0 0 3-3 3 3 0 0 0-3-3z"/></svg>
                            <span>K</span>
                        </kbd>
                    </form>
                </div>

                <!-- Right Profile & Actions -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <!-- Finora AI Advisor Quick Trigger -->
                    <button type="button"
                            @click="$dispatch('toggle-finora-ai')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100/80 text-[#00c078] border border-emerald-200/60 text-xs font-bold transition-all cursor-pointer shadow-2xs"
                            title="Open Finora AI Advisor">
                        <svg class="w-3.5 h-3.5 text-[#00c078] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <span class="hidden sm:inline">Finora AI</span>
                    </button>

                    <!-- Help & Feature Tutorials Trigger -->
                    <a href="{{ route('tutorials.index') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-100/90 hover:bg-gray-200/80 text-gray-700 text-xs font-bold transition-all shadow-2xs border border-gray-200/60"
                       title="{{ __('Panduan & Tutorial Fitur') }}">
                        <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span class="hidden sm:inline">{{ __('Panduan') }}</span>
                    </a>

                    <!-- App Info & Version Trigger -->
                    <a href="{{ route('info.index') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-100/90 hover:bg-gray-200/80 text-gray-700 text-xs font-bold transition-all shadow-2xs border border-gray-200/60"
                       title="{{ __('Info & Versi Aplikasi') }}">
                        <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="hidden sm:inline">v{{ config('app.version', '2.5.0') }}</span>
                    </a>

                    <!-- Interactive Notification Center -->
                    @php
                        $notifData = \App\Http\Controllers\NotificationController::getForUser(auth()->user());
                    @endphp
                    <div class="relative"
                         x-data="{
                             notifOpen: false,
                             unreadCount: {{ $notifData['unread_count'] }},
                             async markAllRead() {
                                 if (this.unreadCount === 0) return;
                                 this.unreadCount = 0;
                                 try {
                                     await fetch('{{ route('notifications.read') }}', {
                                         method: 'POST',
                                         headers: {
                                             'Content-Type': 'application/json',
                                             'Accept': 'application/json',
                                             'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                                         }
                                     });
                                 } catch (e) {}
                             }
                         }">
                        <button type="button"
                                @click="notifOpen = !notifOpen"
                                @click.outside="notifOpen = false"
                                class="relative p-2 rounded-xl text-gray-500 hover:text-gray-800 hover:bg-gray-100/80 transition-colors cursor-pointer"
                                title="{{ __('Notification Center') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span x-show="unreadCount > 0"
                                  x-text="unreadCount"
                                  class="absolute top-1 right-1 min-w-[16px] h-4 px-1 bg-red-500 text-white text-[9px] font-black rounded-full flex items-center justify-center ring-2 ring-white">
                            </span>
                        </button>

                        <!-- Notification Dropdown Panel -->
                        <div x-show="notifOpen"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                             style="display: none; width: 340px; max-width: calc(100vw - 2rem);"
                             class="absolute right-0 mt-2 w-80 sm:w-96 bg-white border border-gray-200 rounded-2xl shadow-2xl z-50 overflow-hidden">

                            <!-- Header -->
                            <div class="px-4 py-3 bg-gray-950 text-white flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-extrabold tracking-tight">{{ __('Notification Center') }}</span>
                                    <span x-show="unreadCount > 0"
                                          x-text="unreadCount + ' {{ app()->getLocale() === 'en' ? 'New' : 'Baru' }}'"
                                          class="px-2 py-0.5 text-[9px] font-bold bg-[#00c078] text-white rounded-full">
                                    </span>
                                </div>
                                <button type="button"
                                        x-show="unreadCount > 0"
                                        @click="markAllRead()"
                                        class="text-[10px] font-bold text-[#00c078] hover:text-emerald-300 transition-colors cursor-pointer">
                                    {{ __('Mark as Read') }}
                                </button>
                            </div>

                            <!-- Notification Items List -->
                            <div class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                                @foreach ($notifData['items'] as $notif)
                                    <a href="{{ $notif['url'] }}"
                                       class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50/90 transition-colors">
                                        <!-- Status Icon -->
                                        @if ($notif['type'] === 'danger')
                                            <div class="w-8 h-8 rounded-xl bg-red-50 text-red-500 flex items-center justify-center shrink-0 mt-0.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                            </div>
                                        @elseif ($notif['type'] === 'warning')
                                            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 mt-0.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                        @elseif ($notif['type'] === 'success')
                                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-[#00c078] flex items-center justify-center shrink-0 mt-0.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                        @endif

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="text-xs font-bold text-gray-900 truncate">{{ $notif['title'] }}</p>
                                                <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded shrink-0
                                                    {{ $notif['type'] === 'danger' ? 'bg-red-50 text-red-600' : ($notif['type'] === 'warning' ? 'bg-amber-50 text-amber-700' : ($notif['type'] === 'success' ? 'bg-emerald-50 text-[#00c078]' : 'bg-gray-100 text-gray-500')) }}">
                                                    {{ $notif['time'] }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-gray-500 leading-snug mt-0.5">{{ $notif['message'] }}</p>
                                        </div>
                                    </a>
                                @endforeach
                            </div>

                            <!-- Footer Quick Links -->
                            <div class="px-4 py-2.5 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-[11px] font-bold">
                                <a href="{{ route('budgets.index') }}" class="text-gray-600 hover:text-gray-900 transition-colors">{{ __('Manage Budgets') }}</a>
                                <a href="{{ route('transactions.index') }}" class="text-[#00c078] hover:text-emerald-700 flex items-center gap-1 transition-colors">
                                    <span>{{ __('All Transactions') }}</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Dual Language Switcher (ID | EN) -->
                    <div class="flex items-center bg-gray-100 p-0.5 rounded-xl text-xs font-bold border border-gray-200/60 shadow-2xs">
                        <a href="{{ route('locale.switch', 'id') }}"
                           class="px-2 py-1 rounded-lg transition-all text-[11px] {{ app()->getLocale() === 'id' ? 'bg-white text-emerald-700 shadow-xs font-extrabold' : 'text-gray-500 hover:text-gray-900' }}"
                           title="{{ __('Ganti ke Bahasa Indonesia') }}">
                            ID
                        </a>
                        <a href="{{ route('locale.switch', 'en') }}"
                           class="px-2 py-1 rounded-lg transition-all text-[11px] {{ app()->getLocale() === 'en' ? 'bg-white text-emerald-700 shadow-xs font-extrabold' : 'text-gray-500 hover:text-gray-900' }}"
                           title="{{ __('Switch to English') }}">
                            EN
                        </a>
                    </div>

                    <!-- Dark / Light Mode Toggle Button -->
                    <button type="button"
                            @click="toggleTheme()"
                            class="p-2 rounded-xl text-gray-500 hover:text-gray-800 hover:bg-gray-100/80 transition-colors cursor-pointer"
                            :title="darkMode ? '{{ __('Switch to Light Mode') }}' : '{{ __('Switch to Dark Mode') }}'">
                        <!-- Sun icon (shown in dark mode) -->
                        <svg x-show="darkMode" x-cloak class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <!-- Moon icon (shown in light mode) -->
                        <svg x-show="!darkMode" class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>

                    <!-- User Avatar & Details -->
                    <div class="flex items-center gap-2 sm:gap-3 pl-2 border-l border-gray-100">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 sm:gap-2.5 hover:opacity-90 transition-opacity">
                            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center text-white text-xs font-bold ring-2 ring-emerald-50 shrink-0">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-xs font-bold text-gray-900 leading-tight">{{ auth()->user()->name }}</p>
                                <p class="text-[10px] text-emerald-600 font-semibold leading-tight">Pro Member</p>
                            </div>
                        </a>

                        <form method="POST" action="{{ route('logout') }}" class="ml-0.5 sm:ml-1">
                            @csrf
                            <button type="submit" class="text-gray-400 hover:text-gray-600 p-1" title="Log Out">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto overflow-x-hidden px-4 lg:px-8 py-8 max-w-full bg-[#f1f5f9] dark:bg-[#0f131c]">
                @if (session('status'))
                    <div class="mb-5 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-medium flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- FINORA AI FINANCIAL ADVISOR WIDGET (GROQ / GROK POWERED)       -->
    <!-- ============================================================== -->
    <div x-data="finoraAiWidget()"
         @toggle-finora-ai.window="toggle()"
         class="fixed bottom-5 right-5 z-50">

        <!-- Chat Panel -->
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
             style="display: none; width: 375px; max-width: calc(100vw - 2.5rem);"
             class="mb-3 w-80 sm:w-96 bg-white rounded-2xl border border-gray-200 shadow-2xl overflow-hidden flex flex-col max-h-[80vh] sm:max-h-[560px]">

            <!-- Header -->
            <div class="px-4 py-3.5 bg-gray-950 text-white flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-[#00c078] flex items-center justify-center text-white shadow-sm shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <h4 class="text-xs font-extrabold tracking-tight text-white">Finora AI Advisor</h4>
                            <span class="px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-[#00c078]/20 text-[#00c078] rounded">Groq / Grok</span>
                        </div>
                        <p class="text-[10px] text-gray-400 flex items-center gap-1 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#00c078] animate-pulse"></span>
                            <span>Real-time Ledger Context Active</span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" @click="resetChat()" class="p-1.5 text-gray-400 hover:text-white rounded-lg hover:bg-white/10 transition-colors" title="{{ __('Reset Conversation') }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </button>
                    <button type="button" @click="open = false" class="p-1.5 text-gray-400 hover:text-white rounded-lg hover:bg-white/10 transition-colors" title="{{ __('Close') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Messages Container -->
            <div x-ref="chatBox" class="flex-1 p-4 space-y-3 overflow-y-auto bg-[#fbfcfd] min-h-[280px] max-h-[360px]">
                <template x-for="(msg, index) in messages" :key="index">
                    <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div :class="msg.role === 'user'
                                ? 'bg-[#00c078] text-white rounded-2xl rounded-br-xs px-3.5 py-2.5 max-w-[85%] text-xs font-medium shadow-2xs whitespace-pre-line leading-relaxed'
                                : 'bg-white text-gray-800 border border-gray-100 rounded-2xl rounded-bl-xs px-3.5 py-2.5 max-w-[88%] text-xs shadow-2xs whitespace-pre-line leading-relaxed'"
                             x-text="msg.content">
                        </div>
                    </div>
                </template>

                <!-- Loading Indicator -->
                <div x-show="loading" class="flex justify-start">
                    <div class="bg-white border border-gray-100 rounded-2xl rounded-bl-xs px-4 py-3 shadow-2xs flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#00c078] animate-bounce"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-[#00c078] animate-bounce" style="animation-delay: 150ms"></span>
                        <span class="w-1.5 h-1.5 rounded-full bg-[#00c078] animate-bounce" style="animation-delay: 300ms"></span>
                        <span class="text-[10px] font-semibold text-gray-400 ml-1">{{ __('Analyzing ledger data...') }}</span>
                    </div>
                </div>
            </div>

            <!-- Quick Suggestion Prompts -->
            <div class="px-3 py-2 bg-gray-50 border-t border-gray-100 flex items-center gap-1.5 overflow-x-auto">
                @if (app()->getLocale() === 'en')
                    <button type="button" @click="sendQuickPrompt('How can I manage my personal finances effectively?')"
                            class="shrink-0 px-2.5 py-1 rounded-lg bg-white hover:bg-emerald-50 text-gray-600 hover:text-emerald-700 border border-gray-200 text-[10px] font-semibold transition-colors cursor-pointer">
                        Money Management
                    </button>
                    <button type="button" @click="sendQuickPrompt('Analyze my financial health and cash flow this month')"
                            class="shrink-0 px-2.5 py-1 rounded-lg bg-white hover:bg-emerald-50 text-gray-600 hover:text-emerald-700 border border-gray-200 text-[10px] font-semibold transition-colors cursor-pointer">
                        Financial Health
                    </button>
                    <button type="button" @click="sendQuickPrompt('Are any of my budgets close to their limits?')"
                            class="shrink-0 px-2.5 py-1 rounded-lg bg-white hover:bg-emerald-50 text-gray-600 hover:text-emerald-700 border border-gray-200 text-[10px] font-semibold transition-colors cursor-pointer">
                        Budget Status
                    </button>
                    <button type="button" @click="sendQuickPrompt('What strategies can accelerate my savings goals?')"
                            class="shrink-0 px-2.5 py-1 rounded-lg bg-white hover:bg-emerald-50 text-gray-600 hover:text-emerald-700 border border-gray-200 text-[10px] font-semibold transition-colors cursor-pointer">
                        Savings Strategy
                    </button>
                @else
                    <button type="button" @click="sendQuickPrompt('Bagaimana cara mengelola keuangan dengan baik?')"
                            class="shrink-0 px-2.5 py-1 rounded-lg bg-white hover:bg-emerald-50 text-gray-600 hover:text-emerald-700 border border-gray-200 text-[10px] font-semibold transition-colors cursor-pointer">
                        Cara Kelola Keuangan
                    </button>
                    <button type="button" @click="sendQuickPrompt('Analisis kesehatan keuangan saya bulan ini')"
                            class="shrink-0 px-2.5 py-1 rounded-lg bg-white hover:bg-emerald-50 text-gray-600 hover:text-emerald-700 border border-gray-200 text-[10px] font-semibold transition-colors cursor-pointer">
                        Analisis Keuangan
                    </button>
                    <button type="button" @click="sendQuickPrompt('Apakah ada budget saya yang hampir habis?')"
                            class="shrink-0 px-2.5 py-1 rounded-lg bg-white hover:bg-emerald-50 text-gray-600 hover:text-emerald-700 border border-gray-200 text-[10px] font-semibold transition-colors cursor-pointer">
                        Cek Status Budget
                    </button>
                    <button type="button" @click="sendQuickPrompt('Bagaimana strategi mempercepat target tabungan saya?')"
                            class="shrink-0 px-2.5 py-1 rounded-lg bg-white hover:bg-emerald-50 text-gray-600 hover:text-emerald-700 border border-gray-200 text-[10px] font-semibold transition-colors cursor-pointer">
                        Strategi Tabungan
                    </button>
                @endif
            </div>

            <!-- Input Form -->
            <form @submit.prevent="sendMessage()" class="p-3 bg-white border-t border-gray-100 flex items-center gap-2">
                <input type="text"
                       x-model="input"
                       :disabled="loading"
                       placeholder="{{ __('Ask anything about your personal finances...') }}"
                       class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-xs text-gray-900 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#00c078] focus:border-transparent">
                <button type="submit"
                        :disabled="loading || !input.trim()"
                        class="w-9 h-9 rounded-xl bg-[#00c078] hover:bg-[#00a868] disabled:opacity-40 text-white flex items-center justify-center transition-colors shrink-0 cursor-pointer shadow-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>
        </div>

        <!-- Floating Trigger Button -->
        <div class="flex justify-end">
            <button type="button"
                    @click="toggle()"
                    class="group flex items-center gap-2.5 px-4 py-3 rounded-2xl bg-gray-950 hover:bg-black text-white shadow-xl shadow-gray-900/20 border border-gray-800 transition-all duration-200 cursor-pointer">
                <span class="w-7 h-7 rounded-xl bg-[#00c078] flex items-center justify-center text-white shadow-xs group-hover:scale-105 transition-transform">
                    <svg x-show="!open" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <svg x-show="open" style="display: none;" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </span>
                <div class="text-left pr-1">
                    <p class="text-xs font-extrabold leading-none text-white">Finora AI</p>
                    <p class="text-[10px] text-[#00c078] font-semibold leading-none mt-1">Financial Advisor</p>
                </div>
            </button>
        </div>
    </div>

    <script>
        function finoraAiWidget() {
            const isEnglish = '{{ app()->getLocale() }}' === 'en';
            const welcomeText = isEnglish
                ? 'Hello {{ explode(" ", auth()->user()->name)[0] }}! I am Finora AI Advisor.\n\nI am connected to your live transaction ledger, budgets, and savings targets. Feel free to ask about cash flow analysis, budgeting, or savings strategies anytime.'
                : 'Halo {{ explode(" ", auth()->user()->name)[0] }}! Saya Finora AI Advisor.\n\nSaya terhubung dengan ringkasan transaksi, budget, dan target tabungan Anda. Silakan tanyakan analisis pengeluaran atau saran keuangan kapan saja.';
            const resetText = isEnglish
                ? 'The conversation has been reset. Please feel free to ask a new question about your finances.'
                : 'Percakapan telah direset. Silakan ajukan pertanyaan baru seputar keuangan Anda.';

            return {
                open: false,
                loading: false,
                input: '',
                messages: [
                    {
                        role: 'assistant',
                        content: welcomeText
                    }
                ],
                toggle() {
                    this.open = !this.open;
                    if (this.open) {
                        this.scrollToBottom();
                    }
                },
                resetChat() {
                    this.messages = [
                        {
                            role: 'assistant',
                            content: resetText
                        }
                    ];
                },
                sendQuickPrompt(promptText) {
                    if (this.loading) return;
                    this.input = promptText;
                    this.sendMessage();
                },
                scrollToBottom() {
                    this.$nextTick(() => {
                        if (this.$refs.chatBox) {
                            this.$refs.chatBox.scrollTop = this.$refs.chatBox.scrollHeight;
                        }
                    });
                },
                async sendMessage() {
                    const text = this.input.trim();
                    if (!text || this.loading) return;

                    const historySnapshot = this.messages.slice(-6).map(m => ({
                        role: m.role,
                        content: m.content
                    }));

                    this.messages.push({ role: 'user', content: text });
                    this.input = '';
                    this.loading = true;
                    this.scrollToBottom();

                    try {
                        const response = await fetch('{{ route("ai.chat") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                message: text,
                                history: historySnapshot
                            })
                        });

                        const data = await response.json();
                        this.messages.push({
                            role: 'assistant',
                            content: data.reply || 'Maaf, tidak ada respons yang diterima.'
                        });
                    } catch (err) {
                        this.messages.push({
                            role: 'assistant',
                            content: 'Maaf, terjadi gangguan jaringan saat menghubungi Finora AI.'
                        });
                    } finally {
                        this.loading = false;
                        this.scrollToBottom();
                    }
                }
            };
        }

        document.addEventListener('keydown', function(e) {
            if ((e.metaKey || e.ctrlKey) && (e.key === 'k' || e.key === 'K')) {
                e.preventDefault();
                const searchInput = document.getElementById('globalSearchInput');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
        });
    </script>
    <x-receipt-scanner-modal />
    @stack('scripts')
</body>
</html>
