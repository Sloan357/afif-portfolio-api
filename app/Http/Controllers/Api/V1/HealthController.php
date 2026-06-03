<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Concerns\HandlesPublicApiRequests;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthController extends Controller
{
    use HandlesPublicApiRequests;

    public function __invoke(Request $request): JsonResponse
    {
        $localeMeta = $this->resolvePublicApiLocale($request);

        if ($localeMeta instanceof JsonResponse) {
            return $localeMeta;
        }

        return ApiResponse::make([
            'status' => 'ok',
        ], $localeMeta);
    }
}
