<?php
namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class BudgetController extends Controller
{
    public function index(Request $request): View
    {
        $budgets = $request->user()->budgets()->with('category')->orderBy('name')->get();
        return view('budgets.index', compact('budgets'));
    }

    public function create(Request $request): View
    {
        $categories = $request->user()->categories()->where('type','expense')->orderBy('name')->get();
        return view('budgets.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required','string','max:255'],
            'amount'      => ['required','numeric','min:0.01'],
            'period'      => ['required','in:daily,weekly,monthly,yearly'],
            'category_id' => ['nullable','exists:categories,id'],
            'start_date'  => ['required','date'],
            'end_date'    => ['nullable','date','after_or_equal:start_date'],
        ]);

        $request->user()->budgets()->create($validated);
        return redirect()->route('budgets.index')->with('status','Budget created successfully.');
    }

    public function edit(Budget $budget): View
    {
        $this->authorize('update', $budget);
        $categories = auth()->user()->categories()->where('type','expense')->orderBy('name')->get();
        return view('budgets.edit', compact('budget','categories'));
    }

    public function update(Request $request, Budget $budget): RedirectResponse
    {
        $this->authorize('update', $budget);
        $validated = $request->validate([
            'name'        => ['required','string','max:255'],
            'amount'      => ['required','numeric','min:0.01'],
            'period'      => ['required','in:daily,weekly,monthly,yearly'],
            'category_id' => ['nullable','exists:categories,id'],
            'start_date'  => ['required','date'],
            'end_date'    => ['nullable','date','after_or_equal:start_date'],
        ]);
        $budget->update($validated);
        return redirect()->route('budgets.index')->with('status','Budget updated successfully.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $this->authorize('delete', $budget);
        $budget->delete();
        return redirect()->route('budgets.index')->with('status','Budget deleted.');
    }
}
