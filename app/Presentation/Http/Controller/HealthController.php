<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controller;

use Illuminate\Http\JsonResponse;

final class HealthController
{
    public function show(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
        ], 200);
    }

    public function check(): JsonResponse
    {
        return $this->show();
    }
}

