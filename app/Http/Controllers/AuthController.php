<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
    public function register(RegisterRequest $req)
    {
        $validated = $req->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response([
            'user' => $user,
            'token' => $user->createToken('secret')->plainTextToken
        ]);
    }

    public function login(Request $req)
    {
        $attrs = $req->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        if(!Auth::attempt($attrs)){
            return response([
                'message' => 'Invalid credentials'
            ], 403);
        }
      
        return response([
            'user' => Auth()->user(),
            'token' => Auth()->user()->createToken('secret')->plainTextToken
        ],200);
    }

    public function logout()
    {
        Auth()->user()->tokens()->delete();
        return response([
            'message' => 'Logout success'
        ],200);
    }

    public function user()
    {
        return response([
            'user' =>auth()->user()
        ],200);
    }
}
