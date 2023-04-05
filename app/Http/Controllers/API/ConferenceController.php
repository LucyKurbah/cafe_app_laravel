<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Conference;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;
use DB;

class ConferenceController extends Controller
{
   public function getConferenceDetails(Request $request){
        $conf=Conference::all()->where('active',true);
        $ip = $request->ip();
        $etag = md5(json_encode($conf));
        foreach($conf AS $item){
            $item->path_file =   $request->getSchemeAndHttpHost() .'/conference/'.$item->path_file;
        }
        return response()->json($conf)->withHeaders([
            'Cache-Control' => 'max-age=15, public',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
            'Vary' => 'Accept-Encoding',
            'ETag' => $etag,
        ]);
    }

    public function checkConference(Request $request){
        $conference_id   = $this->normalizeString($request->conference_id);
        $conference_date = $this->normalizeString($request->conference_date);
        $conference_time_from = $this->normalizeString($request->conference_time_from);
        $conference_time_to = $this->normalizeString($request->conference_time_to);
        $timestamp_from = strtotime($conference_time_from);

        dd($timestamp_from);
        // $sql = Order::where([['conference_id',$conference_id],
        //                     ['conference_date',$conference_date],
        //                      ['conference_time_from',$conference_time_from],
        //                      ['conference_time_to',$conference_time_to]])
        //                      ->count();
        $sql=Order::leftJoin('conference_order', function($join) use ($conference_id) {
                                $join->on('conference_order.order_id', '=', 'order.id')
                                    ->where('conference_order.conference_id', '=', $conference_id);
                            })
                            -> where([['conference_order.conference_id',$conference_id],
                            ['conference_order.conference_date',$conference_date],
                             ['conference_order.conference_time_from',$conference_time_from],
                             ['conference_order.conference_time_to',$conference_time_to]])
                            ->count();
        dd($sql);
        $etag = md5(json_encode($sql));
        if($sql==0){
            $message = "Y";
            return response()->json($message)->withHeaders([
                'Cache-Control' => 'max-age=15, public',
                'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                'Vary' => 'Accept-Encoding',
                'ETag' => $etag,
            ]);
        }
        else {
            $message = "X";
            return response()->json($message)->withHeaders([
                'Cache-Control' => 'max-age=15, public',
                'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                'Vary' => 'Accept-Encoding',
                'ETag' => $etag,
            ]);
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
