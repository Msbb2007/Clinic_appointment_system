<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPatientUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'patient') {
            return response()->json([
                'success' => false,
                'message' => 'دسترسی غیرمجاز. این بخش مخصوص بیماران است.'
            ], 403);
        }
        return $next($request);
    }
}
