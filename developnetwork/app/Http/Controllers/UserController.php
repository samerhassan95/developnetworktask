<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;


class UserController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:15|unique:users',
                'password' => 'required|string|min:8',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Failed', ['errors' => $e->errors()]);
            return response()->json(['errors' => $e->errors()], 422);
        }

        Log::info('After Validation');

        // Proceed with user creation if validation passes
        $user = User::create([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'verification_code' => rand(100000, 999999),
        ]);

        Log::info('User Created', [
            'user' => $user,
            'verification_code' => $user->verification_code,
        ]);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('phone_number', 'password'))) {
            return response()->json(['message' => 'Invalid login details'], 401);
        }

        $user = User::where('phone_number', $request['phone_number'])->firstOrFail();

        if (!$user->is_verified) {
            return response()->json(['message' => 'Account not verified'], 403);
        }

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken,
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'verification_code' => 'required|string|size:6',
        ]);

        $user = User::where('phone_number', $request->phone_number)
            ->where('verification_code', $request->verification_code)
            ->first();

        if ($user) {
            $user->is_verified = true;
            $user->verification_code = null; // Clear verification code after successful verification
            $user->save();

            return response()->json(['message' => 'Account verified successfully']);
        }

        return response()->json(['message' => 'Invalid verification code'], 400);
    }
}
