<?php

namespace App\Http\Controllers;

use App\Services\DashboardMetricsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(DashboardMetricsService $dashboardMetrics): View
    {
        return view('ecommerce', $dashboardMetrics->data());
    }
}
