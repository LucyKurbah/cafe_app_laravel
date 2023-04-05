<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;
use DB;

class ItemController extends Controller
{
    public function getItems(Request $req)
    {
        $user_id = $req->user_id;
        $items=Item::leftJoin('cart', function($join) use ($user_id) {
                            $join->on('cart.item_id', '=', 'item.id')
                                ->where('cart.user_id', '=', $user_id);
                        })
                        ->select('item.*', DB::raw('COALESCE(cart.item_quantity, 0) AS quantity'))
                        ->where('item.active', true)
                        ->get();
        $ip = $req->ip();
        $etag = md5(json_encode($items));
        foreach($items AS $item){
            $item->path_file =   $req->getSchemeAndHttpHost() .'/images/'.$item->path_file;
        }
        return response()->json($items)->withHeaders([
            'Cache-Control' => 'max-age=15, public',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
            'Vary' => 'Accept-Encoding',
            'ETag' => $etag,
        ]);
    }
}
