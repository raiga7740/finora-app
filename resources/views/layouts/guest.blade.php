<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, follow">
    <title>{{ config('app.name', 'Finora') }} – {{ request()->routeIs('register') ? 'Create Account' : 'Sign In' }}</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('images/logo-192.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.getItem('finora_theme') === 'dark' || (!('finora_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="font-sans text-gray-900 antialiased bg-[#f4f7fa] min-h-screen flex flex-col justify-between overflow-x-hidden w-full selection:bg-[#0eb574] selection:text-white"
      x-data="{
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
          }
      }">

    <!-- Top Navigation Bar -->
    <header class="w-full bg-white/90 backdrop-blur-md border-b border-gray-200/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-8 h-16 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                <img src="{{ asset('images/logo.jpg') }}" alt="Finora" width="32" height="32" style="width: 32px; height: 32px; object-fit: cover;" class="w-8 h-8 rounded-lg object-cover shadow-2xs" onerror="this.style.display='none'">
                <span class="text-lg font-extrabold tracking-tight text-gray-900">Finora</span>
            </a>

            <div class="flex items-center gap-4 sm:gap-6 text-xs font-semibold text-gray-600">
                <button type="button"
                        @click="toggleTheme()"
                        class="p-2 rounded-lg text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition-colors cursor-pointer"
                        :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
                    <svg x-show="!darkMode" class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="darkMode" x-cloak class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>
                <a href="{{ url('/') }}" class="hover:text-gray-900 transition-colors">Back to Home</a>
                <a href="{{ url('/#faq') }}" class="hover:text-gray-900 transition-colors hidden sm:inline">Help &amp; Support</a>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-8 sm:py-12 w-full">
        @if (request()->routeIs('login') || request()->routeIs('register'))
            <div class="w-full max-w-[1100px] mx-auto">
                {{ $slot }}
            </div>
        @else
            <div class="w-full max-w-md mx-auto bg-white rounded-2xl border border-gray-100 shadow-xl p-8">
                {{ $slot }}
            </div>
        @endif
    </main>

    <!-- Bottom Footer Bar -->
    <footer class="w-full border-t border-gray-200/50 bg-transparent py-5">
        <div class="max-w-7xl mx-auto px-4 sm:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-gray-500 font-medium">
            <p>&copy; {{ date('Y') }} Finora Inc. All rights reserved. Precision personal financial intelligence.</p>
            <div class="flex items-center gap-5">
                <a href="{{ url('/#faq') }}" class="hover:text-gray-800 transition-colors">Privacy Policy</a>
                <a href="{{ url('/#faq') }}" class="hover:text-gray-800 transition-colors">Terms of Service</a>
                <a href="{{ url('/#faq') }}" class="hover:text-gray-800 transition-colors">Security Protocols</a>
            </div>
        </div>
    </footer>

</body>
</html>
