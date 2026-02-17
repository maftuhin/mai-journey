<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization', '');
        if (!preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $plainToken = trim($matches[1]);
        $hashedToken = hash('sha256', $plainToken);

        $apiToken = ApiToken::with('user')
            ->where('token', $hashedToken)
            ->first();

        if (!$apiToken || !$apiToken->user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($apiToken->expires_at && $apiToken->expires_at->isPast()) {
            return response()->json(['message' => 'Token expired'], 401);
        }

        $apiToken->forceFill(['last_used_at' => now()])->save();
        Auth::setUser($apiToken->user);
        $request->setUserResolver(fn () => $apiToken->user);

        return $next($request);
    }
}
