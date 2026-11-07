<x-app-layout>
    <x-slot name="title">Add Transaction</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('Add Transaction') }}</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ __('Record a new income, expense, or transfer') }}</p>
        </div>
        <button
            type="button"
            @click="$dispatch('open-receipt-scanner')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-[#00c078] hover:bg-[#00ab6b] text-white text-xs font-bold rounded-xl shadow-sm transition-all cursor-pointer"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/>
            </svg>
            <span>{{ __('Scan Receipt (AI OCR)') }}</span>
        </button>
    </div>

    <div class="max-w-2xl bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <form method="POST" action="{{ route('transactions.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title / Description</label>
                <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Salary Deposit, Superindo Grocery" required
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('title') border-red-500 @enderror">
                @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (@currencySymbol)</label>
                    <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0.01" placeholder="e.g. 5000000" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('amount') border-red-500 @enderror">
                    @error('amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select name="type" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('type') border-red-500 @enderror">
                        <option value="">Select type</option>
                        <option value="income" {{ old('type') === 'income' ? 'selected' : '' }}>Income (+)</option>
                        <option value="expense" {{ old('type') === 'expense' ? 'selected' : '' }}>Expense (-)</option>
                        <option value="transfer" {{ old('type') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>
                    @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category_id"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('category_id') border-red-500 @enderror">
                        <option value="">Select category (optional for transfer)</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }} ({{ ucfirst($cat->type) }})</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Account / Wallet (Funding Source)') }}</label>
                    <select name="wallet_id"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @foreach ($wallets as $w)
                            <option value="{{ $w->id }}" {{ (old('wallet_id') == $w->id || ($loop->first && !old('wallet_id'))) ? 'selected' : '' }}>
                                {{ $w->name }} ({{ __('Balance:') }} @currency($w->current_balance)) {{ $w->is_primary ? '• ' . __('Primary') : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method / Channel</label>
                    <input type="text" name="payment_method" value="{{ old('payment_method') }}" placeholder="e.g. QRIS, Debit, Transfer, Tunai"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('transaction_date') border-red-500 @enderror">
                    @error('transaction_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes / Subtitle <span class="text-gray-400">(optional)</span></label>
                <textarea name="notes" rows="3" placeholder="e.g. Monthly household stock, Pass replenishment"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    Save Transaction
                </button>
                <a href="{{ route('transactions.index') }}" class="px-6 py-2.5 border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
