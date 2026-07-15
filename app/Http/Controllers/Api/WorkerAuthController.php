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
        // Validation errors (e.g. duplicate email) must propagate to
        // Laravel's default handler for a proper 422 — catching
        // ValidationException below as a generic \Exception would report a
        // client input error as a 500.
        $request->validate([
            'first_name' => 'required|string|max:200',
            'last_name' => 'required|string|max:200',
            'email' => 'required|email|unique:workers,email',
            'password' => 'required|min:8|confirmed'
        ]);

        try {
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
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'Database error occurred',
                'error' => 'Failed to register worker'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);

        try {
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
            ], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'message' => 'Database error occurred',
                'error' => 'Failed to login'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'logged out successfully',
        ]);
    }
}
