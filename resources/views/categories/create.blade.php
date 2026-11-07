<x-app-layout>
    <x-slot name="title">New Category</x-slot>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create Category</h1>
        <p class="text-sm text-gray-500 mt-0.5">Add a new category to organize your transactions</p>
    </div>

    <div class="max-w-lg bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <form method="POST" action="{{ route('categories.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Groceries"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('name') border-red-500 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" required
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('type') border-red-500 @enderror">
                    <option value="">Select type</option>
                    <option value="income" {{ old('type') === 'income' ? 'selected' : '' }}>Income</option>
                    <option value="expense" {{ old('type') === 'expense' ? 'selected' : '' }}>Expense</option>
                    <option value="saving" {{ old('type') === 'saving' ? 'selected' : '' }}>Saving</option>
                </select>
                @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-gray-400">(optional)</span></label>
                <textarea name="description" rows="2"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ old('description') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    Create Category
                </button>
                <a href="{{ route('categories.index') }}" class="px-6 py-2.5 border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
