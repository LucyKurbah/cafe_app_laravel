<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class PasswordResetContoller extends Controller
{
    public function index(Request $request)
    {
        $encryptedValue = $request->input('data');
        $decryptedValue = Crypt::decryptString($encryptedValue);
        $email = $this->normalizeString($decryptedValue);
        return view('modules.password')->with(compact('email'));
    }

    public function ResetPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        if($validator->fails()){
            return response()->json(400);
        }
        else {
          $sql =  User::where('email',$request->email)->update(['password'=>Hash::make($request->password)]);
          if($sql){
            return response()->json(200);
          }
          else {
            return response()->json(500);

          }
        }
    }

    public function normalizeString($str){
        $str = strip_tags($str);
        $str = preg_replace('/[\r\n\t ]+/', ' ', $str);
        $str = preg_replace('/[\"\*\/\:\<\>\?\'\|]+/', ' ', $str);
        $str = html_entity_decode( $str, ENT_QUOTES, "utf-8" );
        $str = htmlentities($str, ENT_QUOTES, "utf-8");
        $str = mb_ereg_replace("/(&)([a-z])([a-z]+;)/i", '$2', $str);
        $str = str_replace('%', '-', $str);
       return $str;
    }
}
