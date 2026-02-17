<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $credentials['username'])->first();
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $plainToken = Str::random(64);
        $token = ApiToken::create([
            'user_id' => $user->id,
            'token' => hash('sha256', $plainToken),
            'expires_at' => now()->addDays(30),
            'last_used_at' => now(),
        ]);

        return response()->json([
            'token' => $plainToken,
            'token_type' => 'Bearer',
            'expires_at' => optional($token->expires_at)->toIso8601String(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $header = $request->header('Authorization', '');
        if (preg_match('/Bearer\s+(.+)/i', $header, $matches)) {
            ApiToken::where('token', hash('sha256', trim($matches[1])))->delete();
        }

        return response()->json(['message' => 'Logged out']);
    }
}
