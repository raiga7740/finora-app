<x-app-layout>
    <x-slot name="title">Edit Budget</x-slot>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Budget</h1>
        <p class="text-sm text-gray-500 mt-0.5">Update your budget settings</p>
    </div>

    <div class="max-w-2xl bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <form method="POST" action="{{ route('budgets.update', $budget) }}" class="space-y-5">
            @csrf @method('PATCH')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Budget Name</label>
                <input type="text" name="name" value="{{ old('name', $budget->name) }}" required
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Budget Amount (@currencySymbol)</label>
                    <input type="number" name="amount" value="{{ old('amount', $budget->amount) }}" step="0.01" min="0.01" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Period</label>
                    <select name="period" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @foreach (['daily','weekly','monthly','yearly'] as $p)
                            <option value="{{ $p }}" {{ old('period', $budget->period) === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-gray-400">(optional)</span></label>
                <select name="category_id"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">All expense categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $budget->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $budget->start_date->format('Y-m-d')) }}" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date <span class="text-gray-400">(optional)</span></label>
                    <input type="date" name="end_date" value="{{ old('end_date', $budget->end_date?->format('Y-m-d')) }}"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    Update Budget
                </button>
                <a href="{{ route('budgets.index') }}" class="px-6 py-2.5 border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
