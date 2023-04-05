<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Conference;
use App\Models\FAQ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class FAQController extends Controller
{
    public function getFAQ(Request $request){
        $sql = FAQ::select('id','question','answer')->get();
        $etag = md5(json_encode($sql));
        return response()->json($sql)->withHeaders([
            'Cache-Control' => 'max-age=15, public',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
            'Vary' => 'Accept-Encoding',
            'ETag' => $etag,
        ]);
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
