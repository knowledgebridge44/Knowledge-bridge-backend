<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user (SPA cookie-based authentication).
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'sometimes|in:student,teacher',
        ]);

        // Prevent self-registration as Admin
        if ($request->role === 'admin') {
            return response()->json([
                'message' => 'Cannot self-register as admin',
            ], 403);
        }

        $user = User::create([
            'full_name' => $request->name, // Map 'name' to 'full_name'
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'student',
        ]);

        // Log the user in (creates session)
        Auth::login($user);

        return response()->json([
            'data' => $user,
        ], 201);
    }

    /**
     * Login user (SPA cookie-based authentication).
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = Auth::user();

        return response()->json([
            'data' => $user,
        ]);
    }

    /**
     * Logout user (session-based).
     */
    public function logout(Request $request): JsonResponse
    {
        // For session-based auth, we just need to flush the session
        $request->session()->flush();
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user(),
        ]);
    }
}
