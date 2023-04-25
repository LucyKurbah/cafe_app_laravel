<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class ProfileController extends Controller
{
    public function getProfile(Request $request)
    {
        if(isset($request->user_id))
        {
            $profile = User::select('id','name','email','phone_no','document_id')
                        ->where('id','=',$request->user_id)
                        ->get();
            $etag = md5(json_encode($profile));
            return response()->json($profile)->withHeaders([
                            'Cache-Control' => 'max-age=15, public',
                            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                            'Vary' => 'Accept-Encoding',
                            'ETag' => $etag,
                        ]);
        }
        else
        {
            $profile = "X";
            return response()->json($profile)->withHeaders([
                'Cache-Control' => 'max-age=15, public',
                'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                'Vary' => 'Accept-Encoding',
            ]);
        }
       
    }
}
