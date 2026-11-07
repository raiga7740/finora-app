<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>Finora Financial Report - {{ $startDate->format('F Y') }}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color: #1f2937; margin: 0; padding: 24px; font-size: 13px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #10b981; padding-bottom: 16px; margin-bottom: 24px; }
        .logo { font-size: 24px; font-weight: bold; color: #10b981; }
        .report-title { font-size: 18px; font-weight: bold; color: #111827; }
        .meta { color: #6b7280; font-size: 12px; margin-top: 4px; }
        .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 24px; }
        .card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; }
        .card-label { font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: 600; }
        .card-value { font-size: 18px; font-weight: bold; margin-top: 6px; }
        .val-income { color: #059669; }
        .val-expense { color: #dc2626; }
        .val-savings { color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 12px; }
        th { text-align: left; padding: 8px 12px; background: #f3f4f6; color: #4b5563; font-weight: 600; border-bottom: 1px solid #d1d5db; }
        td { padding: 8px 12px; border-bottom: 1px solid #f3f4f6; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .amount-in { color: #059669; font-weight: 600; }
        .amount-out { color: #374151; font-weight: 600; }
        .print-btn { background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; font-size: 13px; }
        .print-btn svg { width: 14px; height: 14px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button class="print-btn" onclick="window.print()">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            <span>Print / Save as PDF</span>
        </button>
    </div>

    <div class="header">
        <div>
            <div class="logo">Finora</div>
            <div class="meta">Personal Finance Command Center • Statement Report</div>
        </div>
        <div style="text-align: right;">
            <div class="report-title">Financial Statement</div>
            <div class="meta">Period: {{ $startDate->format('01 M Y') }} – {{ $endDate->format('d M Y') }}</div>
            <div class="meta">Account Owner: {{ $user->name }} ({{ $user->email }})</div>
        </div>
    </div>

    <div class="summary-grid">
        <div class="card">
            <div class="card-label">Total Inflow / Income</div>
            <div class="card-value val-income">@currency($totalIncome)</div>
        </div>
        <div class="card">
            <div class="card-label">Total Spend / Outflow</div>
            <div class="card-value val-expense">@currency($totalExpense)</div>
        </div>
        <div class="card">
            <div class="card-label">Monthly Net Savings</div>
            <div class="card-value val-savings">@currency($netSavings)</div>
        </div>
        <div class="card">
            <div class="card-label">Savings Rate</div>
            <div class="card-value">{{ $savingsRate }}%</div>
        </div>
    </div>

    <h3 style="font-size: 14px; margin-bottom: 8px; color: #111827;">Detailed Ledger Records ({{ $transactions->count() }})</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Date</th>
                <th style="width: 35%;">Transaction Description</th>
                <th style="width: 20%;">Category</th>
                <th style="width: 15%;">Method</th>
                <th style="width: 15%;" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $t)
                <tr>
                    <td>{{ $t->transaction_date ? format_user_date($t->transaction_date) : '—' }}</td>
                    <td>
                        <strong>{{ $t->title }}</strong>
                        @if ($t->notes)<br><span style="color: #6b7280; font-size: 11px;">{{ $t->notes }}</span>@endif
                    </td>
                    <td>{{ $t->category->name ?? '—' }}</td>
                    <td>{{ $t->payment_method ?? '—' }}</td>
                    <td class="text-right {{ $t->type === 'income' ? 'amount-in' : 'amount-out' }}">
                        {{ $t->type === 'income' ? '+' : '-' }}@currency($t->amount)
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 24px; color: #9ca3af;">No transactions recorded for this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 36px; border-top: 1px solid #e5e7eb; padding-top: 12px; font-size: 11px; color: #9ca3af; text-align: center;">
        Finora Application • Generated on {{ now()->format('d F Y, H:i') }} • Verified Ledger Audit
    </div>
</body>
</html>

