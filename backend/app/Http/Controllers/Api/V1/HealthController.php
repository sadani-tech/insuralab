<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * Lightweight liveness check. Deliberately exposes nothing about the
     * environment or infrastructure.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'insuralab-api',
            'api_version' => 'v1',
            'time' => now()->toIso8601String(),
        ]);
    }
}
