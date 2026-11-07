<x-app-layout>
    <x-slot name="title">Savings Goals</x-slot>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Savings Goals</h1>
            <p class="text-sm text-gray-500 mt-0.5">Track your progress toward financial targets</p>
        </div>
        <a href="{{ route('savings.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Goal
        </a>
    </div>

    @if ($goals->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($goals as $goal)
                @php $pct = $goal->progress_percentage; @endphp
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-start justify-between mb-2">
                        <h3 class="font-semibold text-gray-900">{{ $goal->name }}</h3>
                        <span class="text-xs px-2 py-1 rounded-full bg-purple-100 text-purple-700 font-medium">
                            {{ number_format($pct, 0) }}%
                        </span>
                    </div>
                    @if ($goal->description)
                        <p class="text-xs text-gray-400 mb-3">{{ $goal->description }}</p>
                    @endif

                    <div class="mb-3">
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full bg-purple-500 transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>

                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-500">Saved</span>
                        <span class="font-semibold text-gray-900">@currency($goal->current_amount)</span>
                    </div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-500">Target</span>
                        <span class="font-semibold text-gray-900">@currency($goal->target_amount)</span>
                    </div>
                    <div class="flex justify-between text-sm mb-4">
                        <span class="text-gray-500">Remaining</span>
                        <span class="font-semibold text-purple-600">@currency($goal->remaining)</span>
                    </div>
                    @if ($goal->target_date)
                        <p class="text-xs text-gray-400 mb-3">Target date: {{ format_user_date($goal->target_date) }}</p>
                    @endif

                    <!-- Add Funds Form -->
                    <form method="POST" action="{{ route('savings.add-funds', $goal) }}" class="mb-3 p-3 bg-purple-50 rounded-lg">
                        @csrf
                        <p class="text-xs font-semibold text-purple-700 mb-2">Add Funds</p>
                        <div class="flex gap-2 mb-2">
                            <input type="number" name="amount" placeholder="Amount" step="0.01" min="0.01" required
                                class="flex-1 px-2 py-1.5 border border-purple-200 rounded text-xs focus:outline-none focus:ring-1 focus:ring-purple-400">
                            <input type="date" name="contributed_at" value="{{ date('Y-m-d') }}" required
                                class="px-2 py-1.5 border border-purple-200 rounded text-xs focus:outline-none focus:ring-1 focus:ring-purple-400">
                        </div>
                        <button type="submit" class="w-full py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded transition-colors">
                            + Add Funds
                        </button>
                    </form>

                    <div class="flex gap-2 pt-3 border-t border-gray-50">
                        <a href="{{ route('savings.edit', $goal) }}" class="flex-1 text-center py-1.5 text-sm text-gray-600 hover:text-emerald-600 font-medium transition-colors">Edit</a>
                        <form method="POST" action="{{ route('savings.destroy', $goal) }}" onsubmit="return confirm('Delete this goal?')" class="flex-1">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full py-1.5 text-sm text-gray-600 hover:text-red-500 font-medium transition-colors">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm flex flex-col items-center justify-center py-20 text-gray-400">
            <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-medium">No savings goals yet</p>
            <a href="{{ route('savings.create') }}" class="mt-2 text-sm text-emerald-600 hover:text-emerald-700">Create your first goal</a>
        </div>
    @endif
</x-app-layout>
