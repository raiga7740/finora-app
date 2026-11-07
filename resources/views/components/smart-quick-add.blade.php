@php
    $user = auth()->user();
    $quickWallets = $user ? $user->wallets()->orderByDesc('is_primary')->orderBy('name')->get(['id', 'name', 'currency', 'is_primary']) : collect();
    $quickCategories = $user ? $user->categories()->orderBy('name')->get(['id', 'name', 'type']) : collect();
@endphp

<div x-data="{
    rawInput: '',
    isFocused: false,
    customType: '',
    customCategoryId: '',
    customWalletId: '',
    wallets: @js($quickWallets),
    categories: @js($quickCategories),
    currencySymbol: @js($user?->currency_symbol ?? 'Rp'),
    parseLive() {
        const text = (this.rawInput || '').trim();
        if (!text) {
            return { title: '', amount: 0, type: 'expense', categoryId: '', walletId: '', dateLabel: '{{ __('Today') }}', dateValue: '{{ now()->toDateString() }}' };
        }
        let working = text;

        // 1. Date detection
        let dateLabel = '{{ __('Today') }}';
        let dateValue = '{{ now()->toDateString() }}';
        if (/\b(kemarin|yesterday)\b/i.test(working)) {
            dateLabel = '{{ __('Yesterday') }}';
            dateValue = '{{ now()->subDay()->toDateString() }}';
            working = working.replace(/\b(kemarin|yesterday)\b/i, ' ');
        } else if (/\b(hari\s+ini|today)\b/i.test(working)) {
            working = working.replace(/\b(hari\s+ini|today)\b/i, ' ');
        }

        // 2. Amount detection (rb, ribu, k, jt, juta, m, or plain numbers)
        let amount = 0;
        const unitMatch = working.match(/(?:rp\.?\s*)?(\d+(?:[.,]\d+)?)\s*(jt|juta|m|rb|ribu|k)\b/i);
        if (unitMatch) {
            const val = parseFloat(unitMatch[1].replace(',', '.')) || 0;
            const unit = unitMatch[2].toLowerCase();
            amount = (unit === 'jt' || unit === 'juta' || unit === 'm') ? Math.round(val * 1000000) : Math.round(val * 1000);
            working = working.replace(unitMatch[0], ' ');
        } else {
            const numMatch = working.match(/(?:rp\.?\s*)?(\d{1,3}(?:\.\d{3})+|\d{1,3}(?:,\d{3})+|\d+(?:\.\d+)?)\b/i);
            if (numMatch) {
                let rawNum = numMatch[1];
                if (/^\d{1,3}(\.\d{3})+$/.test(rawNum)) {
                    rawNum = rawNum.replace(/\./g, '');
                } else if (/^\d{1,3}(,\d{3})+$/.test(rawNum)) {
                    rawNum = rawNum.replace(/,/g, '');
                } else {
                    rawNum = rawNum.replace(',', '.');
                }
                amount = parseFloat(rawNum) || 0;
                working = working.replace(numMatch[0], ' ');
            }
        }

        // 3. Wallet detection
        let detectedWalletId = '';
        for (const w of this.wallets) {
            const tokens = w.name.toLowerCase().replace(/[^a-z0-9\s]/g, ' ').split(/\s+/).filter(t => t.length >= 3 && !['bank', 'dompet', 'wallet', 'account', 'rekening', 'utama'].includes(t));
            let matched = false;
            for (const tok of tokens) {
                const re = new RegExp('\\b' + tok + '\\b', 'i');
                if (re.test(working)) {
                    detectedWalletId = w.id;
                    working = working.replace(re, ' ');
                    matched = true;
                    break;
                }
            }
            if (matched) break;
        }
        if (!detectedWalletId && this.wallets.length > 0) {
            detectedWalletId = this.wallets[0].id;
        }
        working = working.replace(/\b(bca|mandiri|bri|bni|jago|seabank|gopay|ovo|dana|shopeepay|qris|tunai|cash|paypal)\b/i, ' ');

        // 4. Type detection
        let detectedType = /\b(gaji|salary|bonus|thr|terima|masuk|inflow|freelance|proyek|project|komisi|dividen|refund|cashback|jual|pendapatan|pemasukan)\b/i.test(text) ? 'income' : 'expense';
        const finalType = this.customType || detectedType;

        // 5. Category detection
        let detectedCatId = '';
        const lower = text.toLowerCase();
        const findCat = (keywords) => this.categories.find(c => keywords.some(k => c.name.toLowerCase().includes(k)));
        if (/(makan|minum|kopi|nasi|sarapan|siang|malam|snack|jajan|resto|warung|gofood|grabfood|coffee|lunch|dinner|bakso|mie|ayam|sate|padang|ramen|sushi)/.test(lower)) {
            detectedCatId = findCat(['food', 'makan', 'dining'])?.id || '';
        } else if (/(bensin|pertamax|pertalite|parkir|tol|gojek|grab|mrt|krl|kereta|bus|transport|fuel)/.test(lower)) {
            detectedCatId = findCat(['transport', 'perjalanan'])?.id || '';
        } else if (/(listrik|pln|token|air|pdam|wifi|internet|indihome|pulsa|kuota|bpjs|sewa|kos)/.test(lower)) {
            detectedCatId = findCat(['bill', 'tagihan', 'utilit'])?.id || '';
        } else if (/(nonton|bioskop|netflix|spotify|game|steam|liburan|hotel|hiburan)/.test(lower)) {
            detectedCatId = findCat(['entertain', 'hiburan'])?.id || '';
        } else if (/(belanja|shopee|tokopedia|indomaret|alfamart|baju|sepatu|skincare|gadget)/.test(lower)) {
            detectedCatId = findCat(['shop', 'belanja', 'lifestyle'])?.id || '';
        } else if (/(gaji|salary|thr|upah)/.test(lower)) {
            detectedCatId = findCat(['salary', 'gaji', 'inflow'])?.id || '';
        } else if (/(freelance|proyek|project|klien|komisi)/.test(lower)) {
            detectedCatId = findCat(['freelance', 'project'])?.id || '';
        }
        if (!detectedCatId) {
            detectedCatId = this.categories.find(c => c.type === finalType)?.id || (this.categories[0]?.id || '');
        }

        // 6. Clean Title
        working = working.replace(/\b(via|pakai|dengan|ke|dari|di|pake|using)\b$/i, '').replace(/\s+/g, ' ').trim();
        const title = working ? working.replace(/\b\w/g, c => c.toUpperCase()) : (finalType === 'income' ? '{{ __('Quick Income') }}' : '{{ __('Quick Expense') }}');

        return {
            title,
            amount,
            type: finalType,
            categoryId: this.customCategoryId || detectedCatId,
            walletId: this.customWalletId || detectedWalletId,
            dateLabel,
            dateValue
        };
    },
    formatPreviewMoney(val) {
        const num = Math.round(parseFloat(val) || 0);
        return this.currencySymbol + num.toLocaleString('id-ID');
    },
    fillExample(sample) {
        this.rawInput = sample;
        this.customType = '';
        this.customCategoryId = '';
        this.customWalletId = '';
    }
}" class="bg-white rounded-2xl border border-gray-100 p-3.5 sm:p-4 shadow-sm mb-6">
    <form method="POST" action="{{ route('transactions.quick-add') }}">
        @csrf
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
            <!-- Natural Language Input Box -->
            <div class="relative flex-1 flex items-center bg-gray-50/90 hover:bg-gray-50 focus-within:bg-white focus-within:ring-2 focus-within:ring-[#00c078] border border-gray-200/80 rounded-xl px-3.5 py-2.5 transition-all">
                <div class="w-7 h-7 rounded-lg bg-[#e6f9f1] text-[#00c078] flex items-center justify-center shrink-0 mr-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>

                <input type="text"
                       name="raw_input"
                       x-model="rawInput"
                       @focus="isFocused = true"
                       required
                       autocomplete="off"
                       placeholder="{{ __('Smart Quick-Add: ketik misal &quot;makan siang padang 35rb bca&quot; atau &quot;gaji 8.5jt mandiri&quot; lalu tekan Enter...') }}"
                       class="w-full bg-transparent border-0 p-0 text-xs sm:text-sm font-medium text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0">

                <template x-if="rawInput.length > 0">
                    <button type="button"
                            @click="rawInput = ''; customType = ''; customCategoryId = ''; customWalletId = ''"
                            class="text-gray-400 hover:text-gray-600 ml-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </template>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs sm:text-sm font-bold transition shadow-sm shrink-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ __('Quick Save') }}</span>
            </button>
        </div>

        <!-- Hidden Override Inputs synced from live parser -->
        <input type="hidden" name="override_type" :value="parseLive().type">
        <input type="hidden" name="override_category_id" :value="parseLive().categoryId">
        <input type="hidden" name="override_wallet_id" :value="parseLive().walletId">

        <!-- Live Smart Detection Preview Bar (Appears while typing) -->
        <div x-show="rawInput.trim().length > 0"
             x-cloak
             class="mt-3 pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 text-xs">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Detected') }}:</span>

                <!-- Clickable Type Pill -->
                <button type="button"
                        @click="customType = (parseLive().type === 'expense' ? 'income' : 'expense')"
                        :class="parseLive().type === 'income' ? 'bg-[#e6f9f1] text-[#00995f] border-[#00c078]/30' : 'bg-red-50 text-red-600 border-red-200'"
                        class="px-2.5 py-1 rounded-lg border text-[11px] font-extrabold uppercase cursor-pointer transition"
                        title="{{ __('Click to switch Income / Expense') }}">
                    <span x-text="parseLive().type === 'income' ? '+ {{ __('Income') }}' : '- {{ __('Expense') }}'"></span>
                </button>

                <!-- Detected Title -->
                <span class="px-2.5 py-1 rounded-lg bg-gray-100 text-gray-800 font-bold" x-text="parseLive().title"></span>

                <!-- Detected Amount -->
                <span class="px-2.5 py-1 rounded-lg font-extrabold"
                      :class="parseLive().amount > 0 ? 'bg-gray-900 text-white' : 'bg-amber-50 text-amber-700'"
                      x-text="parseLive().amount > 0 ? formatPreviewMoney(parseLive().amount) : '{{ __('Type amount, e.g. 35rb') }}'">
                </span>

                <!-- Category Dropdown Override -->
                <select x-model="customCategoryId"
                        class="py-1 pl-2.5 pr-7 rounded-lg border-gray-200 bg-gray-50 text-xs font-semibold text-gray-700 focus:border-[#00c078] focus:ring-[#00c078]">
                    <option value="">{{ __('Auto Category') }}</option>
                    @foreach ($quickCategories as $cat)
                        <option value="{{ $cat->id }}" :selected="parseLive().categoryId == {{ $cat->id }}">
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>

                <!-- Wallet Dropdown Override -->
                @if ($quickWallets->isNotEmpty())
                    <select x-model="customWalletId"
                            class="py-1 pl-2.5 pr-7 rounded-lg border-gray-200 bg-gray-50 text-xs font-semibold text-gray-700 focus:border-[#00c078] focus:ring-[#00c078]">
                        @foreach ($quickWallets as $w)
                            <option value="{{ $w->id }}" :selected="parseLive().walletId == {{ $w->id }}">
                                {{ $w->name }}
                            </option>
                        @endforeach
                    </select>
                @endif

                <!-- Detected Date -->
                <span class="px-2 py-1 rounded-lg bg-gray-50 border border-gray-200 text-[11px] font-semibold text-gray-500"
                      x-text="parseLive().dateLabel"></span>
            </div>
        </div>

        <!-- Quick Example Chips when input is empty -->
        <div x-show="rawInput.trim().length === 0" class="mt-2.5 flex flex-wrap items-center gap-1.5 text-[11px]">
            <span class="text-gray-400 font-semibold">{{ __('Try example') }}:</span>
            <button type="button" @click="fillExample('makan siang padang 35rb bca')"
                    class="px-2 py-0.5 rounded-md bg-gray-100 hover:bg-[#e6f9f1] text-gray-600 hover:text-[#00995f] font-medium transition cursor-pointer">
                "makan siang padang 35rb bca"
            </button>
            <button type="button" @click="fillExample('kopi kenangan 24rb gopay')"
                    class="px-2 py-0.5 rounded-md bg-gray-100 hover:bg-[#e6f9f1] text-gray-600 hover:text-[#00995f] font-medium transition cursor-pointer">
                "kopi kenangan 24rb gopay"
            </button>
            <button type="button" @click="fillExample('bensin pertamax 50rb tunai kemarin')"
                    class="px-2 py-0.5 rounded-md bg-gray-100 hover:bg-[#e6f9f1] text-gray-600 hover:text-[#00995f] font-medium transition cursor-pointer">
                "bensin pertamax 50rb tunai kemarin"
            </button>
            <button type="button" @click="fillExample('terima bonus proyek 1.5jt mandiri')"
                    class="px-2 py-0.5 rounded-md bg-gray-100 hover:bg-[#e6f9f1] text-gray-600 hover:text-[#00995f] font-medium transition cursor-pointer">
                "terima bonus proyek 1.5jt mandiri"
            </button>
        </div>
    </form>
</div>

