<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Food;
use App\Models\FoodOrder;
use App\Models\Cart;
use App\Models\Conference;
use App\Models\ConferenceOrder;
use App\Models\Item;
use App\Models\ItemOrder;
use App\Models\Table;
use App\Models\TableOrder;
use App\Models\Order;
use Illuminate\Support\Facades\Storage;
use Validator;
use DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request){
        $role   = Auth::user()->role;
        return view('modules.order')->with(compact('role'));
    }

    public function ViewContent(Request $request){
        $sql = Order::join('users', 'users.id', '=', 'public.order.user_id')
        ->join('order_status', 'order_status.id', '=', 'public.order.order_status_id')
        ->select('public.order.id','users.name','order_status.status','public.order.tax','public.order.active','public.order.updated_at')->orderByDesc('public.order.id')->get();
        return response()->json($sql)->withHeaders([
            'Cache-Control' => 'max-age=15, public',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
        ]);
    }

    public function ShowData(Request $request){
        $order_id = $this->normalizeString($request->order_id);
        $sql1 = Order::join('users', 'users.id', '=', 'public.order.user_id')
                ->join('order_status', 'order_status.id', '=', 'public.order.order_status_id')
                ->where('public.order.id',$order_id)
                ->select('public.order.id','users.name','order_status.status','public.order.tax','public.order.active','public.order.updated_at','public.order.created_at','public.order.active','users.phone_no')->get();

        $sql2 = FoodOrder::join('food','food.id','=','food_order.food_id')
                ->where('food_order.order_id',$order_id)
                ->select('food_order.id','food.food_name','food.price as food_price','food_order.quantity')->get();

        $sql3 = ItemOrder::join('item','item.id','=','item_order.item_id')
                ->where('item_order.order_id',$order_id)
                ->select('item.item_name','item.price as item_price','item_order.quantity')->get();

        $sql4 = TableOrder::join('public.table','public.table.id','=','table_order.table_id')
                ->where('table_order.order_id',$order_id)
                ->select('public.table.table_name','public.table.price as table_price','table_order.table_time_from','table_order.table_time_to')->get();

         $sql5 = ConferenceOrder::join('public.conference','public.conference.id','=','conference_order.conference_id')
                ->where('conference_order.order_id',$order_id)
                ->select('public.conference.conference_name','public.conference.price as conference_price','public.conference_order.conference_time_from','public.conference_order.conference_time_to')->get();
    
        $data = array($sql1, $sql2, $sql3,$sql4,$sql5);

        return response()->json($data)->withHeaders([
                'Cache-Control' => 'max-age=15, public',
                'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
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
