<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateRouteIdentifiers
{
    public function handle(Request $request, Closure $next): Response
    {
        // These routes use integer IDs (not the readable keys handled by their models).
        foreach (['user', 'booking', 'approval', 'branch', 'pic', 'template', 'message'] as $parameter) {
            $value = $request->route($parameter);
            if ($value === null) {
                continue;
            }
            abort_unless(is_string($value) && ctype_digit($value)
                && filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false, 404);
        }

        return $next($request);
    }
}
