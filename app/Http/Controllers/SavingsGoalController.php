<?php
namespace App\Http\Controllers;

use App\Models\SavingsGoal;
use App\Models\SavingsContribution;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SavingsGoalController extends Controller
{
    public function index(Request $request): View
    {
        $goals = $request->user()->savingsGoals()->orderBy('created_at','desc')->get();
        return view('savings.index', compact('goals'));
    }

    public function create(): View
    {
        return view('savings.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'          => ['required','string','max:255'],
            'target_amount' => ['required','numeric','min:0.01'],
            'target_date'   => ['nullable','date'],
            'description'   => ['nullable','string'],
        ]);
        $validated['current_amount'] = 0;
        $request->user()->savingsGoals()->create($validated);
        return redirect()->route('savings.index')->with('status','Savings goal created.');
    }

    public function edit(SavingsGoal $saving): View
    {
        $this->authorize('update', $saving);
        return view('savings.edit', compact('saving'));
    }

    public function update(Request $request, SavingsGoal $saving): RedirectResponse
    {
        $this->authorize('update', $saving);
        $validated = $request->validate([
            'name'          => ['required','string','max:255'],
            'target_amount' => ['required','numeric','min:0.01'],
            'target_date'   => ['nullable','date'],
            'description'   => ['nullable','string'],
        ]);
        $saving->update($validated);
        return redirect()->route('savings.index')->with('status','Savings goal updated.');
    }

    public function destroy(SavingsGoal $saving): RedirectResponse
    {
        $this->authorize('delete', $saving);
        $saving->delete();
        return redirect()->route('savings.index')->with('status','Goal deleted.');
    }

    public function addFunds(Request $request, SavingsGoal $saving): RedirectResponse
    {
        $this->authorize('update', $saving);
        $validated = $request->validate([
            'amount'         => ['required','numeric','min:0.01'],
            'notes'          => ['nullable','string'],
            'contributed_at' => ['required','date'],
        ]);

        $saving->contributions()->create([
            'user_id'        => $request->user()->id,
            'amount'         => $validated['amount'],
            'notes'          => $validated['notes'] ?? null,
            'contributed_at' => $validated['contributed_at'],
        ]);

        $saving->increment('current_amount', $validated['amount']);

        return redirect()->route('savings.index')->with('status','Funds added to goal.');
    }
}
