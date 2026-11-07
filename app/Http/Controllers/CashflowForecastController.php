<?php

namespace App\Http\Controllers;

use App\Services\CashflowForecastService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashflowForecastController extends Controller
{
    /**
     * Display the Cashflow Forecast & Low Balance Alert page.
     */
    public function index(Request $request, CashflowForecastService $service): View
    {
        $forecast = $service->forecast($request->user(), (int) $request->query('days', 30));

        return view('cashflow.index', compact('forecast'));
    }
}

