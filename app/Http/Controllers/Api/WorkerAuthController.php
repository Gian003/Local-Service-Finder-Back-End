<?php

namespace App\Http\Controllers\Api;

use App\Models\Worker;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WorkerAuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:200',
            'last_name' => 'required|string|max:200',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed'
        ]);

        $worker = Worker::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $worker->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'registered successfully',
            'token' => $token,
            'worker' => $worker
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);

        $worker = Worker::where('email', $request->email)->first();

        if (!$worker || !Hash::check($request->password, $worker->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $worker->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'logged in successfully',
            'token' => $token,
            'worker' => $worker
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'logged out successfully',
        ]);
    }
}
