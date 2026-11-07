<x-app-layout>
    <x-slot name="title">Budgets</x-slot>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Budgets</h1>
            <p class="text-sm text-gray-500 mt-0.5">Track your spending against budget targets</p>
        </div>
        <a href="{{ route('budgets.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Budget
        </a>
    </div>

    @if ($budgets->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($budgets as $budget)
                @php $pct = $budget->progress_percentage; @endphp
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-900">{{ $budget->name }}</h3>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $budget->category->name ?? 'All categories' }} · {{ ucfirst($budget->period) }}</p>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full font-medium
                            {{ $pct >= 90 ? 'bg-red-100 text-red-700' : ($pct >= 70 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                            {{ number_format($pct, 0) }}%
                        </span>
                    </div>

                    <div class="mb-3">
                        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all
                                {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                    </div>

                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-500">Spent</span>
                        <span class="font-semibold text-gray-900">@currency($budget->spent)</span>
                    </div>
                    <div class="flex justify-between text-sm mb-4">
                        <span class="text-gray-500">Budget</span>
                        <span class="font-semibold text-gray-900">@currency($budget->amount)</span>
                    </div>

                    <div class="flex gap-2 pt-3 border-t border-gray-50">
                        <a href="{{ route('budgets.edit', $budget) }}" class="flex-1 text-center py-1.5 text-sm text-gray-600 hover:text-emerald-600 font-medium transition-colors">Edit</a>
                        <form method="POST" action="{{ route('budgets.destroy', $budget) }}" onsubmit="return confirm('Delete this budget?')" class="flex-1">
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <p class="text-sm font-medium">No budgets yet</p>
            <a href="{{ route('budgets.create') }}" class="mt-2 text-sm text-emerald-600 hover:text-emerald-700">Create your first budget</a>
        </div>
    @endif
</x-app-layout>
