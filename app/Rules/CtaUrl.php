<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CtaUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value) || preg_match('/\\s|[\\x00-\\x1F\\x7F]/', $value)) {
            $fail('The :attribute must be a valid URL, relative path, or anchor.');

            return;
        }

        if (preg_match('/^#[A-Za-z0-9][A-Za-z0-9_-]*$/', $value)) {
            return;
        }

        if (preg_match('/^\/(?!\/)[A-Za-z0-9._~\/-]*(?:#[A-Za-z0-9][A-Za-z0-9_-]*)?$/', $value)) {
            return;
        }

        if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
            $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

            if (in_array($scheme, ['http', 'https'], true)) {
                return;
            }
        }

        $fail('The :attribute must be a valid URL, relative path, or anchor.');
    }
}
