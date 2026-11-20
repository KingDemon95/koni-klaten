<?php

namespace App\Http\Controllers;

use App\Support\Monitoring\SystemHealthService;
use Illuminate\View\View;

class SystemHealthController extends Controller
{
    public function index(SystemHealthService $health): View
    {
        return view('system.health', [
            'report' => $health->report(),
        ]);
    }
}
