<x-app-layout>
    <x-slot name="title">Bills & Subscriptions</x-slot>

    <div x-data="{
        createModalOpen: false,
        editModalOpen: false,
        payModalOpen: false,
        activeBill: {
            id: '',
            name: '',
            amount: '',
            category_id: '',
            billing_cycle: 'monthly',
            due_day: 1,
            payment_method: 'bca',
            status: 'active',
            notes: ''
        },
        openEdit(bill) {
            this.activeBill = Object.assign({}, bill);
            this.editModalOpen = true;
        },
        openPay(bill) {
            this.activeBill = Object.assign({}, bill);
            this.payModalOpen = true;
        }
    }">

        {{-- Top Header Section --}}
        <div class="mb-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[11px] font-black text-[#00c078] tracking-widest uppercase">{{ __('Fixed Commitments') }}</span>
                    <span class="text-gray-300">•</span>
                    <span class="text-[11px] font-semibold text-gray-400">{{ __('Cash Flow Velocity') }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">{{ __('Bills & Subscriptions') }}</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    {{ __('Manage recurring bills, monthly subscriptions, and 1-click payments for automated ledger tracking.') }}
                </p>
            </div>

            <button
                type="button"
                @click="createModalOpen = true"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs sm:text-sm font-semibold shadow-sm shadow-[#00c078]/20 transition-all cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span>{{ __('Add Bill') }}</span>
            </button>
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

        {{-- 4 Top KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
            {{-- Card 1: Total Monthly Commitment --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Total Monthly Commitment') }}</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-[#00c078] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">
                        @currency($totalMonthlyCommitment)
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">{{ __('Monthly fixed expense commitments') }}</p>
                </div>
            </div>

            {{-- Card 2: Menunggu Pembayaran --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Awaiting Payment') }}</span>
                    <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="text-xl sm:text-2xl font-black text-amber-600 tracking-tight">
                        {{ $unpaidCount }} <span class="text-xs font-semibold text-gray-500">{{ __('Bills') }}</span>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">{{ __('Remaining') }}: @currency($totalUpcomingAmount)</p>
                </div>
            </div>

            {{-- Card 3: Sudah Terbayar --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Paid This Month') }}</span>
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="text-xl sm:text-2xl font-black text-blue-600 tracking-tight">
                        {{ $paidCount }} <span class="text-xs font-semibold text-gray-500">{{ __('Bills') }}</span>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">{{ __('Recorded') }}: @currency($totalPaidAmount)</p>
                </div>
            </div>

            {{-- Card 4: Tagihan Aktif --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">{{ __('Registered Bills') }}</span>
                    <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="text-xl sm:text-2xl font-black text-gray-900 tracking-tight">
                        {{ $bills->count() }} <span class="text-xs font-semibold text-gray-500">{{ __('Total') }}</span>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">{{ $totalActive }} {{ __('active recurring') }}</p>
                </div>
            </div>
        </div>

        {{-- Bills List Section --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-2xs overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">{{ __('Bills & Subscriptions List') }}</h2>
                    <p class="text-[11px] text-gray-500 mt-0.5">{{ __('Monitor status, due dates, and click Pay for instant recording') }}</p>
                </div>
                <span class="text-[11px] font-semibold text-gray-400">{{ $bills->count() }} {{ __('Registered') }}</span>
            </div>

            @if ($bills->isNotEmpty())
                <div class="divide-y divide-gray-100">
                    @foreach ($bills as $bill)
                        @php
                            $isPaid = $bill->isPaidForCurrentCycle();
                            $days = $bill->days_until_due;
                            $isOverdue = $bill->isOverdue();
                        @endphp
                        <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4 hover:bg-gray-50/70 transition-colors {{ $bill->status !== 'active' ? 'opacity-60 bg-gray-50/30' : '' }}">
                            {{-- Left: Icon & Info --}}
                            <div class="flex items-start sm:items-center gap-3.5 min-w-0">
                                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0
                                    @if ($isPaid) bg-emerald-50 text-emerald-600
                                    @elseif ($isOverdue) bg-red-50 text-red-600
                                    @elseif ($bill->status !== 'active') bg-gray-100 text-gray-400
                                    @elseif ($days <= 3) bg-amber-50 text-amber-600
                                    @else bg-blue-50 text-blue-600
                                    @endif">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                                    </svg>
                                </div>

                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-sm font-bold text-gray-900 truncate">{{ $bill->name }}</h3>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold uppercase tracking-wider bg-gray-100 text-gray-600">
                                            {{ ucfirst($bill->billing_cycle) }}
                                        </span>

                                        @if ($bill->status !== 'active')
                                            <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold uppercase tracking-wider bg-gray-200 text-gray-700">
                                                {{ $bill->status }}
                                            </span>
                                        @elseif ($isPaid)
                                            <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full font-semibold uppercase tracking-wider bg-emerald-100 text-emerald-800">
                                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                                {{ __('Paid This Cycle') }}
                                            </span>
                                        @elseif ($isOverdue)
                                            <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider bg-red-100 text-red-800 animate-pulse">
                                                {{ __('Overdue by :days Days', ['days' => abs($days)]) }}
                                            </span>
                                        @elseif ($days === 0)
                                            <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase tracking-wider bg-amber-100 text-amber-800">
                                                {{ __('Due Today') }}
                                            </span>
                                        @elseif ($days <= 3)
                                            <span class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full font-semibold uppercase tracking-wider bg-amber-50 text-amber-700">
                                                {{ __(':days Days Left', ['days' => $days]) }}
                                            </span>
                                        @else
                                            <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold uppercase tracking-wider bg-blue-50 text-blue-700">
                                                {{ __('Due: Day :day', ['day' => $bill->due_day]) }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-3 text-xs text-gray-500 mt-1 flex-wrap">
                                        <span>{{ __('Category') }}: <strong class="text-gray-700">{{ $bill->category->name ?? __('General Expenses') }}</strong></span>
                                        <span>&bull;</span>
                                        <span>{{ __('Method') }}: <strong class="text-gray-700 uppercase">{{ $bill->payment_method }}</strong></span>
                                        @if ($bill->last_paid_at)
                                            <span>&bull;</span>
                                            <span>{{ __('Last paid') }}: {{ format_user_date($bill->last_paid_at) }}</span>
                                        @endif
                                    </div>
                                    @if ($bill->notes)
                                        <p class="text-[11px] text-gray-400 mt-0.5 truncate max-w-md">{{ $bill->notes }}</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Right: Amount & Action Buttons --}}
                            <div class="flex items-center justify-between md:justify-end gap-3 sm:gap-4 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-gray-100">
                                <div class="text-left md:text-right">
                                    <div class="text-base sm:text-lg font-black text-gray-900">
                                        @currency($bill->amount)
                                    </div>
                                    <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
                                        {{ __('Next due date') }}: {{ format_user_date($bill->next_due_date) }}
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    {{-- Pay Button (1-Click) --}}
                                    @if ($bill->status === 'active' && !$isPaid)
                                        <button
                                            type="button"
                                            @click="openPay({{ json_encode($bill) }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-bold shadow-sm shadow-[#00c078]/20 transition-all cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                            </svg>
                                            <span>{{ __('Pay') }}</span>
                                        </button>
                                    @elseif ($isPaid)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-gray-100 text-gray-500 text-xs font-semibold cursor-default">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                                            </svg>
                                            <span>{{ __('Paid') }}</span>
                                        </span>
                                    @endif

                                    {{-- Toggle Pause/Resume --}}
                                    <form method="POST" action="{{ route('recurring-bills.toggle', $bill) }}" class="inline">
                                        @csrf
                                        <button
                                            type="submit"
                                            title="{{ $bill->status === 'active' ? __('Pause bill') : __('Resume bill') }}"
                                            class="p-2 rounded-xl border border-gray-200 text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition-colors cursor-pointer"
                                        >
                                            @if ($bill->status === 'active')
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5"/>
                                                </svg>
                                            @else
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>

                                    {{-- Edit Button --}}
                                    <button
                                        type="button"
                                        @click="openEdit({{ json_encode($bill) }})"
                                        class="p-2 rounded-xl border border-gray-200 text-gray-500 hover:text-emerald-700 hover:bg-emerald-50 hover:border-emerald-200 transition-colors cursor-pointer"
                                        title="{{ __('Edit bill') }}"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                        </svg>
                                    </button>

                                    {{-- Delete Button --}}
                                    <form method="POST" action="{{ route('recurring-bills.destroy', $bill) }}" onsubmit="return confirm('{{ __('Delete this recurring bill? Existing past ledger entries will not be affected.') }}')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-2 rounded-xl border border-gray-200 text-gray-500 hover:text-red-600 hover:bg-red-50 hover:border-red-200 transition-colors cursor-pointer"
                                            title="{{ __('Delete bill') }}"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-12 text-center">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 text-gray-400 mx-auto flex items-center justify-center mb-4">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-900 mb-1">{{ __('No Bills or Subscriptions Yet') }}</h3>
                    <p class="text-xs text-gray-500 max-w-md mx-auto mb-5 leading-relaxed">
                        {{ __('Register your regular commitments like WiFi, Electricity, Insurance, Rent, or streaming services for automated due-date alerts.') }}
                    </p>
                    <button
                        type="button"
                        @click="createModalOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-semibold shadow-sm shadow-[#00c078]/25 transition-all"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        <span>{{ __('Create First Bill') }}</span>
                    </button>
                </div>
            @endif
        </div>

        {{-- ================= MODAL TAMBAH TAGIHAN ================= --}}
        <div
            x-show="createModalOpen"
            style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
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
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900" id="modal-title">{{ __('Add Bill') }}</h3>
                                <p class="text-[11px] text-gray-500">{{ __('Schedule your recurring expense commitments') }}</p>
                            </div>
                        </div>
                        <button @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('recurring-bills.store') }}" class="space-y-4">
                        @csrf

                        {{-- Nama Tagihan --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Bill / Service Name') }} *</label>
                            <input
                                type="text"
                                name="name"
                                required
                                placeholder="e.g. WiFi Fiber, Netflix Premium, Health Insurance"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                            >
                        </div>

                        {{-- Nominal & Siklus --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Amount') }} (@currencySymbol) *</label>
                                <input
                                    type="number"
                                    name="amount"
                                    required
                                    min="1"
                                    step="any"
                                    placeholder="50"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Billing Cycle') }} *</label>
                                <select
                                    name="billing_cycle"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                                    <option value="monthly" selected>{{ __('Monthly') }}</option>
                                    <option value="weekly">{{ __('Weekly') }}</option>
                                    <option value="yearly">{{ __('Yearly') }}</option>
                                </select>
                            </div>
                        </div>

                        {{-- Tanggal Jatuh Tempo & Kategori --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Due Day (1-31)') }} *</label>
                                <input
                                    type="number"
                                    name="due_day"
                                    required
                                    min="1"
                                    max="31"
                                    value="15"
                                    placeholder="15"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Expense Category') }}</label>
                                <select
                                    name="category_id"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                                    <option value="">-- {{ __('Select Category') }} --</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Metode Pembayaran & Catatan --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Default Payment Method') }}</label>
                                <select
                                    name="payment_method"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                                    <option value="bank">Bank Transfer / Debit</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="wise">Wise</option>
                                    <option value="bca">Bank BCA</option>
                                    <option value="mandiri">Bank Mandiri</option>
                                    <option value="gopay">GoPay</option>
                                    <option value="cash">{{ __('Cash & Physical') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Notes (Optional)') }}</label>
                                <input
                                    type="text"
                                    name="notes"
                                    placeholder="Customer ID / Subscription account"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                            </div>
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
                                {{ __('Save Bill') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ================= MODAL EDIT TAGIHAN ================= --}}
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
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900" id="modal-edit-title">{{ __('Update Bill') }}</h3>
                                <p class="text-[11px] text-gray-500">{{ __('Update amount or due schedule') }}</p>
                            </div>
                        </div>
                        <button @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form :action="'{{ url('recurring-bills') }}/' + activeBill.id" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        {{-- Nama Tagihan --}}
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Bill / Service Name') }} *</label>
                            <input
                                type="text"
                                name="name"
                                required
                                x-model="activeBill.name"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                            >
                        </div>

                        {{-- Nominal & Siklus --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Amount') }} (@currencySymbol) *</label>
                                <input
                                    type="number"
                                    name="amount"
                                    required
                                    min="1"
                                    step="any"
                                    x-model="activeBill.amount"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Billing Cycle') }} *</label>
                                <select
                                    name="billing_cycle"
                                    x-model="activeBill.billing_cycle"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                                    <option value="monthly">{{ __('Monthly') }}</option>
                                    <option value="weekly">{{ __('Weekly') }}</option>
                                    <option value="yearly">{{ __('Yearly') }}</option>
                                </select>
                            </div>
                        </div>

                        {{-- Tanggal Jatuh Tempo & Status --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Due Day (1-31)') }} *</label>
                                <input
                                    type="number"
                                    name="due_day"
                                    required
                                    min="1"
                                    max="31"
                                    x-model="activeBill.due_day"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Status') }} *</label>
                                <select
                                    name="status"
                                    x-model="activeBill.status"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                                    <option value="active">{{ __('Active') }}</option>
                                    <option value="paused">{{ __('Paused') }}</option>
                                    <option value="cancelled">{{ __('Cancelled') }}</option>
                                </select>
                            </div>
                        </div>

                        {{-- Kategori & Metode Bayar --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Category') }}</label>
                                <select
                                    name="category_id"
                                    x-model="activeBill.category_id"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                                    <option value="">-- {{ __('Select Category') }} --</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Payment Method') }}</label>
                                <select
                                    name="payment_method"
                                    x-model="activeBill.payment_method"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                                >
                                    <option value="bank">Bank Transfer / Debit</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="wise">Wise</option>
                                    <option value="bca">Bank BCA</option>
                                    <option value="mandiri">Bank Mandiri</option>
                                    <option value="gopay">GoPay</option>
                                    <option value="cash">{{ __('Cash & Physical') }}</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Notes (Optional)') }}</label>
                            <input
                                type="text"
                                name="notes"
                                x-model="activeBill.notes"
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
                                {{ __('Update Bill') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ================= MODAL BAYAR (1-KLIK) ================= --}}
        <div
            x-show="payModalOpen"
            style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-pay-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="fixed inset-0 bg-black/50 backdrop-blur-xs transition-opacity" @click="payModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div
                    class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl border border-gray-100 transition-all text-left"
                    @click.stop
                >
                    <div class="text-center mb-5">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-[#00c078] mx-auto flex items-center justify-center mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900" id="modal-pay-title">{{ __('Confirm Bill Payment') }}</h3>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ __('Finora will automatically record this expense into your transactions ledger.') }}
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 mb-5">
                        <div class="flex justify-between items-center text-xs mb-1.5">
                            <span class="text-gray-500">{{ __('Bill / Service Name') }}:</span>
                            <span class="font-bold text-gray-900" x-text="activeBill.name"></span>
                        </div>
                        <div class="flex justify-between items-center text-xs mb-1.5">
                            <span class="text-gray-500">{{ __('Amount') }}:</span>
                            <span class="font-black text-emerald-600 text-sm">
                                @currencySymbol<span x-text="Number(activeBill.amount).toLocaleString('{{ (auth()->user()->currency ?? 'IDR') === 'IDR' ? 'id-ID' : 'en-US' }}')"></span>
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-500">{{ __('Execution Date') }}:</span>
                            <span class="font-medium text-gray-700">{{ format_user_date(now()) }} ({{ __('Today') }})</span>
                        </div>
                    </div>

                    <form :action="'{{ url('recurring-bills') }}/' + activeBill.id + '/pay'" method="POST">
                        @csrf

                        <div class="mb-5">
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">{{ __('Funding Source / Payment Wallet') }}</label>
                            <select
                                name="payment_method"
                                x-model="activeBill.payment_method"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-[#00c078]/20 focus:border-[#00c078] transition"
                            >
                                <option value="bank">Bank Transfer / Debit</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="paypal">PayPal</option>
                                <option value="wise">Wise</option>
                                <option value="bca">Bank BCA</option>
                                <option value="mandiri">Bank Mandiri</option>
                                <option value="gopay">GoPay</option>
                                <option value="cash">{{ __('Cash & Physical') }}</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <button
                                type="button"
                                @click="payModalOpen = false"
                                class="flex-1 py-2.5 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition cursor-pointer"
                            >
                                {{ __('Cancel') }}
                            </button>
                            <button
                                type="submit"
                                class="flex-1 py-2.5 rounded-xl bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-bold shadow-sm shadow-[#00c078]/25 transition cursor-pointer"
                            >
                                {{ __('Pay Now') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>

