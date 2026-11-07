<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\QuickTransactionController;
use App\Http\Controllers\TransactionImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\SavingsGoalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\FinancialHealthController;
use App\Http\Controllers\FinancialWrappedController;
use App\Http\Controllers\AiAssistantController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RecurringBillController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ReceiptScannerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile & Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/preferences', [ProfileController::class, 'updatePreferences'])->name('profile.preferences.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Categories
    Route::resource('categories', CategoryController::class)->except(['show']);

    // Smart Import Mutasi Bank
    Route::get('transactions/import', [TransactionImportController::class, 'create'])->name('transactions.import');
    Route::post('transactions/import/parse', [TransactionImportController::class, 'parse'])->name('transactions.import.parse');
    Route::post('transactions/import/confirm', [TransactionImportController::class, 'confirm'])->name('transactions.import.confirm');
    Route::get('transactions/import/template', [TransactionImportController::class, 'downloadTemplate'])->name('transactions.import.template');

    // Transactions & Smart Quick-Add (Natural Language)
    Route::post('transactions/quick-add', [QuickTransactionController::class, 'store'])->name('transactions.quick-add');
    Route::resource('transactions', TransactionController::class)->except(['show']);

    // Wallets & Accounts
    Route::resource('wallets', WalletController::class)->except(['show', 'create', 'edit']);
    Route::post('wallets/transfer', [WalletController::class, 'transfer'])->name('wallets.transfer');

    // Budgets
    Route::resource('budgets', BudgetController::class)->except(['show']);

    // Recurring Bills & Subscriptions
    Route::resource('recurring-bills', RecurringBillController::class)->except(['show', 'create', 'edit']);
    Route::post('recurring-bills/{recurringBill}/pay', [RecurringBillController::class, 'pay'])->name('recurring-bills.pay');
    Route::post('recurring-bills/{recurringBill}/toggle', [RecurringBillController::class, 'toggleStatus'])->name('recurring-bills.toggle');

    // Debts & Loans Tracker + Split Bill WhatsApp
    Route::post('debts/split-bill', [DebtController::class, 'storeSplitBill'])->name('debts.split-bill');
    Route::resource('debts', DebtController::class)->except(['show', 'create', 'edit']);
    Route::post('debts/{debt}/pay', [DebtController::class, 'recordPayment'])->name('debts.pay');

    // Savings Goals
    Route::resource('savings', SavingsGoalController::class)->except(['show']);
    Route::post('savings/{saving}/add-funds', [SavingsGoalController::class, 'addFunds'])->name('savings.add-funds');

    // Financial Reports, Health Audit Radar & Monthly Wrapped
    Route::get('/financial-health', [FinancialHealthController::class, 'index'])->name('financial-health.index');
    Route::get('/wrapped', [FinancialWrappedController::class, 'index'])->name('wrapped.index');
    Route::get('/cashflow-forecast', [\App\Http\Controllers\CashflowForecastController::class, 'index'])->name('cashflow.index');
    Route::get('/tutorials', [\App\Http\Controllers\TutorialController::class, 'index'])->name('tutorials.index');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');

    // Cash Flow Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

    // AI Financial Advisor (Groq / Grok)
    Route::post('/ai/chat', [AiAssistantController::class, 'chat'])->name('ai.chat');

    // AI Receipt Scanner (OCR)
    Route::post('/receipts/scan', [ReceiptScannerController::class, 'scan'])->name('receipts.scan');
    Route::post('/receipts/store', [ReceiptScannerController::class, 'store'])->name('receipts.store');

    // Interactive Notification Center
    Route::post('/notifications/read', [NotificationController::class, 'markAllRead'])->name('notifications.read');
});

require __DIR__.'/auth.php';
