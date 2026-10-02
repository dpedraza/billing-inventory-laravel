<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService
    ) {}

    public function __invoke(): View
    {
        $user = auth()->user();
        $data = $this->dashboardService->getDashboardDataForUser($user);

        return view('dashboard.index', $data);
    }
}

