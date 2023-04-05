<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordMailer;


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
        $etag = md5(json_encode($user));
        return response()->json([
            'user' => $user,
            'token' => $user->createToken('secret')->plainTextToken
             ])->withHeaders([
            'Cache-Control' => 'max-age=15, public',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
            'Vary' => 'Accept-Encoding',
            'ETag' => $etag,
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
      
        return response()->json([
            'user' => Auth()->user(),
            'token' => Auth()->user()->createToken('secret')->plainTextToken
        ],200)->withHeaders([
            'Cache-Control' => 'max-age=15, public',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
            'Vary' => 'Accept-Encoding',
        ]);
    }

    public function logout()
    {
        Auth()->user()->tokens()->delete();
        return response()->json(['message' => 'Logout success'],200)
            ->withHeaders([
            'Cache-Control' => 'max-age=15, public',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
            'Vary' => 'Accept-Encoding',
            ]);
    }

    public function user()
    {
        return response()->json(['user' =>auth()->user()],200)
            ->withHeaders([
            'Cache-Control' => 'max-age=15, public',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
            'Vary' => 'Accept-Encoding',
            ]);
    }

    public function forgotPassword(Request $request){
        $sql = User::where('email','=',$request->email)->count();
        if($sql>=1){
            $email = $request->email;
            Mail::to($email)->send(new PasswordMailer($email));
            return response()->json(200)->withHeaders([
                'Cache-Control' => 'max-age=15, public',
                'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                'Vary' => 'Accept-Encoding',
                ]);
        }
        else {
            return response()->json(300)->withHeaders([
                'Cache-Control' => 'max-age=15, public',
                'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                'Vary' => 'Accept-Encoding',
                ]);
        }
    }
}
