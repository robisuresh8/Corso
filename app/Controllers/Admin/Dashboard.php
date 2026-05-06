<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\DashboardService;
use App\Services\AnalyticsService;

class Dashboard extends BaseController
{
    protected AnalyticsService $analyticsService;
    protected DashboardService $dashboardService;

    public function __construct()
    {
        $this->analyticsService = new AnalyticsService();
        $this->dashboardService = new DashboardService();
    }

    public function index()
    {
        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $this->dashboardService->getKPIData()
        ]);
    }
    public function analytics()
    {
        // Call the methods from the property
        return $this->response->setJSON([
            'status' => 'success',
            'data'   => [
                'monthlyUsers'    => $this->analyticsService->monthlyUsers(),
                'monthlyRevenue'  => $this->analyticsService->monthlyRevenue(),
                'monthlyAttempts' => $this->analyticsService->monthlyAttempts(),
                'monthlyPassRate' => $this->analyticsService->monthlyPassRate(),
                'topCourses'      => $this->analyticsService->topCourses(),
                'weeklyActivity'  => $this->analyticsService->weeklyActivity()
                
            ]
        ]);
    }
}