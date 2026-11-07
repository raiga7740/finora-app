<x-guest-layout>
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200/70 overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[620px]">

        <!-- Left Dark Command Center Panel -->
        <div class="lg:col-span-5 bg-gradient-to-br from-[#121e2d] via-[#132535] to-[#0f2b2f] text-white p-8 sm:p-10 flex flex-col justify-between relative overflow-hidden">
            <!-- Subtle ambient glow -->
            <div class="absolute -bottom-16 -right-16 w-64 h-64 bg-[#0eb574]/15 rounded-full blur-3xl pointer-events-none"></div>

            <div>
                <!-- Pill Badge -->
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/10 text-[#10b981] text-[10px] font-extrabold tracking-widest uppercase">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#10b981]"></span>
                    <span>Financial Command Center</span>
                </div>

                <!-- Main Heading -->
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight leading-tight text-white mt-5">
                    Welcome back to your financial clarity.
                </h1>

                <p class="text-xs sm:text-[13px] text-slate-400 leading-relaxed mt-3">
                    Monitor transactions, maintain budget discipline, and stay on track with your real-time wealth goals.
                </p>
            </div>

            <!-- Middle Liquid Balance Preview Card -->
            <div class="my-8 p-5 rounded-2xl bg-white/[0.06] border border-white/10 backdrop-blur-xs">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-[10px] font-bold tracking-wider uppercase text-slate-400">
                        <svg class="w-3.5 h-3.5 text-[#10b981]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/>
                        </svg>
                        <span>Total Liquid Balance</span>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#10b981]/20 text-[#10b981] text-[10px] font-bold">
                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        +14.2%
                    </span>
                </div>

                <div class="mt-4 flex items-end justify-between gap-4">
                    <div>
                        <p class="text-2xl font-black text-white tracking-tight">Rp12.500.000</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Updated 2m ago &bull; Verified ledger</p>
                    </div>
                    <!-- Mini Sparkline -->
                    <svg class="w-24 h-10 text-[#10b981] shrink-0" viewBox="0 0 96 40" fill="none">
                        <path d="M4 30 L28 27 L48 19 L68 21 L90 9" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                        <circle cx="90" cy="9" r="3" fill="#10b981"/>
                    </svg>
                </div>
            </div>

            <!-- Security Guarantees Footer -->
            <div class="pt-6 border-t border-white/10 space-y-2.5 text-xs text-slate-400">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-[#10b981] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span>256-Bit Bank Grade TLS Encryption</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-[#10b981] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/>
                    </svg>
                    <span>Hardware Token &amp; Biometric 2FA Protected</span>
                </div>
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-[#10b981] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span>Non-custodial zero data resale guarantee</span>
                </div>
            </div>
        </div>

        <!-- Right White Form Panel -->
        <div class="lg:col-span-7 bg-white p-8 sm:p-12 flex flex-col justify-between">
            <div class="max-w-md w-full mx-auto my-auto">
                <!-- Brand ID Header -->
                <div class="flex items-center gap-2.5 mb-3">
                    <img src="{{ asset('images/logo.jpg') }}" alt="Finora" width="28" height="28" style="width: 28px; height: 28px; object-fit: cover;" class="w-7 h-7 rounded-lg object-cover shadow-2xs" onerror="this.style.display='none'">
                    <span class="text-sm font-extrabold text-gray-900">Finora ID</span>
                </div>

                <h2 class="text-2xl font-extrabold text-gray-900 tracking-tight">Sign in to Finora</h2>
                <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">
                    Enter your credentials to access your dashboard. Don't have an account?
                    <a href="{{ route('register') }}" class="font-bold text-[#0eb574] hover:text-[#099960] transition-colors">Create account</a>
                </p>

                <x-auth-session-status class="mt-4 p-3 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-xl text-xs font-medium" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5" x-data="{ showPassword: false }">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="email" class="block text-xs font-bold text-gray-800">Email Address</label>
                            <span class="text-[11px] font-semibold text-[#0eb574]">Active session token</span>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <input id="email"
                                   type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   placeholder="alex.rivera@finora.io"
                                   required
                                   autofocus
                                   class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#0eb574] focus:border-transparent transition-all @error('email') border-red-500 @enderror">
                        </div>
                        @error('email')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-bold text-gray-800">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-[11px] font-bold text-[#0eb574] hover:text-[#099960] transition-colors">
                                    Forgot password?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input id="password"
                                   :type="showPassword ? 'text' : 'password'"
                                   name="password"
                                   placeholder="Enter your password"
                                   required
                                   class="w-full pl-10 pr-10 py-2.5 bg-white border border-gray-200 rounded-xl text-xs sm:text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#0eb574] focus:border-transparent transition-all @error('password') border-red-500 @enderror">
                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer"
                                    tabindex="-1">
                                <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPassword" style="display: none;" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Device Checkbox -->
                    <div class="flex items-center">
                        <label class="inline-flex items-center gap-2.5 text-xs font-medium text-gray-600 cursor-pointer select-none">
                            <input type="checkbox"
                                   name="remember"
                                   class="w-4 h-4 rounded border-gray-300 text-[#0eb574] focus:ring-[#0eb574]">
                            <span>Remember this device for 30 days</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit"
                            class="w-full py-3 px-4 bg-[#0eb574] hover:bg-[#099960] text-white text-xs sm:text-sm font-bold rounded-xl shadow-md shadow-[#0eb574]/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <span>Sign In to Dashboard</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>

                    <!-- Enterprise Security Caption -->
                    <div class="flex items-center justify-center gap-1.5 text-[11px] text-gray-400 pt-1">
                        <svg class="w-3.5 h-3.5 text-[#0eb574] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span>Protected by enterprise reCAPTCHA and bank-grade TLS.</span>
                    </div>
                </form>
            </div>

            <!-- Bottom Panel Status Bar -->
            <div class="pt-6 mt-6 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 text-[11px] text-gray-500 font-medium">
                <div class="flex items-center gap-4">
                    <a href="{{ url('/#faq') }}" class="hover:text-gray-800 transition-colors">Privacy Policy</a>
                    <a href="{{ url('/#faq') }}" class="hover:text-gray-800 transition-colors">Terms of Service</a>
                </div>
                <div class="flex items-center gap-1.5 text-gray-600 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-[#0eb574]"></span>
                    <span>All Systems Operational</span>
                </div>
            </div>
        </div>

    </div>
</x-guest-layout>
