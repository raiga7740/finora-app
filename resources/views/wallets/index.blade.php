<x-app-layout>
    <x-slot name="title">Wallets & Accounts</x-slot>

    <div x-data="{
        createModalOpen: false,
        editModalOpen: false,
        transferModalOpen: false,
        presetFilter: 'all',
        rates: {{ json_encode($currencyService->getRatesToIdr()) }},
        walletList: {{ json_encode($wallets->map(fn($w) => [
            'id' => $w->id,
            'name' => $w->name,
            'type' => $w->type,
            'currency' => strtoupper($w->currency ?? auth()->user()->currency ?? 'IDR'),
            'symbol' => $w->currency_symbol,
            'balance' => (float) $w->current_balance,
            'color' => $w->color,
            'notes' => $w->notes ?? '',
            'account_number' => $w->account_number ?? '',
            'initial_balance' => (float) $w->initial_balance,
            'is_primary' => (bool) $w->is_primary,
        ])) }},
        presets: [
            // International / US & Global Banks
            { category: 'global', name: 'Chase Checking', type: 'bank', currency: 'USD', color: '#1170cf', notes: 'Primary checking & direct deposit' },
            { category: 'global', name: 'Bank of America', type: 'bank', currency: 'USD', color: '#e31837', notes: 'Advantage banking' },
            { category: 'global', name: 'Wells Fargo', type: 'bank', currency: 'USD', color: '#d71e28', notes: 'Everyday checking' },
            { category: 'global', name: 'Citibank', type: 'bank', currency: 'USD', color: '#002d72', notes: 'Citi Priority account' },
            { category: 'global', name: 'Barclays', type: 'bank', currency: 'GBP', color: '#00aeef', notes: 'UK current account' },
            { category: 'global', name: 'HSBC Global', type: 'bank', currency: 'GBP', color: '#db0011', notes: 'International banking' },
            { category: 'global', name: 'Revolut', type: 'bank', currency: 'EUR', color: '#0075eb', notes: 'Multi-currency account' },
            { category: 'global', name: 'Monzo', type: 'bank', currency: 'GBP', color: '#ff3366', notes: 'Current account & savings pots' },
            { category: 'global', name: 'N26 Smart', type: 'bank', currency: 'EUR', color: '#36a18b', notes: 'European digital bank' },
            { category: 'global', name: 'DBS Bank', type: 'bank', currency: 'SGD', color: '#d90000', notes: 'Singapore multi-currency account' },
            { category: 'global', name: 'Commonwealth Bank', type: 'bank', currency: 'AUD', color: '#ffcc00', notes: 'CommBank Smart Access' },
            { category: 'global', name: 'MUFG Bank', type: 'bank', currency: 'JPY', color: '#ed1a3d', notes: 'Japan primary checking' },

            // Digital Wallets & Global Fintech
            { category: 'ewallet', name: 'PayPal', type: 'ewallet', currency: 'USD', color: '#0079c1', notes: 'Online payments & balance' },
            { category: 'ewallet', name: 'Wise', type: 'ewallet', currency: 'USD', color: '#2ed06e', notes: 'International multi-currency wallet' },
            { category: 'ewallet', name: 'Apple Pay / Cash', type: 'ewallet', currency: 'USD', color: '#1c1c1e', notes: 'Apple Wallet contactless cash' },
            { category: 'ewallet', name: 'Google Wallet', type: 'ewallet', currency: 'USD', color: '#4285f4', notes: 'Google Pay balance' },
            { category: 'ewallet', name: 'Stripe Balance', type: 'ewallet', currency: 'USD', color: '#635bff', notes: 'Merchant revenue & payouts' },
            { category: 'ewallet', name: 'Venmo', type: 'ewallet', currency: 'USD', color: '#008cff', notes: 'P2P social payments' },
            { category: 'ewallet', name: 'Cash App', type: 'ewallet', currency: 'USD', color: '#00d632', notes: 'Square digital cash' },
            { category: 'ewallet', name: 'GrabPay', type: 'ewallet', currency: 'SGD', color: '#00b140', notes: 'Daily QR & transit wallet' },

            // Indonesian Banks & E-Wallets
            { category: 'id', name: 'Bank BCA', type: 'bank', currency: 'IDR', color: '#005baa', notes: 'Rekening operasional utama' },
            { category: 'id', name: 'Bank Mandiri', type: 'bank', currency: 'IDR', color: '#003366', notes: 'Rekening tabungan sekunder' },
            { category: 'id', name: 'Bank BRI', type: 'bank', currency: 'IDR', color: '#00529c', notes: 'BritAma tabungan' },
            { category: 'id', name: 'Bank BNI', type: 'bank', currency: 'IDR', color: '#f15a22', notes: 'Taplus account' },
            { category: 'id', name: 'Bank Jago', type: 'bank', currency: 'IDR', color: '#ff6600', notes: 'Kantong utama Jago' },
            { category: 'id', name: 'Bank Syariah (BSI)', type: 'bank', currency: 'IDR', color: '#00a39d', notes: 'Tabungan wadiah' },
            { category: 'id', name: 'GoPay', type: 'ewallet', currency: 'IDR', color: '#00aed6', notes: 'Dompet digital & QRIS' },
            { category: 'id', name: 'OVO', type: 'ewallet', currency: 'IDR', color: '#4c3494', notes: 'Dompet digital OVO' },
            { category: 'id', name: 'DANA', type: 'ewallet', currency: 'IDR', color: '#118eea', notes: 'Dompet digital DANA' },
            { category: 'id', name: 'ShopeePay', type: 'ewallet', currency: 'IDR', color: '#ee4d2d', notes: 'Dompet digital Shopee' },
        ],
        newWallet: {
            name: '',
            type: 'bank',
            currency: '{{ auth()->user()->currency ?? 'IDR' }}',
            account_number: '',
            initial_balance: 0,
            color: '#00c078',
            is_primary: false,
            notes: ''
        },
        applyPreset(p) {
            this.newWallet.name = p.name;
            this.newWallet.type = p.type;
            this.newWallet.color = p.color;
            if (p.currency) {
                this.newWallet.currency = p.currency;
            }
            if (!this.newWallet.notes) {
                this.newWallet.notes = p.notes;
            }
        },
        activeWallet: {
            id: '',
            name: '',
            type: 'bank',
            currency: '{{ auth()->user()->currency ?? 'IDR' }}',
            account_number: '',
            initial_balance: 0,
            color: '#00c078',
            is_primary: false,
            notes: ''
        },
        transferData: {
            source_wallet_id: '',
            destination_wallet_id: '',
            amount: '',
            destination_amount: '',
            isCrossCurrency: false,
            rateText: '',
            notes: '',
            transaction_date: '{{ now()->toDateString() }}'
        },
        calculateTransfer() {
            const src = this.walletList.find(w => w.id == this.transferData.source_wallet_id);
            const dst = this.walletList.find(w => w.id == this.transferData.destination_wallet_id);
            if (!src || !dst) {
                this.transferData.isCrossCurrency = false;
                return;
            }
            if (src.currency === dst.currency) {
                this.transferData.isCrossCurrency = false;
                this.transferData.destination_amount = this.transferData.amount;
                return;
            }
            this.transferData.isCrossCurrency = true;
            const srcToIdr = this.rates[src.currency] || 1;
            const dstToIdr = this.rates[dst.currency] || 1;
            const rate = srcToIdr / dstToIdr;
            const amt = parseFloat(this.transferData.amount) || 0;
            const converted = amt * rate;
            this.transferData.destination_amount = (dst.currency === 'IDR' || dst.currency === 'JPY')
                ? Math.round(converted)
                : Number(converted.toFixed(2));
            this.transferData.rateText = '1 ' + src.currency + ' ≈ ' + (rate < 1 ? rate.toFixed(4) : (rate > 100 ? Math.round(rate) : rate.toFixed(2))) + ' ' + dst.currency;
        },
        getSourceCurrencySymbol() {
            const src = this.walletList.find(w => w.id == this.transferData.source_wallet_id);
            return src ? src.symbol : '{{ currency_symbol() }}';
        },
        getDestCurrencySymbol() {
            const dst = this.walletList.find(w => w.id == this.transferData.destination_wallet_id);
            return dst ? dst.symbol : '{{ currency_symbol() }}';
        },
        openEdit(wallet) {
            this.activeWallet = Object.assign({}, wallet);
            if (!this.activeWallet.currency) {
                this.activeWallet.currency = '{{ auth()->user()->currency ?? 'IDR' }}';
            }
            this.editModalOpen = true;
        },
        openTransferFrom(walletId) {
            this.transferData.source_wallet_id = walletId;
            this.calculateTransfer();
            this.transferModalOpen = true;
        }
    }">

        {{-- Top Header Section --}}
        <div class="mb-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[11px] font-black text-[#00c078] tracking-widest uppercase">{{ __('Multi-Asset Ledger') }}</span>
                    <span class="text-gray-300">•</span>
                    <span class="text-[11px] font-semibold text-gray-400">{{ __('Liquid Capital Architecture') }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">{{ __('Wallets & Accounts') }}</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    {{ __('Monitor cash balances across all accounts, e-wallets, cash, and easily transfer funds between accounts.') }}
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <button
                    type="button"
                    @click="transferModalOpen = true"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-xs sm:text-sm font-semibold shadow-2xs transition-all cursor-pointer"
                >
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                    </svg>
                    <span>{{ __('Transfer Funds') }}</span>
                </button>

                <button
                    type="button"
                    @click="createModalOpen = true"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs sm:text-sm font-semibold shadow-sm shadow-[#00c078]/20 transition-all cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    <span>{{ __('Add Wallet') }}</span>
                </button>
            </div>
        </div>

        {{-- Flash Status Message --}}
        @if (session('status'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200/70 text-emerald-800 text-xs sm:text-sm flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="font-medium">{{ session('status') }}</span>
                </div>
            </div>
        @endif

        {{-- Top 4 KPI Metrics Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
            {{-- Card 1: Total Liquid Net Worth --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Total Liquid Balance') }}</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-[#00c078] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">
                        @currency($totalNetWorth)
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">{{ __('Combined active accounts') }}</p>
                </div>
            </div>

            {{-- Card 2: Saldo Bank --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Bank Accounts') }}</span>
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.5m-15 10.5V10.5M3 21h18"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="text-xl sm:text-2xl font-black text-blue-600 tracking-tight">
                        @currency($totalBank)
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">{{ __('BCA, Mandiri, PayPal, etc.') }}</p>
                </div>
            </div>

            {{-- Card 3: Saldo E-Wallet --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Digital Wallets') }}</span>
                    <div class="w-8 h-8 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="text-xl sm:text-2xl font-black text-cyan-600 tracking-tight">
                        @currency($totalEwallet)
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">{{ __('GoPay, OVO, Wise, etc.') }}</p>
                </div>
            </div>

            {{-- Card 4: Saldo Tunai & Lainnya --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Cash & Physical') }}</span>
                    <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">
                        @currency($totalCash + $totalInvestment)
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">{{ __('Physical cash & liquid assets') }}</p>
                </div>
            </div>
        </div>

        {{-- Wallets Digital Cards Grid --}}
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">{{ __('Your Wallets & Accounts') }}</h2>
                <span class="text-xs text-gray-400">{{ $wallets->count() }} {{ __('Connected Accounts') }}</span>
            </div>

            @if ($wallets->isEmpty())
                <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center shadow-2xs">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-[#00c078] flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-gray-900">{{ __('No Wallets or Accounts Yet') }}</h3>
                    <p class="text-xs text-gray-500 max-w-md mx-auto mt-1">
                        {{ __('Add your first bank account, digital wallet (e-wallet), or physical cash wallet to start tracking your real balances.') }}
                    </p>
                    <div class="mt-4">
                        <button
                            type="button"
                            @click="createModalOpen = true"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-semibold shadow-sm transition-all cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                            <span>{{ __('Add Wallet') }}</span>
                        </button>
                    </div>
                </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($wallets as $wallet)
                    @php
                        $balance = $wallet->current_balance;
                        $isNeg = $balance < 0;
                    @endphp
                    <div class="rounded-2xl p-5 border border-gray-100 bg-white shadow-2xs hover:shadow-md transition-all flex flex-col justify-between relative overflow-hidden group">
                        {{-- Top Accent Line --}}
                        <div class="absolute top-0 left-0 right-0 h-1.5" style="background-color: {{ $wallet->color }};"></div>

                        <div>
                            {{-- Header Card --}}
                            <div class="flex items-start justify-between mb-4 pt-1">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 wallet-brand-tint" style="background-color: {{ $wallet->color }}15; color: {{ $wallet->color }};">
                                        @if ($wallet->type === 'bank')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.5m-15 10.5V10.5M3 21h18"/></svg>
                                        @elseif ($wallet->type === 'ewallet')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>
                                        @elseif ($wallet->type === 'investment')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/></svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <h3 class="text-sm font-bold text-gray-900 leading-snug">{{ $wallet->name }}</h3>
                                            @php
                                                $ln = strtolower($wallet->name);
                                                $brand = null;
                                                if (str_contains($ln, 'chase')) $brand = 'CHASE';
                                                elseif (str_contains($ln, 'america') || str_contains($ln, 'bofa')) $brand = 'BofA';
                                                elseif (str_contains($ln, 'wells')) $brand = 'WELLS';
                                                elseif (str_contains($ln, 'citi')) $brand = 'CITI';
                                                elseif (str_contains($ln, 'barclays')) $brand = 'BARCLAYS';
                                                elseif (str_contains($ln, 'hsbc')) $brand = 'HSBC';
                                                elseif (str_contains($ln, 'revolut')) $brand = 'REVOLUT';
                                                elseif (str_contains($ln, 'monzo')) $brand = 'MONZO';
                                                elseif (str_contains($ln, 'n26')) $brand = 'N26';
                                                elseif (str_contains($ln, 'paypal')) $brand = 'PAYPAL';
                                                elseif (str_contains($ln, 'wise')) $brand = 'WISE';
                                                elseif (str_contains($ln, 'apple')) $brand = 'APPLE';
                                                elseif (str_contains($ln, 'google')) $brand = 'G-PAY';
                                                elseif (str_contains($ln, 'stripe')) $brand = 'STRIPE';
                                                elseif (str_contains($ln, 'venmo')) $brand = 'VENMO';
                                                elseif (str_contains($ln, 'cash app')) $brand = 'CASH APP';
                                                elseif (str_contains($ln, 'dbs')) $brand = 'DBS';
                                                elseif (str_contains($ln, 'ocbc')) $brand = 'OCBC';
                                                elseif (str_contains($ln, 'mufg')) $brand = 'MUFG';
                                                elseif (str_contains($ln, 'commonwealth')) $brand = 'CBA';
                                                elseif (str_contains($ln, 'bca')) $brand = 'BCA';
                                                elseif (str_contains($ln, 'mandiri')) $brand = 'MANDIRI';
                                                elseif (str_contains($ln, 'bri')) $brand = 'BRI';
                                                elseif (str_contains($ln, 'bni')) $brand = 'BNI';
                                                elseif (str_contains($ln, 'jago')) $brand = 'JAGO';
                                                elseif (str_contains($ln, 'gopay')) $brand = 'GOPAY';
                                                elseif (str_contains($ln, 'ovo')) $brand = 'OVO';
                                                elseif (str_contains($ln, 'dana')) $brand = 'DANA';
                                                elseif (str_contains($ln, 'shopee')) $brand = 'SHOPEE';
                                            @endphp
                                            @if ($brand)
                                                <span class="text-[9px] font-black px-1.5 py-0.5 rounded tracking-wider uppercase font-mono shadow-2xs border border-gray-100 wallet-brand-tint" style="background-color: {{ $wallet->color }}18; color: {{ $wallet->color }};">
                                                    {{ $brand }}
                                                </span>
                                            @endif
                                            <span class="text-[9px] font-black px-1.5 py-0.5 rounded tracking-wider uppercase font-mono bg-gray-100 text-gray-700 border border-gray-200">
                                                {{ $wallet->currency ?? auth()->user()->currency ?? 'IDR' }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-gray-400 font-mono mt-0.5">
                                            {{ $wallet->account_number ?: ucfirst($wallet->type) }}
                                        </p>
                                    </div>
                                </div>

                                @if ($wallet->is_primary)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-50 text-[#00c078] border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#00c078]"></span>
                                        {{ __('Primary') }}
                                    </span>
                                @endif
                            </div>

                            {{-- Saldo Card --}}
                            <div class="mb-4">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Current Balance') }}</span>
                                <div class="text-2xl font-black {{ $isNeg ? 'text-red-500' : 'text-gray-900' }} tracking-tight mt-0.5">
                                    {{ $wallet->formatBalance($balance) }}
                                </div>
                                @if (strtoupper($wallet->currency ?? auth()->user()->currency ?? 'IDR') !== strtoupper(auth()->user()->currency ?? 'IDR'))
                                    <div class="mt-1 flex items-center gap-1.5 text-[11px] font-semibold text-emerald-600">
                                        <span>≈ {{ auth()->user()->formatCurrency($wallet->converted_balance) }}</span>
                                        <span class="text-[10px] font-normal text-gray-400">({{ $currencyService->formatRateSummary($wallet->currency, auth()->user()->currency) }})</span>
                                    </div>
                                @endif
                                @if ($wallet->notes)
                                    <p class="text-[11px] text-gray-400 mt-1 truncate">{{ $wallet->notes }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Card Actions Footer --}}
                        <div class="pt-3.5 border-t border-gray-100 flex items-center justify-between gap-2 text-xs">
                            <button
                                type="button"
                                @click="openTransferFrom({{ $wallet->id }})"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 hover:bg-emerald-50 text-gray-700 hover:text-emerald-700 font-semibold transition-colors cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                                <span>{{ __('Transfer Funds') }}</span>
                            </button>

                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    @click="openEdit({{ json_encode($wallet) }})"
                                    class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
                                    title="{{ __('Edit Wallet / Account') }}"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                </button>

                                <form method="POST" action="{{ route('wallets.destroy', $wallet) }}" onsubmit="return confirm('{{ __('Delete this wallet/account? Previous transaction history will be preserved.') }}')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                                        title="{{ __('Delete Account') }}"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Recent Internal Transfers Table --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-gray-900">{{ __('Internal Transfer History') }}</h3>
                    <p class="text-[11px] text-gray-400 mt-0.5">{{ __('Internal fund movements between wallets without impacting expense cash flow') }}</p>
                </div>
                <button
                    type="button"
                    @click="transferModalOpen = true"
                    class="text-xs font-bold text-[#00c078] hover:underline flex items-center gap-1 cursor-pointer"
                >
                    <span>{{ __('+ New Transfer') }}</span>
                </button>
            </div>

            @if ($recentTransfers->isNotEmpty())
                <div class="divide-y divide-gray-100">
                    @foreach ($recentTransfers as $tx)
                        <div class="p-4 sm:p-5 flex items-center justify-between gap-4 hover:bg-gray-50/70 transition-colors">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-sm font-bold text-gray-900 truncate">
                                        {{ $tx->wallet->name ?? __('Source Wallet') }}
                                        <span class="text-gray-400 font-normal">→</span>
                                        {{ $tx->destinationWallet->name ?? __('Destination Wallet') }}
                                    </h4>
                                    <p class="text-[11px] text-gray-500 mt-0.5">
                                        {{ $tx->transaction_date ? format_user_date($tx->transaction_date) : __('Today') }}
                                        @if ($tx->notes)
                                            &bull; {{ $tx->notes }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                @if ($tx->destination_amount && $tx->destination_amount != $tx->amount && $tx->destinationWallet)
                                    <div class="text-sm font-black text-purple-700 flex items-center justify-end gap-1">
                                        <span>{{ $tx->wallet ? $tx->wallet->formatBalance($tx->amount) : format_currency($tx->amount) }}</span>
                                        <span class="text-xs text-gray-400 font-normal">→</span>
                                        <span>{{ $tx->destinationWallet->formatBalance($tx->destination_amount) }}</span>
                                    </div>
                                    <span class="text-[10px] font-mono text-purple-600 font-semibold block">1 {{ $tx->currency }} ≈ {{ round($tx->exchange_rate, 4) }} {{ $tx->destinationWallet->currency }}</span>
                                @else
                                    <div class="text-sm font-black text-purple-700">
                                        {{ $tx->wallet ? $tx->wallet->formatBalance($tx->amount) : format_currency($tx->amount) }}
                                    </div>
                                    <span class="text-[10px] uppercase font-bold text-gray-400">{{ __('Internal Transfer') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-10 text-center text-gray-400 text-xs">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                    <span>{{ __('No internal transfers recorded yet.') }}</span>
                </div>
            @endif
        </div>

        {{-- ================= MODAL TAMBAH REKENING ================= --}}
        <div
            x-show="createModalOpen"
            style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-create-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity" @click="createModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div
                    class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl border border-gray-100 transition-all text-left"
                    @click.stop
                >
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-[#00c078] flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900" id="modal-create-title">{{ __('Add Wallet / Account') }}</h3>
                                <p class="text-[11px] text-gray-500">{{ __('Link a bank account, digital wallet, or physical cash') }}</p>
                            </div>
                        </div>
                        <button @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Quick Presets Selector --}}
                    <div class="mb-4 p-3.5 rounded-xl bg-gray-50/80 border border-gray-100">
                        <div class="flex items-center justify-between gap-2 mb-2 flex-wrap">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">
                                {{ __('Quick Institution & Wallet Presets') }}
                            </span>
                            <div class="inline-flex gap-1 bg-white p-0.5 rounded-lg border border-gray-200 text-[10px] font-semibold">
                                <button type="button" @click="presetFilter = 'all'" :class="presetFilter === 'all' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-800'" class="px-2 py-0.5 rounded-md transition cursor-pointer">{{ __('All') }}</button>
                                <button type="button" @click="presetFilter = 'global'" :class="presetFilter === 'global' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-800'" class="px-2 py-0.5 rounded-md transition cursor-pointer">{{ __('Global Banks') }}</button>
                                <button type="button" @click="presetFilter = 'ewallet'" :class="presetFilter === 'ewallet' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-800'" class="px-2 py-0.5 rounded-md transition cursor-pointer">{{ __('E-Wallets') }}</button>
                                <button type="button" @click="presetFilter = 'id'" :class="presetFilter === 'id' ? 'bg-gray-900 text-white' : 'text-gray-500 hover:text-gray-800'" class="px-2 py-0.5 rounded-md transition cursor-pointer">{{ __('Indonesia') }}</button>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 flex-wrap max-h-28 overflow-y-auto pr-1">
                            <template x-for="p in presets.filter(item => presetFilter === 'all' || item.category === presetFilter)" :key="p.name">
                                <button
                                    type="button"
                                    @click="applyPreset(p)"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-white border border-gray-200 hover:border-emerald-400 hover:bg-emerald-50/40 text-gray-700 transition cursor-pointer shadow-2xs"
                                    :title="p.notes"
                                >
                                    <span class="w-2 h-2 rounded-full shrink-0" :style="'background-color: ' + p.color"></span>
                                    <span x-text="p.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('wallets.store') }}" class="space-y-4">
                        @csrf

                        {{-- Nama, Tipe & Mata Uang --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                            <div class="sm:col-span-1">
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Account / Bank Name') }} *</label>
                                <input
                                    type="text"
                                    name="name"
                                    required
                                    x-model="newWallet.name"
                                    placeholder="e.g. Chase, BCA"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Account Type') }} *</label>
                                <select
                                    name="type"
                                    x-model="newWallet.type"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                                    <option value="bank">{{ __('Bank Accounts') }}</option>
                                    <option value="ewallet">{{ __('Digital Wallets') }}</option>
                                    <option value="cash">{{ __('Cash & Physical') }}</option>
                                    <option value="investment">{{ __('Investment / Mutual Fund') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Account Currency') }} *</label>
                                <select
                                    name="currency"
                                    x-model="newWallet.currency"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                                    <option value="IDR">IDR (Rp)</option>
                                    <option value="USD">USD ($)</option>
                                    <option value="EUR">EUR (€)</option>
                                    <option value="GBP">GBP (£)</option>
                                    <option value="SGD">SGD (S$)</option>
                                    <option value="AUD">AUD (A$)</option>
                                    <option value="JPY">JPY (¥)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Saldo Awal & Nomor Rekening --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Initial Balance') }} (@currencySymbol) *</label>
                                <input
                                    type="number"
                                    name="initial_balance"
                                    required
                                    min="0"
                                    step="any"
                                    x-model="newWallet.initial_balance"
                                    placeholder="0"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Account Number / Phone') }}</label>
                                <input
                                    type="text"
                                    name="account_number"
                                    x-model="newWallet.account_number"
                                    placeholder="•••• 1234, IBAN, or user@email.com"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                            </div>
                        </div>

                        {{-- Warna Aksen & Opsi Utama --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 items-center">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Card Accent Color') }}</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        name="color"
                                        x-model="newWallet.color"
                                        class="w-10 h-10 rounded-xl cursor-pointer border border-gray-200 p-0.5"
                                    >
                                    <span class="text-xs text-gray-500">{{ __('Choose theme color') }}</span>
                                </div>
                            </div>
                            <div class="pt-4">
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="is_primary"
                                        value="1"
                                        x-model="newWallet.is_primary"
                                        class="w-4 h-4 rounded border-gray-300 text-[#00c078] focus:ring-[#00c078]"
                                    >
                                    <span class="text-xs font-semibold text-gray-700">{{ __('Set as Primary Account') }}</span>
                                </label>
                            </div>
                        </div>

                        {{-- Catatan --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Notes (Optional)') }}</label>
                            <input
                                type="text"
                                name="notes"
                                x-model="newWallet.notes"
                                placeholder="e.g. Salary direct deposit, business payouts, travel cash"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                            >
                        </div>

                        <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                            <button
                                type="button"
                                @click="createModalOpen = false"
                                class="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition cursor-pointer"
                            >
                                {{ __('Cancel') }}
                            </button>
                            <button
                                type="submit"
                                class="px-5 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-semibold shadow-sm transition cursor-pointer"
                            >
                                {{ __('Save Account') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ================= MODAL EDIT REKENING ================= --}}
        <div
            x-show="editModalOpen"
            style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-edit-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity" @click="editModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div
                    class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl border border-gray-100 transition-all text-left"
                    @click.stop
                >
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900" id="modal-edit-title">{{ __('Edit Wallet / Account') }}</h3>
                                <p class="text-[11px] text-gray-500">{{ __('Update account details or initial balance') }}</p>
                            </div>
                        </div>
                        <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form :action="'{{ url('wallets') }}/' + activeWallet.id" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        {{-- Nama, Tipe & Mata Uang --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                            <div class="sm:col-span-1">
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Account / Bank Name') }} *</label>
                                <input
                                    type="text"
                                    name="name"
                                    required
                                    x-model="activeWallet.name"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Account Type') }} *</label>
                                <select
                                    name="type"
                                    x-model="activeWallet.type"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                                    <option value="bank">{{ __('Bank Accounts') }}</option>
                                    <option value="ewallet">{{ __('Digital Wallets') }}</option>
                                    <option value="cash">{{ __('Cash & Physical') }}</option>
                                    <option value="investment">{{ __('Investment / Mutual Fund') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Account Currency') }} *</label>
                                <select
                                    name="currency"
                                    x-model="activeWallet.currency"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                                    <option value="IDR">IDR (Rp)</option>
                                    <option value="USD">USD ($)</option>
                                    <option value="EUR">EUR (€)</option>
                                    <option value="GBP">GBP (£)</option>
                                    <option value="SGD">SGD (S$)</option>
                                    <option value="AUD">AUD (A$)</option>
                                    <option value="JPY">JPY (¥)</option>
                                </select>
                            </div>
                        </div>

                        {{-- Saldo Awal & Nomor Rekening --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Initial Balance') }} (@currencySymbol) *</label>
                                <input
                                    type="number"
                                    name="initial_balance"
                                    required
                                    min="0"
                                    step="any"
                                    x-model="activeWallet.initial_balance"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Account Number / Phone') }}</label>
                                <input
                                    type="text"
                                    name="account_number"
                                    x-model="activeWallet.account_number"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                            </div>
                        </div>

                        {{-- Warna & Utama --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 items-center">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Card Accent Color') }}</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="color"
                                        name="color"
                                        x-model="activeWallet.color"
                                        class="w-10 h-10 rounded-xl cursor-pointer border border-gray-200 p-0.5"
                                    >
                                    <span class="text-xs text-gray-500">{{ __('Choose theme color') }}</span>
                                </div>
                            </div>
                            <div class="pt-4">
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        name="is_primary"
                                        value="1"
                                        :checked="activeWallet.is_primary"
                                        class="w-4 h-4 rounded border-gray-300 text-[#00c078] focus:ring-[#00c078]"
                                    >
                                    <span class="text-xs font-semibold text-gray-700">{{ __('Set as Primary Account') }}</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Notes (Optional)') }}</label>
                            <input
                                type="text"
                                name="notes"
                                x-model="activeWallet.notes"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                            >
                        </div>

                        <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                            <button
                                type="button"
                                @click="editModalOpen = false"
                                class="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition cursor-pointer"
                            >
                                {{ __('Cancel') }}
                            </button>
                            <button
                                type="submit"
                                class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition cursor-pointer"
                            >
                                {{ __('Update Account') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ================= MODAL TRANSFER ANTAR REKENING ================= --}}
        <div
            x-show="transferModalOpen"
            style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-transfer-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity" @click="transferModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div
                    class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl border border-gray-100 transition-all text-left"
                    @click.stop
                >
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900" id="modal-transfer-title">{{ __('Transfer Between Wallets') }}</h3>
                                <p class="text-[11px] text-gray-500">{{ __('Instantly move funds from one account to another') }}</p>
                            </div>
                        </div>
                        <button @click="transferModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('wallets.transfer') }}" class="space-y-4">
                        @csrf

                        {{-- Rekening Asal & Tujuan --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('From Account (Source)') }} *</label>
                                <select
                                    name="source_wallet_id"
                                    required
                                    x-model="transferData.source_wallet_id"
                                    @change="calculateTransfer()"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                                    <option value="">-- {{ __('Select Source Account') }} --</option>
                                    @foreach ($wallets as $w)
                                        <option value="{{ $w->id }}">
                                            {{ $w->name }} ({{ $w->formatBalance() }}) &bull; {{ $w->currency }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('To Account (Destination)') }} *</label>
                                <select
                                    name="destination_wallet_id"
                                    required
                                    x-model="transferData.destination_wallet_id"
                                    @change="calculateTransfer()"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                                    <option value="">-- {{ __('Select Destination Account') }} --</option>
                                    @foreach ($wallets as $w)
                                        <option value="{{ $w->id }}">
                                            {{ $w->name }} ({{ $w->formatBalance() }}) &bull; {{ $w->currency }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Cross-Currency Exchange Box --}}
                        <div x-show="transferData.isCrossCurrency" style="display: none;" class="p-3.5 rounded-xl bg-purple-50/80 border border-purple-200/80 space-y-2.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-purple-900 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                                    {{ __('Cross-Currency Exchange') }}
                                </span>
                                <span class="font-mono text-[11px] text-purple-700 font-bold px-2 py-0.5 rounded bg-white border border-purple-200" x-text="transferData.rateText"></span>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-purple-900 mb-1">
                                    {{ __('Recipient Receives') }} (<span x-text="getDestCurrencySymbol()"></span>)
                                </label>
                                <input
                                    type="number"
                                    step="any"
                                    name="destination_amount"
                                    x-model="transferData.destination_amount"
                                    class="w-full px-3.5 py-2 rounded-xl bg-white border border-purple-200 text-sm font-black text-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-400"
                                >
                                <p class="text-[10px] text-purple-600 mt-1">
                                    {{ __('Auto-calculated via live market rate. You can fine-tune if there are transfer fees.') }}
                                </p>
                            </div>
                        </div>

                        {{-- Nominal & Tanggal --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                    {{ __('Transfer Amount') }} (<span x-text="getSourceCurrencySymbol()"></span>) *
                                </label>
                                <input
                                    type="number"
                                    name="amount"
                                    required
                                    min="0.01"
                                    step="any"
                                    x-model="transferData.amount"
                                    @input="calculateTransfer()"
                                    placeholder="e.g. 100"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Transfer Date') }}</label>
                                <input
                                    type="date"
                                    name="transaction_date"
                                    x-model="transferData.transaction_date"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Transfer Notes (Optional)') }}</label>
                            <input
                                type="text"
                                name="notes"
                                placeholder="e.g. Move to savings, ATM withdrawal"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                            >
                        </div>

                        <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100">
                            <button
                                type="button"
                                @click="transferModalOpen = false"
                                class="px-4 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition cursor-pointer"
                            >
                                {{ __('Cancel') }}
                            </button>
                            <button
                                type="submit"
                                class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold shadow-sm transition cursor-pointer"
                            >
                                {{ __('Execute Transfer') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>

