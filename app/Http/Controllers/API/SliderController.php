<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class SliderController extends Controller
{
    public function getSliders(Request $req)
    {
        $ip = $req->ip();
        $sql = Slider::all()->where('active',true);
        $etag = md5(json_encode($sql));
        foreach($sql AS $key){       
              $key->path_file =   $req->getSchemeAndHttpHost() .'/sliders/'.$key->path_file;
        }
        return response()->json($sql)->withHeaders([
            'Cache-Control' => 'max-age=15, public',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
            'Vary' => 'Accept-Encoding',
            'ETag' => $etag,
        ]);
    } 
}
