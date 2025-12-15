<?php

namespace Iqonic\FileManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Iqonic\FileManager\Services\UsageService;

class StatsController extends Controller
{
    protected UsageService $usageService;

    public function __construct(UsageService $usageService)
    {
        $this->usageService = $usageService;
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id;

        return response()->json([
            'used' => $this->usageService->getUserUsage($userId),
            'quota' => $this->usageService->getUserQuota($userId),
            'by_type' => $this->usageService->getUsageByType($userId),
        ]);
    }
}
