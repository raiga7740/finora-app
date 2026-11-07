<x-guest-layout>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch" x-data="{ showPassword: false, password: '' }">
        {{-- Left Value Proposition Column --}}
        <div class="lg:col-span-5 bg-gradient-to-b from-[#e4f5ef] via-[#edf3f8] to-[#eef2f6] rounded-2xl p-6 sm:p-8 border border-emerald-100/70 flex flex-col justify-between">
            <div>
                {{-- Pill Badge --}}
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#ccf2e3] border border-[#a8e6cf] mb-5">
                    <span class="w-2 h-2 rounded-full bg-[#0eb574]"></span>
                    <span class="text-[10px] font-bold tracking-[0.12em] uppercase text-[#066943]">START IN UNDER 2 MINUTES</span>
                </div>

                {{-- Heading --}}
                <h1 class="text-2xl sm:text-[32px] font-extrabold text-[#111827] leading-[1.18] tracking-tight mb-3">
                    Take control of every <span class="text-[#0eb574]">Rupiah</span> today.
                </h1>
                <p class="text-[13px] text-gray-600 leading-relaxed mb-6">
                    High-precision personal wealth architecture designed for modern Indonesian professionals. Connect your digital ledgers effortlessly.
                </p>

                {{-- 4 Feature Cards --}}
                <div class="space-y-3 mb-6">
                    <div class="bg-white/95 rounded-xl p-3.5 border border-gray-200/60 shadow-sm flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-lg bg-[#e6f8f0] text-[#0eb574] flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-[13px] font-bold text-[#111827]">Automated Categorization</h3>
                            <p class="text-[11px] text-gray-500 leading-relaxed mt-0.5">Deep instant parsing for BCA, Bank Mandiri, and GoPay QRIS digital statements.</p>
                        </div>
                    </div>

                    <div class="bg-white/95 rounded-xl p-3.5 border border-gray-200/60 shadow-sm flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-lg bg-[#e6f8f0] text-[#0eb574] flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-[13px] font-bold text-[#111827]">80% Budget Safety Velocity</h3>
                            <p class="text-[11px] text-gray-500 leading-relaxed mt-0.5">Preemptive burn rate signals notify you days before threshold breach.</p>
                        </div>
                    </div>

                    <div class="bg-white/95 rounded-xl p-3.5 border border-gray-200/60 shadow-sm flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-lg bg-[#e6f8f0] text-[#0eb574] flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-[13px] font-bold text-[#111827]">Compound Target Pots</h3>
                            <p class="text-[11px] text-gray-500 leading-relaxed mt-0.5">Algorithmic allocation toward emergency buffers and long-term milestones.</p>
                        </div>
                    </div>

                    <div class="bg-white/95 rounded-xl p-3.5 border border-gray-200/60 shadow-sm flex items-start gap-3.5">
                        <div class="w-9 h-9 rounded-lg bg-[#e6f8f0] text-[#0eb574] flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-[13px] font-bold text-[#111827]">Audit-Ready Tax Reports</h3>
                            <p class="text-[11px] text-gray-500 leading-relaxed mt-0.5">One-click formatted CSV and PDF exports ready for annual filing.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Testimonial Card --}}
            <div class="bg-white rounded-xl p-4 border border-gray-200/70 shadow-sm">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-0.5 text-[#0eb574]">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                    <span class="text-[10px] font-bold text-gray-600 uppercase tracking-wider">4.96 / 5.0 RATING</span>
                </div>
                <h4 class="text-[13px] font-bold text-[#111827] leading-snug mb-1.5">
                    Trusted by 10,000+ modern professionals in Indonesia
                </h4>
                <p class="text-[11px] text-gray-500 leading-relaxed mb-3">
                    "Finora replaced 3 spreadsheets and gave me absolute clarity over our household runway in less than a week."
                </p>
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 font-bold text-[10px] flex items-center justify-center shrink-0">
                        DW
                    </div>
                    <div class="text-[11px] text-gray-600 truncate">
                        <span class="font-bold text-[#111827]">Dimas Wardhana</span> &bull; Lead Architect, Jakarta
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Registration Form Card --}}
        <div class="lg:col-span-7 bg-white rounded-2xl border border-gray-200/70 shadow-[0_20px_50px_-20px_rgba(15,23,42,0.12)] p-6 sm:p-10 flex flex-col justify-between">
            <div>
                <h2 class="text-2xl sm:text-[26px] font-extrabold text-[#111827] tracking-tight mb-1.5">Create your Finora account</h2>
                <p class="text-[13px] text-gray-500 mb-7">
                    Start tracking and optimizing your finances. Already have an account?
                    <a href="{{ route('login') }}" class="text-[#0eb574] font-semibold hover:text-[#099960] transition-colors">Sign in</a>
                </p>

                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    {{-- Full Name --}}
                    <div>
                        <label for="name" class="block text-xs font-semibold text-gray-700 mb-1.5">Full Name</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                </svg>
                            </span>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                autofocus
                                autocomplete="name"
                                placeholder="Alex Rivera"
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#f3f5f8] border border-transparent text-sm text-gray-900 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0eb574]/25 focus:border-[#0eb574] transition @error('name') border-red-400 ring-2 ring-red-100 @enderror"
                            >
                        </div>
                        @error('name')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email Address --}}
                    <div>
                        <label for="email" class="block text-xs font-semibold text-gray-700 mb-1.5">Email Address</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                                </svg>
                            </span>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="username"
                                placeholder="alex.rivera@example.com"
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#f3f5f8] border border-transparent text-sm text-gray-900 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0eb574]/25 focus:border-[#0eb574] transition @error('email') border-red-400 ring-2 ring-red-100 @enderror"
                            >
                        </div>
                        @error('email')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Primary Ledger Currency --}}
                    <div>
                        <label for="currency" class="block text-xs font-semibold text-gray-700 mb-1.5">Primary Ledger Currency</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                                </svg>
                            </span>
                            <select
                                id="currency"
                                name="currency"
                                class="w-full pl-10 pr-9 py-2.5 rounded-xl bg-[#f3f5f8] border border-transparent text-sm text-gray-800 font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0eb574]/25 focus:border-[#0eb574] transition"
                            >
                                <option value="IDR" {{ old('currency', 'IDR') === 'IDR' ? 'selected' : '' }}>IDR - Indonesian Rupiah (Rp)</option>
                                <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD - US Dollar ($)</option>
                                <option value="SGD" {{ old('currency') === 'SGD' ? 'selected' : '' }}>SGD - Singapore Dollar (S$)</option>
                                <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR - Euro (€)</option>
                                <option value="GBP" {{ old('currency') === 'GBP' ? 'selected' : '' }}>GBP - British Pound (£)</option>
                                <option value="JPY" {{ old('currency') === 'JPY' ? 'selected' : '' }}>JPY - Japanese Yen (¥)</option>
                                <option value="AUD" {{ old('currency') === 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar (A$)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Security Key (Password) --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-semibold text-gray-700">Security Key (Password)</label>
                            <span class="text-[11px] font-medium text-gray-500">Min. 8 characters</span>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                                </svg>
                            </span>
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                x-model="password"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••••••"
                                class="w-full pl-10 pr-10 py-2.5 rounded-xl bg-[#f3f5f8] border border-transparent text-sm text-gray-900 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0eb574]/25 focus:border-[#0eb574] transition @error('password') border-red-400 ring-2 ring-red-100 @enderror"
                            >
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                                aria-label="Toggle password visibility"
                            >
                                <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <svg x-show="showPassword" style="display: none;" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                                </svg>
                            </button>
                        </div>
                        {{-- 4-Bar Strength Meter --}}
                        <div class="grid grid-cols-4 gap-1.5 mt-2">
                            <div class="h-1 rounded-full transition-colors" :class="password.length >= 2 ? 'bg-[#0eb574]' : 'bg-gray-200'"></div>
                            <div class="h-1 rounded-full transition-colors" :class="password.length >= 6 ? 'bg-[#0eb574]' : 'bg-gray-200'"></div>
                            <div class="h-1 rounded-full transition-colors" :class="password.length >= 8 ? 'bg-[#0eb574]' : 'bg-gray-200'"></div>
                            <div class="h-1 rounded-full transition-colors" :class="password.length >= 10 && /[0-9]/.test(password) ? 'bg-[#0eb574]' : 'bg-gray-200'"></div>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm Security Key --}}
                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-gray-700 mb-1.5">Confirm Security Key</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                                </svg>
                            </span>
                            <input
                                id="password_confirmation"
                                :type="showPassword ? 'text' : 'password'"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="••••••••••••"
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#f3f5f8] border border-transparent text-sm text-gray-900 placeholder-gray-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#0eb574]/25 focus:border-[#0eb574] transition"
                            >
                        </div>
                    </div>

                    {{-- Checkboxes --}}
                    <div class="space-y-2.5 pt-1">
                        <label class="flex items-start gap-2.5 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                name="terms"
                                checked
                                class="w-4 h-4 mt-0.5 rounded border-gray-300 text-[#0eb574] focus:ring-[#0eb574]"
                            >
                            <span class="text-xs text-gray-600">
                                I agree to Finora's <a href="{{ url('/') }}" class="font-semibold text-gray-900 hover:underline">Terms of Service</a> and <a href="{{ url('/') }}" class="font-semibold text-gray-900 hover:underline">Privacy Policy</a>.
                            </span>
                        </label>
                        <label class="flex items-start gap-2.5 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                name="newsletter"
                                checked
                                class="w-4 h-4 mt-0.5 rounded border-gray-300 text-[#0eb574] focus:ring-[#0eb574]"
                            >
                            <span class="text-xs text-gray-600">
                                Send me weekly financial summaries, runway projections, and budget optimization tips.
                            </span>
                        </label>
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-2">
                        <button
                            type="submit"
                            class="w-full py-3 px-4 rounded-xl bg-[#0eb574] hover:bg-[#099960] text-white font-semibold text-sm shadow-sm hover:shadow transition-all flex items-center justify-center gap-2"
                        >
                            <span>Create Free Account</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                            </svg>
                        </button>
                    </div>

                    <p class="text-center text-[11px] text-gray-500 pt-1">
                        100% Free starter tier &bull; No credit card required &bull; Instant ledger setup
                    </p>
                </form>
            </div>

            {{-- Bottom Card Links --}}
            <div class="pt-6 mt-6 border-t border-gray-100 flex flex-wrap items-center justify-center gap-5 text-[11px] font-medium text-gray-500">
                <a href="{{ url('/#security') }}" class="inline-flex items-center gap-1.5 hover:text-gray-800 transition-colors">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                    </svg>
                    <span>Security Architecture</span>
                </a>
                <a href="{{ url('/#faq') }}" class="inline-flex items-center gap-1.5 hover:text-gray-800 transition-colors">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/>
                    </svg>
                    <span>Help Center</span>
                </a>
                <a href="{{ url('/#faq') }}" class="inline-flex items-center gap-1.5 hover:text-gray-800 transition-colors">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 006-6v-1.5m-6 7.5a6 6 0 01-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 01-3-3V4.5a3 3 0 116 0v8.25a3 3 0 01-3 3z"/>
                    </svg>
                    <span>Contact Support</span>
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
