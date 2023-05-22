<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|exists:users,email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {

            $user = Auth::user();

            $token = $user->createToken('API Token')->accessToken;

            return response()->success($token, "Successfully Authenticated", 200);

        }

        return response()->error("Unauthorized", 401);
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|unique:users',
            'password' => 'required|confirmed',
            'name' => 'required|min:5|max:30'
        ]);

        try {

            DB::beginTransaction();

            User::create([
                'name' => $request->name,
                'password' => Hash::make($request->password),
                'email' => $request->email,
            ]);

            DB::commit();

        } catch (\Exception $e) {

            DB::rollback();

            Log::error($e->getMessage());

            return response()->error($e->getMessage(), 500);
        }

        return response()->success(null, "Successfully Registered", 200);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout successful']);
    }
}
