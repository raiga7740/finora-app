<x-app-layout>
    <div x-data="{
        activeTab: 'all',
        showCreateModal: false,
        showPayModal: false,
        showEditModal: false,
        showSplitModal: {{ request()->boolean('split') ? 'true' : 'false' }},
        expandedHistory: {},
        createType: 'payable',
        splitCopied: false,
        splitBill: {
            title: '',
            mode: 'equal',
            subtotal: 0,
            mySubtotal: 0,
            taxPercent: 10,
            servicePercent: 5,
            discountAmount: 0,
            includeSelf: true,
            dueDate: '',
            paymentInfo: '',
            participants: [
                { name: '', contact: '', subtotal: 0, items_note: '' },
                { name: '', contact: '', subtotal: 0, items_note: '' }
            ]
        },
        addSplitParticipant() {
            this.splitBill.participants.push({ name: '', contact: '', subtotal: 0, items_note: '' });
        },
        removeSplitParticipant(idx) {
            if (this.splitBill.participants.length > 1) {
                this.splitBill.participants.splice(idx, 1);
            }
        },
        getSplitBaseSubtotal() {
            if (this.splitBill.mode === 'equal') {
                return Math.max(0, parseFloat(this.splitBill.subtotal) || 0);
            }
            const friendsSum = this.splitBill.participants.reduce((acc, p) => acc + (parseFloat(p.subtotal) || 0), 0);
            const mySum = this.splitBill.includeSelf ? (parseFloat(this.splitBill.mySubtotal) || 0) : 0;
            return Math.max(0, friendsSum + mySum);
        },
        getSplitGrandTotal() {
            const base = this.getSplitBaseSubtotal();
            const tax = base * ((parseFloat(this.splitBill.taxPercent) || 0) / 100);
            const srv = base * ((parseFloat(this.splitBill.servicePercent) || 0) / 100);
            const disc = parseFloat(this.splitBill.discountAmount) || 0;
            return Math.max(0, Math.round(base + tax + srv - disc));
        },
        getParticipantFinalShare(p) {
            const grand = this.getSplitGrandTotal();
            if (grand <= 0) return 0;
            if (this.splitBill.mode === 'equal') {
                const count = this.splitBill.participants.length + (this.splitBill.includeSelf ? 1 : 0);
                return count > 0 ? Math.round(grand / count) : 0;
            }
            const base = this.getSplitBaseSubtotal();
            if (base <= 0) return 0;
            const pSub = parseFloat(p.subtotal) || 0;
            return Math.round((pSub / base) * grand);
        },
        formatSplitMoney(amount) {
            const sym = '{{ auth()->user()->currency_symbol }}';
            const num = Math.round(parseFloat(amount) || 0);
            return sym + num.toLocaleString('id-ID');
        },
        buildGroupSummaryText() {
            const title = this.splitBill.title || '{{ __('Split Bill') }}';
            let lines = [];
            lines.push('*RINCIAN SPLIT BILL: ' + title.toUpperCase() + '*');
            lines.push('Total Tagihan: ' + this.formatSplitMoney(this.getSplitGrandTotal()) + ' (Pajak ' + (this.splitBill.taxPercent || 0) + '% + Service ' + (this.splitBill.servicePercent || 0) + '%)');
            lines.push('--------------------------------');
            this.splitBill.participants.forEach((p, i) => {
                const pName = p.name || ('Teman ' + (i + 1));
                const share = this.formatSplitMoney(this.getParticipantFinalShare(p));
                const menu = p.items_note ? ' (' + p.items_note + ')' : '';
                lines.push((i + 1) + '. ' + pName + menu + ': *' + share + '*');
            });
            if (this.splitBill.paymentInfo) {
                lines.push('--------------------------------');
                lines.push('Transfer ke: *' + this.splitBill.paymentInfo + '*');
            }
            lines.push('Terima kasih!');
            return lines.join('\n');
        },
        copySplitSummary() {
            navigator.clipboard.writeText(this.buildGroupSummaryText());
            this.splitCopied = true;
            setTimeout(() => { this.splitCopied = false; }, 2500);
        },
        getParticipantWaUrl(p) {
            const digits = (p.contact || '').replace(/[^0-9]/g, '');
            const norm = digits.startsWith('0') ? ('62' + digits.slice(1)) : digits;
            const title = this.splitBill.title || 'Split Bill';
            const share = this.formatSplitMoney(this.getParticipantFinalShare(p));
            const menu = p.items_note ? ' (' + p.items_note + ')' : '';
            const pay = this.splitBill.paymentInfo ? ('\nTransfer ke: ' + this.splitBill.paymentInfo) : '';
            const msg = 'Halo ' + (p.name || 'Teman') + ', berikut rincian patungan *' + title + '*' + menu + ' sebesar *' + share + '* (sudah termasuk pajak/service proporsional).' + pay + '\nTerima kasih!';
            return norm ? ('https://wa.me/' + norm + '?text=' + encodeURIComponent(msg)) : ('https://wa.me/?text=' + encodeURIComponent(msg));
        },
        payTarget: {
            id: null,
            title: '',
            person_name: '',
            type: 'payable',
            remaining_amount: 0,
            formatted_remaining: ''
        },
        editTarget: {
            id: null,
            type: 'payable',
            person_name: '',
            person_contact: '',
            title: '',
            total_amount: '',
            due_date: '',
            status: 'active',
            notes: ''
        },
        openPayModal(debt) {
            this.payTarget = debt;
            this.showPayModal = true;
        },
        openEditModal(debt) {
            this.editTarget = debt;
            this.showEditModal = true;
        },
        toggleHistory(id) {
            this.expandedHistory[id] = !this.expandedHistory[id];
        }
    }" class="space-y-6">

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-[11px] font-bold tracking-wider uppercase text-[#00c078] mb-1">
                    <span>{{ __('OBLIGATION & RECEIVABLE LEDGER') }}</span>
                    <span class="text-gray-300">•</span>
                    <span class="text-gray-500 font-medium capitalize">{{ __('Installment & Settlement Tracking') }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">{{ __('Debts & Loans Tracker') }}</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-0.5">{{ __('Monitor money you owe (Payable) and money owed to you (Receivable) with partial installment logs.') }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button"
                        @click="showSplitModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#00c078]/30 bg-[#e6f9f1] hover:bg-[#d4f5e6] text-[#00995f] text-xs font-bold transition shadow-sm cursor-pointer">
                    <svg class="w-4 h-4 text-[#00c078]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ __('Split Bill (WhatsApp)') }}
                </button>

                <button type="button"
                        @click="createType = 'receivable'; showCreateModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 text-xs font-bold transition shadow-sm cursor-pointer">
                    <svg class="w-4 h-4 text-[#00c078]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Add Receivable (Owed to Me)') }}
                </button>

                <button type="button"
                        @click="createType = 'payable'; showCreateModal = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-bold transition shadow-sm cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    {{ __('Add Debt (I Owe)') }}
                </button>
            </div>
        </div>

        <!-- Flash Status -->
        @if (session('status'))
            <div class="rounded-2xl bg-[#e6f9f1] border border-[#00c078]/20 px-4 py-3 flex items-center justify-between text-xs font-semibold text-[#00995f]">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl bg-red-50 border border-red-200 px-4 py-3 text-xs font-semibold text-red-600 space-y-1">
                @foreach ($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- 4 KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Payable (I Owe) -->
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center text-red-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                        </svg>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-red-50 text-red-600">
                        {{ $activePayableCount }} {{ __('Active') }}
                    </span>
                </div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('TOTAL I OWE (PAYABLE)') }}</p>
                <p class="text-xl sm:text-2xl font-extrabold text-gray-900 mt-1">
                    {{ auth()->user()->formatCurrency($totalPayableRemaining) }}
                </p>
                <p class="text-[11px] text-gray-400 mt-2">{{ __('Remaining unpaid obligations') }}</p>
            </div>

            <!-- Total Receivable (Owed to Me) -->
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-9 h-9 rounded-xl bg-[#e6f9f1] flex items-center justify-center text-[#00c078]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                        </svg>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#e6f9f1] text-[#00995f]">
                        {{ $activeReceivableCount }} {{ __('Active') }}
                    </span>
                </div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('TOTAL OWED TO ME (RECEIVABLE)') }}</p>
                <p class="text-xl sm:text-2xl font-extrabold text-[#00c078] mt-1">
                    {{ auth()->user()->formatCurrency($totalReceivableRemaining) }}
                </p>
                <p class="text-[11px] text-gray-400 mt-2">{{ __('Pending incoming repayments') }}</p>
            </div>

            <!-- Net Debt Position -->
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $netDebtPosition >= 0 ? 'bg-[#e6f9f1] text-[#00995f]' : 'bg-amber-50 text-amber-700' }}">
                        {{ $netDebtPosition >= 0 ? __('Net Positive') : __('Net Liability') }}
                    </span>
                </div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('NET DEBT POSITION') }}</p>
                <p class="text-xl sm:text-2xl font-extrabold {{ $netDebtPosition >= 0 ? 'text-gray-900' : 'text-red-600' }} mt-1">
                    {{ auth()->user()->formatCurrency($netDebtPosition, true) }}
                </p>
                <p class="text-[11px] text-gray-400 mt-2">{{ __('Receivables minus Payables') }}</p>
            </div>

            <!-- Due & Settled Status -->
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-9 h-9 rounded-xl {{ $overdueCount > 0 ? 'bg-amber-50 text-amber-600' : 'bg-gray-100 text-gray-600' }} flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700">
                        {{ $settledCount }} {{ __('Settled') }}
                    </span>
                </div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('OVERDUE OBLIGATIONS') }}</p>
                <p class="text-xl sm:text-2xl font-extrabold {{ $overdueCount > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">
                    {{ $overdueCount }} {{ __('Records') }}
                </p>
                <p class="text-[11px] text-gray-400 mt-2">{{ __('Past due date & still active') }}</p>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-2 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex flex-wrap items-center gap-1.5">
                <button type="button"
                        @click="activeTab = 'all'"
                        :class="activeTab === 'all' ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                    {{ __('All Records') }} ({{ $debts->count() }})
                </button>
                <button type="button"
                        @click="activeTab = 'payable'"
                        :class="activeTab === 'payable' ? 'bg-red-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                    {{ __('I Owe / Payable') }} ({{ $activePayableCount }})
                </button>
                <button type="button"
                        @click="activeTab = 'receivable'"
                        :class="activeTab === 'receivable' ? 'bg-[#00c078] text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                    {{ __('Owed to Me / Receivable') }} ({{ $activeReceivableCount }})
                </button>
                <button type="button"
                        @click="activeTab = 'settled'"
                        :class="activeTab === 'settled' ? 'bg-gray-900 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-50'"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                    {{ __('Settled / Paid') }} ({{ $settledCount }})
                </button>
            </div>
        </div>

        <!-- Debts & Receivables List -->
        @if ($debts->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center shadow-sm">
                <div class="w-14 h-14 rounded-2xl bg-[#e6f9f1] text-[#00c078] flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900">{{ __('No Debt or Receivable Records Yet') }}</h3>
                <p class="text-xs text-gray-500 max-w-md mx-auto mt-1">
                    {{ __('Start tracking personal loans, split bills, or installments owed by or to others. Every payment can automatically sync with your wallet balance.') }}
                </p>
                <div class="mt-5 flex items-center justify-center gap-3">
                    <button type="button"
                            @click="createType = 'payable'; showCreateModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-bold transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ __('Add First Record') }}
                    </button>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4">
                @foreach ($debts as $debt)
                    @php
                        $isPayable = $debt->type === 'payable';
                        $isPaid = $debt->status === 'paid';
                        $progress = $debt->progress_percentage;
                        $remaining = $debt->remaining_amount;
                        $waUrl = $debt->whatsapp_reminder_url;
                    @endphp
                    <div x-show="
                            activeTab === 'all' ||
                            (activeTab === 'payable' && '{{ $debt->type }}' === 'payable' && '{{ $debt->status }}' === 'active') ||
                            (activeTab === 'receivable' && '{{ $debt->type }}' === 'receivable' && '{{ $debt->status }}' === 'active') ||
                            (activeTab === 'settled' && '{{ $debt->status }}' === 'paid')
                         "
                         class="bg-white rounded-2xl border {{ $debt->is_overdue ? 'border-amber-300' : 'border-gray-100' }} p-5 shadow-sm transition hover:border-gray-200">

                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            <!-- Left Info -->
                            <div class="space-y-2 flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($isPayable)
                                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold uppercase tracking-wider bg-red-50 text-red-600">
                                            {{ __('I OWE (PAYABLE)') }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold uppercase tracking-wider bg-[#e6f9f1] text-[#00995f]">
                                            {{ __('OWED TO ME (RECEIVABLE)') }}
                                        </span>
                                    @endif

                                    @if ($isPaid)
                                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold uppercase tracking-wider bg-gray-900 text-white">
                                            {{ __('SETTLED / PAID') }}
                                        </span>
                                    @elseif ($debt->is_overdue)
                                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800">
                                            {{ __('OVERDUE') }}
                                        </span>
                                    @endif

                                    @if ($debt->due_date)
                                        <span class="text-[11px] font-medium text-gray-500 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ __('Due') }}: {{ auth()->user()->formatUserDate($debt->due_date) }}
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-baseline gap-2 flex-wrap">
                                    <h3 class="text-base font-extrabold text-gray-900">{{ $debt->title }}</h3>
                                    <span class="text-xs font-semibold text-gray-500">
                                        — {{ $isPayable ? __('Lender') : __('Borrower') }}: <strong class="text-gray-800">{{ $debt->person_name }}</strong>
                                    </span>
                                    @if ($debt->person_contact)
                                        <span class="text-[11px] text-gray-400 font-mono">({{ $debt->person_contact }})</span>
                                    @endif
                                </div>

                                @if ($debt->notes)
                                    <p class="text-xs text-gray-500">{{ $debt->notes }}</p>
                                @endif
                            </div>

                            <!-- Right Amounts & Progress -->
                            <div class="lg:w-80 space-y-2 shrink-0">
                                <div class="flex items-baseline justify-between">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">
                                        {{ $isPaid ? __('Fully Settled') : __('Remaining') }}
                                    </span>
                                    <span class="text-lg font-extrabold {{ $isPaid ? 'text-gray-400 line-through' : ($isPayable ? 'text-red-600' : 'text-[#00c078]') }}">
                                        {{ auth()->user()->formatCurrency($isPaid ? $debt->total_amount : $remaining) }}
                                    </span>
                                </div>

                                <!-- Progress Bar -->
                                <div class="w-full h-2 rounded-full bg-gray-100 overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-300 {{ $isPayable ? 'bg-red-500' : 'bg-[#00c078]' }}"
                                         style="width: {{ $progress }}%"></div>
                                </div>

                                <div class="flex items-center justify-between text-[11px] font-medium text-gray-500">
                                    <span>{{ __('Paid') }}: {{ auth()->user()->formatCurrency($debt->paid_amount) }} ({{ $progress }}%)</span>
                                    <span>{{ __('Total') }}: {{ auth()->user()->formatCurrency($debt->total_amount) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Footer -->
                        <div class="mt-4 pt-3.5 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2">
                            <div class="flex flex-wrap items-center gap-2">
                                @if (! $isPaid)
                                    <button type="button"
                                            @click="openPayModal({
                                                id: {{ $debt->id }},
                                                title: @js($debt->title),
                                                person_name: @js($debt->person_name),
                                                type: @js($debt->type),
                                                remaining_amount: {{ $remaining }},
                                                formatted_remaining: @js(auth()->user()->formatCurrency($remaining))
                                            })"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-bold transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        {{ $isPayable ? __('Record Repayment') : __('Record Collection') }}
                                    </button>
                                @endif

                                @if ($waUrl && ! $isPaid)
                                    <a href="{{ $waUrl }}"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-[#00c078]/30 bg-[#e6f9f1] hover:bg-[#d4f5e6] text-[#00995f] text-xs font-bold transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                        </svg>
                                        {{ __('WhatsApp Reminder') }}
                                    </a>
                                @endif

                                <button type="button"
                                        @click="toggleHistory({{ $debt->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-semibold transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ __('Installment History') }} ({{ $debt->payments->count() }})
                                </button>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button"
                                        @click="openEditModal({
                                            id: {{ $debt->id }},
                                            type: @js($debt->type),
                                            person_name: @js($debt->person_name),
                                            person_contact: @js($debt->person_contact ?? ''),
                                            title: @js($debt->title),
                                            total_amount: {{ (float) $debt->total_amount }},
                                            due_date: @js($debt->due_date ? $debt->due_date->format('Y-m-d') : ''),
                                            status: @js($debt->status),
                                            notes: @js($debt->notes ?? '')
                                        })"
                                        class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 transition cursor-pointer">
                                    {{ __('Edit') }}
                                </button>

                                <form method="POST"
                                      action="{{ route('debts.destroy', $debt) }}"
                                      onsubmit="return confirm('{{ __('Are you sure you want to delete this record?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-red-600 hover:bg-red-50 transition cursor-pointer">
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Expandable Installment History -->
                        <div x-show="expandedHistory[{{ $debt->id }}]"
                             x-cloak
                             class="mt-3 pt-3 border-t border-dashed border-gray-200 bg-gray-50/70 rounded-xl p-3">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2">
                                {{ __('Payment & Installment Log') }}
                            </p>
                            @if ($debt->payments->isEmpty())
                                <p class="text-xs text-gray-500">{{ __('No installment payments recorded yet.') }}</p>
                            @else
                                <div class="space-y-1.5">
                                    @foreach ($debt->payments as $payment)
                                        <div class="flex items-center justify-between text-xs bg-white px-3 py-2 rounded-lg border border-gray-100">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-gray-900">{{ auth()->user()->formatCurrency($payment->amount) }}</span>
                                                <span class="text-gray-400">•</span>
                                                <span class="text-gray-600">{{ auth()->user()->formatUserDate($payment->payment_date) }}</span>
                                                @if ($payment->wallet)
                                                    <span class="px-2 py-0.5 rounded bg-gray-100 text-[10px] font-semibold text-gray-600">
                                                        {{ $payment->wallet->name }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if ($payment->notes)
                                                <span class="text-gray-500 italic">{{ $payment->notes }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- MODAL 1: Create Debt / Receivable -->
        <div x-show="showCreateModal"
             x-cloak
             style="background-color: rgba(0, 0, 0, 0.65);"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            <div @click.outside="showCreateModal = false"
                 class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-lg overflow-hidden my-8">
                <div class="bg-[#00c078] px-6 py-4 flex items-center justify-between text-white">
                    <div>
                        <h3 class="text-base font-extrabold" x-text="createType === 'payable' ? '{{ __('Add Debt (I Owe)') }}' : '{{ __('Add Receivable (Owed to Me)') }}'"></h3>
                        <p class="text-xs text-white/90">{{ __('Track principal amount, due date, and optional wallet sync.') }}</p>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="text-white/80 hover:text-white cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('debts.store') }}" class="p-6 space-y-4">
                    @csrf
                    <!-- Type Toggle -->
                    <div class="grid grid-cols-2 gap-2 p-1 bg-gray-100 rounded-xl">
                        <button type="button"
                                @click="createType = 'payable'"
                                :class="createType === 'payable' ? 'bg-white text-red-600 shadow-sm' : 'text-gray-600'"
                                class="py-2 rounded-lg text-xs font-bold transition cursor-pointer">
                            {{ __('I Owe (Payable)') }}
                        </button>
                        <button type="button"
                                @click="createType = 'receivable'"
                                :class="createType === 'receivable' ? 'bg-white text-[#00c078] shadow-sm' : 'text-gray-600'"
                                class="py-2 rounded-lg text-xs font-bold transition cursor-pointer">
                            {{ __('Owed to Me (Receivable)') }}
                        </button>
                    </div>
                    <input type="hidden" name="type" :value="createType">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1" x-text="createType === 'payable' ? '{{ __('Lender Name') }}' : '{{ __('Borrower Name') }}'"></label>
                            <input type="text" name="person_name" required placeholder="{{ __('e.g. Budi Santoso') }}"
                                   class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('WhatsApp / Phone (Optional)') }}</label>
                            <input type="text" name="person_contact" placeholder="0812xxxxxxxx"
                                   class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Title / Description') }}</label>
                        <input type="text" name="title" required placeholder="{{ __('e.g. Laptop Installment / Emergency Loan') }}"
                               class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Total Amount') }} ({{ auth()->user()->currency_symbol }})</label>
                            <input type="number" step="0.01" min="0.01" name="total_amount" required placeholder="0"
                                   class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Already Paid (Optional)') }}</label>
                            <input type="number" step="0.01" min="0" name="paid_amount" value="0"
                                   class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Transaction Date') }}</label>
                            <input type="date" name="start_date" value="{{ now()->toDateString() }}"
                                   class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Due Date (Optional)') }}</label>
                            <input type="date" name="due_date"
                                   class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Sync Initial Loan with Wallet (Optional)') }}</label>
                        <select name="wallet_id" class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                            <option value="">{{ __('Do not alter wallet balance (Record only)') }}</option>
                            @foreach ($wallets as $wallet)
                                <option value="{{ $wallet->id }}">{{ $wallet->name }} ({{ $wallet->currency }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Notes (Optional)') }}</label>
                        <textarea name="notes" rows="2" placeholder="{{ __('Bank account number or agreement details...') }}"
                                  class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="showCreateModal = false"
                                class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50 cursor-pointer">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-bold transition cursor-pointer">
                            {{ __('Save Record') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: Record Installment / Payment -->
        <div x-show="showPayModal"
             x-cloak
             style="background-color: rgba(0, 0, 0, 0.65);"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            <div @click.outside="showPayModal = false"
                 class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md overflow-hidden my-8">
                <div class="bg-[#00c078] px-6 py-4 flex items-center justify-between text-white">
                    <div>
                        <h3 class="text-base font-extrabold">{{ __('Record Installment / Payment') }}</h3>
                        <p class="text-xs text-white/90" x-text="payTarget.title + ' — ' + payTarget.person_name"></p>
                    </div>
                    <button type="button" @click="showPayModal = false" class="text-white/80 hover:text-white cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="'{{ url('/debts') }}/' + payTarget.id + '/pay'" class="p-6 space-y-4">
                    @csrf
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500">{{ __('Remaining Balance') }}</span>
                        <span class="text-sm font-extrabold text-gray-900" x-text="payTarget.formatted_remaining"></span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Payment Amount') }} ({{ auth()->user()->currency_symbol }})</label>
                        <input type="number" step="0.01" min="0.01" name="amount" required :value="payTarget.remaining_amount"
                               class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Payment Date') }}</label>
                        <input type="date" name="payment_date" value="{{ now()->toDateString() }}"
                               class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Sync with Wallet Balance (Optional)') }}</label>
                        <select name="wallet_id" class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                            <option value="">{{ __('Do not alter wallet balance') }}</option>
                            @foreach ($wallets as $wallet)
                                <option value="{{ $wallet->id }}">{{ $wallet->name }} ({{ $wallet->currency }})</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">
                            {{ __('Selecting a wallet will automatically record an Expense (for Payable) or Income (for Receivable) transaction.') }}
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Notes (Optional)') }}</label>
                        <input type="text" name="notes" placeholder="{{ __('e.g. 1st Installment via Bank Transfer') }}"
                               class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="showPayModal = false"
                                class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50 cursor-pointer">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-bold transition cursor-pointer">
                            {{ __('Confirm Payment') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 3: Edit Debt / Receivable -->
        <div x-show="showEditModal"
             x-cloak
             style="background-color: rgba(0, 0, 0, 0.65);"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            <div @click.outside="showEditModal = false"
                 class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-lg overflow-hidden my-8">
                <div class="bg-gray-900 px-6 py-4 flex items-center justify-between text-white">
                    <div>
                        <h3 class="text-base font-extrabold">{{ __('Edit Debt / Receivable Record') }}</h3>
                        <p class="text-xs text-gray-300" x-text="editTarget.title"></p>
                    </div>
                    <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-white cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="'{{ url('/debts') }}/' + editTarget.id" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Record Type') }}</label>
                            <select name="type" x-model="editTarget.type"
                                    class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                                <option value="payable">{{ __('I Owe (Payable)') }}</option>
                                <option value="receivable">{{ __('Owed to Me (Receivable)') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Status') }}</label>
                            <select name="status" x-model="editTarget.status"
                                    class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="paid">{{ __('Settled / Paid') }}</option>
                                <option value="cancelled">{{ __('Cancelled') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Person Name') }}</label>
                            <input type="text" name="person_name" required x-model="editTarget.person_name"
                                   class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('WhatsApp / Contact') }}</label>
                            <input type="text" name="person_contact" x-model="editTarget.person_contact"
                                   class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Title / Description') }}</label>
                        <input type="text" name="title" required x-model="editTarget.title"
                               class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Total Amount') }} ({{ auth()->user()->currency_symbol }})</label>
                            <input type="number" step="0.01" min="0.01" name="total_amount" required x-model="editTarget.total_amount"
                                   class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Due Date') }}</label>
                            <input type="date" name="due_date" x-model="editTarget.due_date"
                                   class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Notes') }}</label>
                        <textarea name="notes" rows="2" x-model="editTarget.notes"
                                  class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="showEditModal = false"
                                class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50 cursor-pointer">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-bold transition cursor-pointer">
                            {{ __('Update Record') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 4: Split Bill WhatsApp Studio -->
        <div x-show="showSplitModal"
             x-cloak
             style="background-color: rgba(0, 0, 0, 0.65);"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            <div @click.outside="showSplitModal = false"
                 class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-3xl overflow-hidden my-8">
                <div class="bg-[#00c078] px-6 py-4 flex items-center justify-between text-white">
                    <div>
                        <h3 class="text-base font-extrabold">{{ __('Split Bill & WhatsApp Calculator') }}</h3>
                        <p class="text-xs text-white/90">{{ __('Calculate proportional tax & service charge, share via WhatsApp, and save directly to Receivables.') }}</p>
                    </div>
                    <button type="button" @click="showSplitModal = false" class="text-white/80 hover:text-white cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('debts.split-bill') }}" class="p-6 space-y-5 max-h-[82vh] overflow-y-auto">
                    @csrf

                    <!-- Split Mode Toggle -->
                    <div class="grid grid-cols-2 gap-2 p-1 bg-gray-100 rounded-xl">
                        <button type="button"
                                @click="splitBill.mode = 'equal'"
                                :class="splitBill.mode === 'equal' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600'"
                                class="py-2 rounded-lg text-xs font-bold transition cursor-pointer">
                            {{ __('Equal Split (Divide Equally)') }}
                        </button>
                        <button type="button"
                                @click="splitBill.mode = 'custom'"
                                :class="splitBill.mode === 'custom' ? 'bg-white text-[#00c078] shadow-sm' : 'text-gray-600'"
                                class="py-2 rounded-lg text-xs font-bold transition cursor-pointer">
                            {{ __('Itemized / Custom Order Split') }}
                        </button>
                    </div>

                    <!-- Event Name, Due Date & Transfer Info -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Place / Event Name') }}</label>
                            <input type="text" name="title" x-model="splitBill.title" required
                                   placeholder="{{ __('e.g. Dinner at Sushi Tei / Futsal Court') }}"
                                   class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">{{ __('Due Date (Optional)') }}</label>
                            <input type="date" name="due_date" x-model="splitBill.dueDate"
                                   class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                        </div>
                    </div>

                    <!-- Bill Numbers: Subtotal, Tax %, Service %, Discount -->
                    <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 space-y-3">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div x-show="splitBill.mode === 'equal'">
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">{{ __('Bill Subtotal') }}</label>
                                <input type="number" step="0.01" min="0" x-model="splitBill.subtotal"
                                       class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                            </div>
                            <div x-show="splitBill.mode === 'custom'">
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">{{ __('My Own Order Subtotal') }}</label>
                                <input type="number" step="0.01" min="0" x-model="splitBill.mySubtotal"
                                       class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">{{ __('Tax / PB1 (%)') }}</label>
                                <input type="number" step="0.1" min="0" x-model="splitBill.taxPercent"
                                       class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">{{ __('Service Charge (%)') }}</label>
                                <input type="number" step="0.1" min="0" x-model="splitBill.servicePercent"
                                       class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">{{ __('Discount / Promo') }}</label>
                                <input type="number" step="0.01" min="0" x-model="splitBill.discountAmount"
                                       class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-gray-200/70">
                            <label class="inline-flex items-center gap-2 text-xs font-semibold text-gray-700 cursor-pointer">
                                <input type="checkbox" x-model="splitBill.includeSelf"
                                       class="rounded border-gray-300 text-[#00c078] focus:ring-[#00c078]">
                                <span>{{ __('Include myself in the total bill split') }}</span>
                            </label>
                            <div class="text-xs font-bold text-gray-900">
                                {{ __('Grand Total (incl. Tax & Service)') }}:
                                <span class="text-sm font-extrabold text-[#00c078]" x-text="formatSplitMoney(getSplitGrandTotal())"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Bank / E-Wallet Transfer Destination -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-gray-700">{{ __('Transfer Destination Account (Shown in WhatsApp)') }}</label>
                            @if ($wallets->isNotEmpty())
                                <div class="flex flex-wrap items-center gap-1">
                                    @foreach ($wallets->take(3) as $w)
                                        <button type="button"
                                                @click="splitBill.paymentInfo = @js($w->name . ($w->account_number ? ' (' . $w->account_number . ')' : '') . ' a/n ' . auth()->user()->name)"
                                                class="px-2 py-0.5 rounded-md bg-gray-100 hover:bg-[#e6f9f1] text-[10px] font-bold text-gray-700 hover:text-[#00995f] transition cursor-pointer">
                                            + {{ $w->name }}
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <input type="text" name="payment_info" x-model="splitBill.paymentInfo"
                               placeholder="{{ __('e.g. BCA 8821009912 a/n Gayuh / GoPay 0812xxxx') }}"
                               class="w-full rounded-xl border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                    </div>

                    <!-- Participants List -->
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-extrabold uppercase tracking-wider text-gray-500">
                                {{ __('Friends Participating in Split Bill') }} (<span x-text="splitBill.participants.length"></span>)
                            </label>
                            <button type="button"
                                    @click="addSplitParticipant()"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-[#e6f9f1] hover:bg-[#d4f5e6] text-[#00995f] text-xs font-bold transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ __('Add Friend') }}
                            </button>
                        </div>

                        <template x-for="(p, index) in splitBill.participants" :key="index">
                            <div class="p-3 rounded-xl border border-gray-200 bg-white space-y-2">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 items-center">
                                    <div class="sm:col-span-3">
                                        <input type="text"
                                               :name="'participants[' + index + '][name]'"
                                               x-model="p.name"
                                               required
                                               placeholder="{{ __('Friend Name') }}"
                                               class="w-full rounded-lg border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                                    </div>
                                    <div class="sm:col-span-3">
                                        <input type="text"
                                               :name="'participants[' + index + '][contact]'"
                                               x-model="p.contact"
                                               placeholder="{{ __('WhatsApp (0812...)') }}"
                                               class="w-full rounded-lg border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                                    </div>
                                    <div class="sm:col-span-3">
                                        <input type="text"
                                               :name="'participants[' + index + '][items_note]'"
                                               x-model="p.items_note"
                                               placeholder="{{ __('Menu / Items Ordered') }}"
                                               class="w-full rounded-lg border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                                    </div>
                                    <div class="sm:col-span-3" x-show="splitBill.mode === 'custom'">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               x-model="p.subtotal"
                                               placeholder="{{ __('Order Subtotal') }}"
                                               class="w-full rounded-lg border-gray-200 text-xs focus:border-[#00c078] focus:ring-[#00c078]">
                                    </div>
                                    <!-- Hidden final calculated amount sent to backend -->
                                    <input type="hidden"
                                           :name="'participants[' + index + '][amount]'"
                                           :value="getParticipantFinalShare(p)">
                                </div>

                                <div class="flex items-center justify-between pt-1.5 border-t border-gray-100 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-500">{{ __('Net Share (incl. Tax/Service)') }}:</span>
                                        <span class="font-extrabold text-[#00c078]" x-text="formatSplitMoney(getParticipantFinalShare(p))"></span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a :href="getParticipantWaUrl(p)"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#e6f9f1] hover:bg-[#d4f5e6] text-[#00995f] text-[11px] font-bold transition">
                                            {{ __('Send WhatsApp') }}
                                        </a>
                                        <button type="button"
                                                x-show="splitBill.participants.length > 1"
                                                @click="removeSplitParticipant(index)"
                                                class="text-red-500 hover:text-red-700 text-[11px] font-semibold cursor-pointer">
                                            {{ __('Remove') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- WhatsApp Group Summary Preview Box -->
                    <div class="p-3.5 rounded-xl bg-gray-900 text-gray-100 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-[#00c078]">{{ __('WhatsApp Group Summary Preview') }}</span>
                            <div class="flex items-center gap-2">
                                <button type="button"
                                        @click="copySplitSummary()"
                                        class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-white text-[11px] font-bold transition cursor-pointer">
                                    <span x-text="splitCopied ? '{{ __('Copied!') }}' : '{{ __('Copy Group Text') }}'"></span>
                                </button>
                                <a :href="'https://wa.me/?text=' + encodeURIComponent(buildGroupSummaryText())"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="px-2.5 py-1 rounded-lg bg-[#00c078] hover:bg-[#00ab6b] text-white text-[11px] font-bold transition">
                                    {{ __('Share to WhatsApp Group') }}
                                </a>
                            </div>
                        </div>
                        <pre class="text-[11px] font-mono whitespace-pre-wrap text-gray-300 leading-relaxed" x-text="buildGroupSummaryText()"></pre>
                    </div>

                    <div class="flex flex-wrap items-center justify-end gap-2 pt-2 border-t border-gray-100">
                        <button type="button" @click="showSplitModal = false"
                                class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50 cursor-pointer">
                            {{ __('Close') }}
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-bold transition cursor-pointer">
                            {{ __('Save All Shares to Receivables (Piutang)') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>

