<x-app-layout>
    <x-slot name="title">New Savings Goal</x-slot>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create Savings Goal</h1>
        <p class="text-sm text-gray-500 mt-0.5">Set a new financial target to work toward</p>
    </div>

    <div class="max-w-2xl bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <form method="POST" action="{{ route('savings.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Goal Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Emergency Fund"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('name') border-red-500 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Amount (@currencySymbol)</label>
                    <input type="number" name="target_amount" value="{{ old('target_amount') }}" step="0.01" min="0.01" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('target_amount') border-red-500 @enderror">
                    @error('target_amount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Date <span class="text-gray-400">(optional)</span></label>
                    <input type="date" name="target_date" value="{{ old('target_date') }}"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-gray-400">(optional)</span></label>
                <textarea name="description" rows="3" placeholder="What are you saving for?"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ old('description') }}</textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
                    Create Goal
                </button>
                <a href="{{ route('savings.index') }}" class="px-6 py-2.5 border border-gray-200 hover:bg-gray-50 text-gray-600 text-sm font-medium rounded-lg transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
