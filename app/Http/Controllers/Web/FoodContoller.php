<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class FoodController extends Controller
{
    public function getItems(Request $req)
    {
        $items=Food::leftJoin('cart', 'cart.item_id', '=', 'food.id')
                        ->select('food.*', 'cart.item_quantity')
                        ->where('food.active', true)
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