<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;

class Authenticate extends Middleware
{
    protected function unauthenticated($request, array $guards)
    {
        if ($request->routeIs(
            'invoices.download',
            'payrolls.download',
            'bank-checks.download',
            'requests.download',
            'bank-checks-preview.download',
            'reports.download'
        )) {
            throw new HttpResponseException(response()->json([
                'message' => 'Unauthenticated.',
            ], 401)->header('Cache-Control', 'private, no-store'));
        }

        parent::unauthenticated($request, $guards);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
