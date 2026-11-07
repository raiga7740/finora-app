<x-app-layout>
    <x-slot name="title">{{ __('Smart Bank Statement Import') }}</x-slot>

    <div class="max-w-4xl mx-auto" x-data="{ tab: 'file', fileName: '', isDragging: false }">
        {{-- Header Section --}}
        <div class="mb-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[11px] font-black text-[#00c078] tracking-widest uppercase">{{ __('Smart Parser Engine') }}</span>
                    <span class="text-gray-300">•</span>
                    <span class="text-[11px] font-semibold text-gray-400">{{ __('Zero-Typing Automation') }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">{{ __('Smart Bank Statement Import') }}</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    {{ __('Upload your bank statement or paste transaction history to automatically import without manual data entry.') }}
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a
                    href="{{ route('transactions.import.template') }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-xs font-semibold shadow-2xs transition-all"
                >
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    <span>{{ __('Download CSV Template') }}</span>
                </a>
            </div>
        </div>

        {{-- Error Alerts --}}
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm flex items-start gap-3">
                <div class="w-6 h-6 rounded-lg bg-red-100 text-red-600 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                </div>
                <div>
                    <p class="font-bold">{{ __('Failed to process file:') }}</p>
                    <ul class="list-disc list-inside mt-1 space-y-0.5 text-xs">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Main Form Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-2xs overflow-hidden p-6 sm:p-8">
            <form method="POST" action="{{ route('transactions.import.parse') }}" enctype="multipart/form-data">
                @csrf

                {{-- Step 1: Destination Wallet --}}
                <div class="mb-6 pb-6 border-b border-gray-100">
                    <label class="block text-xs font-bold text-gray-900 uppercase tracking-wider mb-2">
                        {{ __('1. Select Destination Wallet / Account') }}
                    </label>
                    <p class="text-xs text-gray-500 mb-3">
                        {{ __('Imported transactions will be attached to this account balance.') }}
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach ($wallets as $w)
                            <label class="relative flex items-center gap-3 p-3.5 rounded-xl border border-gray-200 hover:border-emerald-400 hover:bg-emerald-50/30 cursor-pointer transition-all">
                                <input
                                    type="radio"
                                    name="wallet_id"
                                    value="{{ $w->id }}"
                                    {{ $w->is_primary || $loop->first ? 'checked' : '' }}
                                    class="w-4 h-4 text-[#00c078] focus:ring-[#00c078] border-gray-300"
                                >
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-gray-900 truncate">{{ $w->name }}</p>
                                    <p class="text-[11px] text-gray-500">{{ __('Balance:') }} @currency($w->current_balance)</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Step 2: Input Method Switcher (File vs Paste) --}}
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-xs font-bold text-gray-900 uppercase tracking-wider">
                            {{ __('2. Provide Statement Data') }}
                        </label>
                        <div class="inline-flex p-1 bg-gray-100 rounded-xl text-xs font-semibold">
                            <button
                                type="button"
                                @click="tab = 'file'"
                                :class="tab === 'file' ? 'bg-white shadow-2xs text-gray-900' : 'text-gray-500 hover:text-gray-800'"
                                class="px-3 py-1.5 rounded-lg transition-all cursor-pointer"
                            >
                                {{ __('Upload CSV File') }}
                            </button>
                            <button
                                type="button"
                                @click="tab = 'paste'"
                                :class="tab === 'paste' ? 'bg-white shadow-2xs text-gray-900' : 'text-gray-500 hover:text-gray-800'"
                                class="px-3 py-1.5 rounded-lg transition-all cursor-pointer"
                            >
                                {{ __('Paste Statement Text') }}
                            </button>
                        </div>
                    </div>

                    {{-- Mode A: Drag & Drop File CSV --}}
                    <div x-show="tab === 'file'">
                        <div
                            class="relative border-2 border-dashed rounded-2xl p-8 text-center transition-all"
                            :class="isDragging ? 'border-[#00c078] bg-emerald-50/40' : 'border-gray-200 hover:border-gray-300 bg-gray-50/50'"
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="isDragging = false; $refs.fileInput.files = $event.dataTransfer.files; fileName = $refs.fileInput.files[0] ? $refs.fileInput.files[0].name : ''"
                        >
                            <input
                                type="file"
                                name="csv_file"
                                x-ref="fileInput"
                                accept=".csv,.txt"
                                @change="fileName = $refs.fileInput.files[0] ? $refs.fileInput.files[0].name : ''"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                            >
                            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-[#00c078] mx-auto flex items-center justify-center mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                                </svg>
                            </div>
                            <h3 class="text-sm font-bold text-gray-900 mb-1" x-text="fileName ? '{{ __('Selected File:') }} ' + fileName : '{{ __('Drag & drop CSV file here, or browse') }}'"></h3>
                            <p class="text-xs text-gray-500 mb-3">{{ __('Supports .csv or .txt bank export files (Max. 5 MB)') }}</p>
                            <span class="inline-block px-3.5 py-1.5 rounded-lg bg-white border border-gray-200 text-xs font-semibold text-gray-700 shadow-2xs">
                                {{ __('Browse Computer Files') }}
                            </span>
                        </div>
                    </div>

                    {{-- Mode B: Paste Statement Text --}}
                    <div x-show="tab === 'paste'" style="display: none;">
                        <textarea
                            name="paste_text"
                            rows="6"
                            placeholder="{{ __('Copy and paste your bank statement lines here...') }}&#10;{{ __('Example format:') }}&#10;05/11/2026,QRIS GROCERIES,EXPENSE,65000&#10;04/11/2026,SHELL FUEL,EXPENSE,150000&#10;01/11/2026,PAYROLL SALARY,INCOME,8500000"
                            class="w-full p-4 rounded-xl border border-gray-200 font-mono text-xs text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                        ></textarea>
                        <p class="text-[11px] text-gray-400 mt-1.5">
                            {{ __('Tip: You can directly copy table rows from online banking or spreadsheets and paste them here.') }}
                        </p>
                    </div>
                </div>

                {{-- Action Submit Button --}}
                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-gray-500 hover:text-gray-800 transition">
                        &larr; {{ __('Back to Transactions') }}
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs sm:text-sm font-bold shadow-sm shadow-[#00c078]/25 transition-all cursor-pointer"
                    >
                        <span>{{ __('Process & Preview Statement') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        {{-- Supported Banks & Format Guide Card --}}
        <div class="mt-8 bg-gray-50/80 rounded-2xl p-6 border border-gray-100 text-xs text-gray-600">
            <h4 class="font-bold text-gray-900 mb-2 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#00c078]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                <span>{{ __('Finora Supported Statement Formats:') }}</span>
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                <div class="bg-white p-3.5 rounded-xl border border-gray-200/70">
                    <p class="font-bold text-gray-800">1. Bank BCA (KlikBCA / myBCA)</p>
                    <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">
                        {{ __('Automatic CSV detection of transaction dates, QRIS / transfer descriptions, and DB (expense) or CR (income) flags.') }}
                    </p>
                </div>
                <div class="bg-white p-3.5 rounded-xl border border-gray-200/70">
                    <p class="font-bold text-gray-800">2. Bank Mandiri (Livin\' Mandiri)</p>
                    <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">
                        {{ __('Supports statements with separate Debit and Credit columns and localized numeric formats.') }}
                    </p>
                </div>
                <div class="bg-white p-3.5 rounded-xl border border-gray-200/70">
                    <p class="font-bold text-gray-800">{{ __('3. Standard Finora CSV Template') }}</p>
                    <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">
                        {{ __('Column sequence: Date, Description, Type, Amount, Category. Use the template download button above.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
