<x-app-layout>
    <x-slot name="title">New Budget</x-slot>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create Budget</h1>
        <p class="text-sm text-gray-500 mt-0.5">Set a spending limit for a category or period</p>
    </div>

    <div class="max-w-2xl bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <form method="POST" action="{{ route('budgets.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Budget Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Monthly Food Budget"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('name') border-red-500 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Budget Amount (@currencySymbol)</label>
                    <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0.01" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('amount') border-red-500 @enderror">
                    @error('amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Period</label>
                    <select name="period" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="daily" {{ old('period') === 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ old('period') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ old('period', 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="yearly" {{ old('period') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-gray-400">(optional)</span></label>
                <select name="category_id"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">All expense categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-01')) }}" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('start_date') border-red-500 @enderror">
                    @error('start_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date <span class="text-gray-400">(optional)</span></label>
                    <input type="date" name="end_date" value="{{ old('end_date', date('Y-m-t')) }}"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('end_date') border-red-500 @enderror">
                    @error('end_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    Create Budget
                </button>
                <a href="{{ route('budgets.index') }}" class="px-6 py-2.5 border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
