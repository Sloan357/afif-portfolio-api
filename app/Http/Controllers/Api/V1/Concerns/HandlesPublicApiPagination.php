<?php

namespace App\Http\Controllers\Api\V1\Concerns;

use App\Support\ApiResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait HandlesPublicApiPagination
{
    private const DEFAULT_PER_PAGE = 12;

    private const MAX_PER_PAGE = 50;

    /**
     * @return array{page: int, perPage: int}|JsonResponse
     */
    protected function resolvePublicApiPagination(Request $request, array $meta): array|JsonResponse
    {
        $errors = [];
        $page = $request->query('page', 1);
        $perPage = $request->query('perPage', self::DEFAULT_PER_PAGE);

        if (! $this->isPositiveInteger($page)) {
            $errors['page'] = ['The page must be an integer greater than or equal to 1.'];
        }

        if (! $this->isPositiveInteger($perPage)) {
            $errors['perPage'] = ['The perPage must be an integer between 1 and 50.'];
        }

        if ($errors === [] && (int) $perPage > self::MAX_PER_PAGE) {
            $errors['perPage'] = ['The perPage must be an integer between 1 and 50.'];
        }

        if ($errors !== []) {
            return ApiResponse::validationError($errors, $meta);
        }

        return [
            'page' => (int) $page,
            'perPage' => (int) $perPage,
        ];
    }

    /**
     * @return array<string, int|null>
     */
    protected function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'currentPage' => $paginator->currentPage(),
            'perPage' => $paginator->perPage(),
            'lastPage' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    protected function paginationLinks(LengthAwarePaginator $paginator): array
    {
        return [
            'self' => $paginator->url($paginator->currentPage()),
            'first' => $paginator->url(1),
            'last' => $paginator->url($paginator->lastPage()),
            'prev' => $paginator->previousPageUrl(),
            'next' => $paginator->nextPageUrl(),
        ];
    }

    private function isPositiveInteger(mixed $value): bool
    {
        if (is_array($value)) {
            return false;
        }

        if (is_int($value)) {
            return $value >= 1;
        }

        return is_string($value) && preg_match('/^[1-9][0-9]*$/', $value) === 1;
    }
}
