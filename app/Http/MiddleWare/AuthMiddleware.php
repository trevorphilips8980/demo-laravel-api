<?php

namespace App\Http\Middleware;

use App\Services\JsonService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $jsonService = new JsonService;

        if (!Auth::guard('api')->check()) {
            return $jsonService->sendResponse(false, [], __('Unauthorized!'), 401);
        }

        $user = Auth::guard('api')->user();
        return $next($request);
    }
}
