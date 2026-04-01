<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DailyBusinessSummaryService;


class DailyBusinessController extends Controller
{
    protected $dailyBusinessService;
    public function __construct(DailyBusinessSummaryService $dailyBusinessService)
    {
        $this->dailyBusinessService = $dailyBusinessService;
    }
    public function generateDailySummary(Request $request)
    {
        $request->validate([
            'business_date' => 'nullable|date_format:Y-m-d',
        ]);
        $user=$request->user();
        $shop = $user->shop;
        $businessDate = $request->input('business_date', now()->toDateString());

        $summary = $this->dailyBusinessService->generateDailySummary($shop->id, $businessDate);

        return response()->json($summary);
    }
    public function getMonthlyReport(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);
        $user=$request->user();
        $shop = $user->shop;
        $month = $request->input('month');

        $report = $this->dailyBusinessService->getMonthlyReport($shop->id, $month);

        return response()->json($report);
    }
    public function getDailySummary(Request $request)
    {
        $request->validate([
            'business_date' => 'required|date_format:Y-m-d',
        ]);
        $user=$request->user();
        $shop = $user->shop;
        $businessDate = $request->input('business_date');

        $summary = $this->dailyBusinessService->getDailySummary($shop->id, $businessDate);

        if (!$summary) {
            return response()->json(['message' => 'No summary found for the given date'], 404);
        }

        return response()->json($summary);
    }
}
