<?php

namespace App\Http\Controllers;

use App\Services\FinancialHealthService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialHealthController extends Controller
{
    /**
     * Display the Financial Health Score & 5-Axis Audit Radar dashboard.
     */
    public function index(Request $request, FinancialHealthService $healthService): View
    {
        $audit = $healthService->analyze($request->user());

        return view('financial-health.index', compact('audit'));
    }
}

