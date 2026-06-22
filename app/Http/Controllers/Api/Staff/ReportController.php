<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Services\ReportsService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function summary(Request $request, ReportsService $reportsService)
    {
        if (!$request->user()->can('reports.view')) {
            abort(403);
        }

        return response()->json(['data' => $reportsService->summary()]);
    }

    public function donations(Request $request, ReportsService $reportsService)
    {
        if (!$request->user()->can('reports.view')) {
            abort(403);
        }

        return response()->json(['data' => $reportsService->donationReport()]);
    }

    public function inventory(Request $request, ReportsService $reportsService)
    {
        if (!$request->user()->can('reports.view')) {
            abort(403);
        }

        return response()->json(['data' => $reportsService->inventoryReport()]);
    }
}
