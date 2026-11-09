<x-app-layout>
    <x-slot name="title">Settings & Profile</x-slot>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Account Settings</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage your profile credentials, password & personal preferences</p>
    </div>

    <div class="space-y-6 max-w-4xl">
        <!-- Personal Preferences -->
        <div class="p-6 bg-white border border-gray-100 rounded-2xl shadow-2xs">
            <h2 class="text-base font-bold text-gray-900 mb-1">Preferences</h2>
            <p class="text-xs text-gray-500 mb-5">Configure localized currency display and statement cycle.</p>

            <form method="POST" action="{{ route('profile.preferences.update') }}" class="space-y-5">
                @csrf
                @method('PATCH')

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label for="currency" class="block font-semibold text-gray-700 mb-1">Primary Currency</label>
                        <select id="currency" name="currency" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-gray-800 text-xs font-medium focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="IDR" @selected(old('currency', $user->currency ?? 'IDR') === 'IDR')>IDR (Indonesian Rupiah - Rp)</option>
                            <option value="USD" @selected(old('currency', $user->currency) === 'USD')>USD (United States Dollar - $)</option>
                            <option value="SGD" @selected(old('currency', $user->currency) === 'SGD')>SGD (Singapore Dollar - S$)</option>
                            <option value="EUR" @selected(old('currency', $user->currency) === 'EUR')>EUR (Euro - €)</option>
                            <option value="GBP" @selected(old('currency', $user->currency) === 'GBP')>GBP (British Pound - £)</option>
                            <option value="JPY" @selected(old('currency', $user->currency) === 'JPY')>JPY (Japanese Yen - ¥)</option>
                            <option value="AUD" @selected(old('currency', $user->currency) === 'AUD')>AUD (Australian Dollar - A$)</option>
                        </select>
                        <x-input-error class="mt-1" :messages="$errors->get('currency')" />
                        <p class="text-[10px] text-gray-400 mt-1">Realtime forex conversion automatically updates all balances.</p>
                    </div>

                    <div>
                        <label for="locale" class="block font-semibold text-gray-700 mb-1">Language / Bahasa</label>
                        <select id="locale" name="locale" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-gray-800 text-xs font-medium focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="en" @selected(old('locale', $user->locale ?? 'en') === 'en')>English (Global / International)</option>
                            <option value="id" @selected(old('locale', $user->locale) === 'id')>Bahasa Indonesia</option>
                        </select>
                        <x-input-error class="mt-1" :messages="$errors->get('locale')" />
                        <p class="text-[10px] text-gray-400 mt-1">AI Advisor & interface language.</p>
                    </div>

                    <div>
                        <label for="timezone" class="block font-semibold text-gray-700 mb-1">Timezone</label>
                        <select id="timezone" name="timezone" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-gray-800 text-xs font-medium focus:ring-emerald-500 focus:border-emerald-500">
                            <optgroup label="Americas">
                                <option value="America/New_York" @selected(old('timezone', $user->timezone ?? 'Asia/Jakarta') === 'America/New_York')>Eastern Time (US / NY) [UTC-5]</option>
                                <option value="America/Chicago" @selected(old('timezone', $user->timezone) === 'America/Chicago')>Central Time (US / Chicago) [UTC-6]</option>
                                <option value="America/Los_Angeles" @selected(old('timezone', $user->timezone) === 'America/Los_Angeles')>Pacific Time (US / LA) [UTC-8]</option>
                            </optgroup>
                            <optgroup label="Europe">
                                <option value="Europe/London" @selected(old('timezone', $user->timezone) === 'Europe/London')>London / Dublin [UTC+0]</option>
                                <option value="Europe/Paris" @selected(old('timezone', $user->timezone) === 'Europe/Paris')>Paris / Berlin / Rome [UTC+1]</option>
                            </optgroup>
                            <optgroup label="Asia & Pacific">
                                <option value="Asia/Dubai" @selected(old('timezone', $user->timezone) === 'Asia/Dubai')>Dubai (Gulf) [UTC+4]</option>
                                <option value="Asia/Jakarta" @selected(old('timezone', $user->timezone ?? 'Asia/Jakarta') === 'Asia/Jakarta')>WIB / Jakarta [UTC+7]</option>
                                <option value="Asia/Singapore" @selected(old('timezone', $user->timezone) === 'Asia/Singapore')>Singapore / KL [UTC+8]</option>
                                <option value="Asia/Tokyo" @selected(old('timezone', $user->timezone) === 'Asia/Tokyo')>Tokyo / Seoul [UTC+9]</option>
                                <option value="Australia/Sydney" @selected(old('timezone', $user->timezone) === 'Australia/Sydney')>Sydney / Melbourne [UTC+11]</option>
                            </optgroup>
                            <optgroup label="Standard">
                                <option value="UTC" @selected(old('timezone', $user->timezone) === 'UTC')>UTC Universal</option>
                            </optgroup>
                        </select>
                        <x-input-error class="mt-1" :messages="$errors->get('timezone')" />
                        <p class="text-[10px] text-gray-400 mt-1">Calendar & statement timestamps.</p>
                    </div>

                    <div>
                        <label for="date_format" class="block font-semibold text-gray-700 mb-1">Date Format</label>
                        <select id="date_format" name="date_format" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-gray-800 text-xs font-medium focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="M d, Y" @selected(old('date_format', $user->date_format ?? 'd M Y') === 'M d, Y')>MMM DD, YYYY (e.g. Oct 25, 2026 - US Standard)</option>
                            <option value="d M Y" @selected(old('date_format', $user->date_format ?? 'd M Y') === 'd M Y')>DD MMM YYYY (e.g. 25 Oct 2026)</option>
                            <option value="d/m/Y" @selected(old('date_format', $user->date_format) === 'd/m/Y')>DD/MM/YYYY (e.g. 25/10/2026 - UK/Europe)</option>
                            <option value="Y-m-d" @selected(old('date_format', $user->date_format) === 'Y-m-d')>YYYY-MM-DD (e.g. 2026-10-25 - ISO)</option>
                        </select>
                        <x-input-error class="mt-1" :messages="$errors->get('date_format')" />
                    </div>

                    <div>
                        <label for="ledger_mode" class="block font-semibold text-gray-700 mb-1">Audit Ledger Mode</label>
                        <select id="ledger_mode" name="ledger_mode" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-gray-800 text-xs font-medium focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="strict_realtime" @selected(old('ledger_mode', $user->ledger_mode ?? 'strict_realtime') === 'strict_realtime')>Strict Real-time Balance</option>
                            <option value="standard_daily" @selected(old('ledger_mode', $user->ledger_mode) === 'standard_daily')>Standard Daily Settlement</option>
                        </select>
                        <x-input-error class="mt-1" :messages="$errors->get('ledger_mode')" />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">{{ __('Theme Mode') }}</label>
                        <div class="flex items-center gap-2 pt-1" x-data="{
                            activeTheme: localStorage.getItem('finora_theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
                            setTheme(mode) {
                                this.activeTheme = mode;
                                localStorage.setItem('finora_theme', mode);
                                if (mode === 'dark') {
                                    document.documentElement.classList.add('dark');
                                    darkMode = true;
                                } else {
                                    document.documentElement.classList.remove('dark');
                                    darkMode = false;
                                }
                            }
                        }">
                            <button type="button"
                                    @click="setTheme('light')"
                                    :class="activeTheme === 'light' ? 'bg-[#00c078] text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <span>{{ __('Light Mode') }}</span>
                            </button>
                            <button type="button"
                                    @click="setTheme('dark')"
                                    :class="activeTheme === 'dark' ? 'bg-[#00c078] text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                </svg>
                                <span>{{ __('Dark Mode') }}</span>
                            </button>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">{{ __('Choose your visual theme.') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-1">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                        Save Preferences
                    </button>

                    @if (session('status') === 'preferences-updated')
                        <p
                            x-data="{ show: true }"
                            x-show="show"
                            x-transition
                            x-init="setTimeout(() => show = false, 5000)"
                            class="text-xs font-medium text-emerald-600 flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ session('conversion_message') ?? 'Preferences successfully updated.' }}
                        </p>
                    @endif
                </div>
            </form>
        </div>

        <!-- App Version & Release Info -->
        <div class="p-6 bg-white border border-gray-100 rounded-2xl shadow-2xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-[#00c078] flex items-center justify-center font-black">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-base font-bold text-gray-900">{{ __('Info & Versi Aplikasi') }}</h2>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                            v{{ config('app.version', '2.5.0') }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ __('Finora') }} {{ config('app.version_codename', 'Aurora Zenith') }} &bull; {{ __('Lihat riwayat rilis & pembaruan sistem.') }}
                    </p>
                </div>
            </div>

            <a href="{{ route('info.index') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-100 hover:bg-[#00c078] text-gray-700 hover:text-white text-xs font-bold rounded-xl transition-all shadow-2xs self-start sm:self-auto">
                <span>{{ __('Lihat Catatan Rilis & Changelog') }}</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <!-- Profile Information -->
        <div class="p-6 bg-white border border-gray-100 rounded-2xl shadow-2xs">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <!-- Update Password -->
        <div class="p-6 bg-white border border-gray-100 rounded-2xl shadow-2xs">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <!-- Delete Account -->
        <div class="p-6 bg-white border border-gray-100 rounded-2xl shadow-2xs">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
