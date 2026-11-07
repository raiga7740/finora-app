<x-app-layout>
    <x-slot name="title">{{ __('Import Statement Preview') }}</x-slot>

    @php
        $totalIncome = 0;
        $totalExpense = 0;
        $duplicateCount = 0;

        foreach ($rows as $r) {
            if ($r['type'] === 'income') {
                $totalIncome += $r['amount'];
            } else {
                $totalExpense += $r['amount'];
            }
            if ($r['is_duplicate']) {
                $duplicateCount++;
            }
        }
    @endphp

    <div x-data="{
        selectAll: true,
        selectedCount: {{ count($rows) }},
        toggleAll(state) {
            const checkboxes = document.querySelectorAll('.tx-checkbox');
            checkboxes.forEach(cb => cb.checked = state);
            this.selectedCount = state ? checkboxes.length : 0;
        },
        updateCount() {
            const checked = document.querySelectorAll('.tx-checkbox:checked');
            this.selectedCount = checked.length;
            const total = document.querySelectorAll('.tx-checkbox').length;
            this.selectAll = (checked.length === total);
        }
    }">

        {{-- Header Section --}}
        <div class="mb-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[11px] font-black text-[#00c078] tracking-widest uppercase">{{ __('Smart Parser Complete') }}</span>
                    <span class="text-gray-300">•</span>
                    <span class="text-[11px] font-semibold text-gray-400">{{ __('Review & Confirmation') }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">{{ __('Transaction Statement Preview') }}</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    {{ __('Categories were automatically mapped by Finora AI. Review and adjust before saving to your ledger.') }}
                </p>
            </div>

            <a
                href="{{ route('transactions.import') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 text-xs font-semibold shadow-2xs transition"
            >
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                <span>{{ __('Upload Another File') }}</span>
            </a>
        </div>

        {{-- Top Summary Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Total Rows Parsed') }}</span>
                <div class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight mt-1">
                    {{ count($rows) }} <span class="text-xs font-semibold text-gray-500">{{ __('Transactions') }}</span>
                </div>
                <p class="text-[11px] text-gray-500 mt-0.5" x-text="selectedCount + ' {{ __('rows selected') }}'"></p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Total Outflow / Expense') }}</span>
                <div class="text-xl sm:text-2xl font-black text-red-600 tracking-tight mt-1">
                    @currency($totalExpense)
                </div>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Debits from statement') }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Total Inflow / Income') }}</span>
                <div class="text-xl sm:text-2xl font-black text-emerald-600 tracking-tight mt-1">
                    @currency($totalIncome)
                </div>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Credits / Payroll') }}</p>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Duplicate Warning') }}</span>
                <div class="text-xl sm:text-2xl font-black {{ $duplicateCount > 0 ? 'text-amber-600' : 'text-gray-900' }} tracking-tight mt-1">
                    {{ $duplicateCount }} <span class="text-xs font-semibold text-gray-500">{{ __('Potential Duplicates') }}</span>
                </div>
                <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Matching date & amount') }}</p>
            </div>
        </div>

        {{-- Form Batch Save --}}
        <form method="POST" action="{{ route('transactions.import.confirm') }}">
            @csrf
            <input type="hidden" name="wallet_id" value="{{ $selectedWalletId }}">

            {{-- Table Card --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-2xs overflow-hidden mb-6">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3 bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                x-model="selectAll"
                                @change="toggleAll($event.target.checked)"
                                class="w-4 h-4 rounded border-gray-300 text-[#00c078] focus:ring-[#00c078]"
                            >
                            <span class="text-xs font-bold text-gray-700">{{ __('Select All') }}</span>
                        </label>
                        <span class="text-xs text-gray-400">&bull;</span>
                        <span class="text-xs font-medium text-gray-500" x-text="selectedCount + ' / ' + {{ count($rows) }} + ' {{ __('selected') }}'"></span>
                    </div>

                    <div class="text-xs text-gray-500">
                        {{ __('Target Account:') }} <strong class="text-gray-800">{{ $wallets->find($selectedWalletId)?->name ?? __('Default') }}</strong>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-600">
                        <thead class="bg-gray-50/80 text-[10px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                            <tr>
                                <th class="p-4 w-10 text-center">{{ __('Select') }}</th>
                                <th class="py-3 px-4 w-32">{{ __('Date') }}</th>
                                <th class="py-3 px-4 min-w-[200px]">{{ __('Description / Merchant') }}</th>
                                <th class="py-3 px-4 w-28">{{ __('Type') }}</th>
                                <th class="py-3 px-4 w-48">{{ __('Smart Category') }}</th>
                                <th class="py-3 px-4 text-right w-36">{{ __('Amount') }}</th>
                                <th class="py-3 px-4 w-28 text-center">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($rows as $index => $row)
                                <tr class="hover:bg-gray-50/70 transition-colors {{ $row['is_duplicate'] ? 'bg-amber-50/30' : '' }}">
                                    {{-- Checkbox --}}
                                    <td class="p-4 text-center">
                                        <input
                                            type="checkbox"
                                            name="transactions[{{ $index }}][selected]"
                                            value="1"
                                            {{ $row['is_duplicate'] ? '' : 'checked' }}
                                            class="tx-checkbox w-4 h-4 rounded border-gray-300 text-[#00c078] focus:ring-[#00c078]"
                                            @change="updateCount()"
                                        >
                                    </td>

                                    {{-- Tanggal --}}
                                    <td class="py-3 px-4">
                                        <input
                                            type="date"
                                            name="transactions[{{ $index }}][date]"
                                            value="{{ $row['date'] }}"
                                            class="w-full px-2 py-1.5 rounded-lg border border-gray-200 text-xs focus:ring-1 focus:ring-[#00c078] focus:border-[#00c078]"
                                        >
                                    </td>

                                    {{-- Judul / Merchant --}}
                                    <td class="py-3 px-4">
                                        <input
                                            type="text"
                                            name="transactions[{{ $index }}][title]"
                                            value="{{ $row['title'] }}"
                                            required
                                            class="w-full px-2.5 py-1.5 rounded-lg border border-gray-200 text-xs font-semibold text-gray-900 focus:ring-1 focus:ring-[#00c078] focus:border-[#00c078]"
                                        >
                                        <input type="hidden" name="transactions[{{ $index }}][notes]" value="{{ $row['notes'] }}">
                                    </td>

                                    {{-- Tipe Transaksi --}}
                                    <td class="py-3 px-4">
                                        <select
                                            name="transactions[{{ $index }}][type]"
                                            class="w-full px-2 py-1.5 rounded-lg border border-gray-200 text-xs font-semibold {{ $row['type'] === 'income' ? 'text-emerald-700 bg-emerald-50/50' : 'text-gray-800' }} focus:ring-1 focus:ring-[#00c078]"
                                        >
                                            <option value="expense" {{ $row['type'] === 'expense' ? 'selected' : '' }}>{{ __('Expense (-)') }}</option>
                                            <option value="income" {{ $row['type'] === 'income' ? 'selected' : '' }}>{{ __('Income (+)') }}</option>
                                        </select>
                                    </td>

                                    {{-- Kategori Cerdas --}}
                                    <td class="py-3 px-4">
                                        <select
                                            name="transactions[{{ $index }}][category_id]"
                                            class="w-full px-2 py-1.5 rounded-lg border border-gray-200 text-xs focus:ring-1 focus:ring-[#00c078]"
                                        >
                                            <option value="">-- {{ __('Uncategorized') }} --</option>
                                            @foreach ($categories as $cat)
                                                <option value="{{ $cat->id }}" {{ $row['category_id'] == $cat->id ? 'selected' : '' }}>
                                                    {{ $cat->name }} ({{ ucfirst($cat->type) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    {{-- Nominal --}}
                                    <td class="py-3 px-4 text-right">
                                        <div class="relative">
                                            <input
                                                type="number"
                                                name="transactions[{{ $index }}][amount]"
                                                value="{{ $row['amount'] }}"
                                                step="any"
                                                required
                                                class="w-full text-right px-2 py-1.5 rounded-lg border border-gray-200 text-xs font-black {{ $row['type'] === 'income' ? 'text-emerald-600' : 'text-gray-900' }} focus:ring-1 focus:ring-[#00c078]"
                                            >
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td class="py-3 px-4 text-center">
                                        @if ($row['is_duplicate'])
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800" title="{{ __('Matching date and amount with existing transaction') }}">
                                                {{ __('Duplicate?') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800">
                                                {{ __('Ready to Import') }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Floating Save Bar --}}
            <div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100 shadow-lg sticky bottom-4 z-20">
                <div class="flex items-center gap-2 text-xs text-gray-600">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#00c078] animate-pulse"></span>
                    <span>{{ __('Ready to execute import into Finora financial ledger.') }}</span>
                </div>

                <div class="flex items-center gap-3">
                    <a
                        href="{{ route('transactions.import') }}"
                        class="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition"
                    >
                        {{ __('Cancel') }}
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs sm:text-sm font-bold shadow-md shadow-[#00c078]/25 transition cursor-pointer"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                        <span>{{ __('Save') }} <span x-text="selectedCount"></span> {{ __('Selected Transactions') }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
