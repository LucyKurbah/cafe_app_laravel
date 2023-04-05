<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Food;
use App\Models\Cart;
use Illuminate\Support\Facades\Storage;
use Validator;
use DB;

class CartController extends Controller
{
    public function getCart(Request $request)
    {  
        $data = $request->all();
        $user_id = $request->has('user_id')? (int)$data['user_id']:'';  
      
        
        $foods = Cart::select('food_name as item_name', 'food_price as item_price' ,'flag', 'food_id as item_id','food.path_file','food.featured','food.active','food_quantity as item_quantity')
                ->join('food', 'cart.food_id', '=', 'food.id')
                ->where('user_id', $user_id);
        
        $tables = Cart::select('table_name as item_name', 'table_price as item_price','flag', 'table_id as item_id','table.path_file',DB::raw('true as featured'),DB::raw('true as active'),DB::raw('1 as item_quantity'))
                ->join('table', 'cart.table_id', '=', 'table.id')
                ->where('user_id', $user_id);
        
        $items = Cart::select('item_name', 'item_price','flag', 'item_id','item.path_file','item.featured','item.active','item_quantity')
                ->join('item', 'cart.item_id', '=', 'item.id')
                ->where('user_id', $user_id);
        
        $results = $foods->union($tables)
                ->union($items)
                ->get();
        
        // $totalPrice = $results->sum('food_price') + $results->sum('table_price') + $results->sum('item_price');
        
        $etag = md5(json_encode($results));
        foreach($results AS $item){
                $item->path_file =   $request->getSchemeAndHttpHost() .'/images/'.$item->path_file;
        }
        return response()->json($results)->withHeaders([
                'Cache-Control' => 'max-age=15, public',
                'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                'Vary' => 'Accept-Encoding',
                'ETag' => $etag,
        ]);
    }

    public function getCartItems()
    {
                
        $foods = Cart::select('food_name', 'food_price' ,'flag', 'food_id','food.path_file','food_quantity')
        ->join('food', 'cart.food_id', '=', 'food.id')
        
        ->where('user_id', $userId);

        $tables = Cart::select('table_name', 'table_price','flag', 'table_id','table.path_file',DB::raw('1 as table_quantity'))
        ->join('table', 'cart.table_id', '=', 'table.id')
        ->where('user_id', $userId);

        $items = Cart::select('item_name', 'item_price','flag', 'item_id','item.path_file','item_quantity')
        ->join('item', 'cart.item_id', '=', 'item.id')
        ->where('user_id', $userId);

        $results = $foods->union($tables)
        ->union($items)
        ->get();

        $totalPrice = $results->sum('food_price') + $results->sum('table_price') + $results->sum('item_price');
    }
    
    public function cartAdd(Request $request)
    {
            $data = $request->all();
            if(isset($request->food_id)){
                $checkCartItemFood = Cart::where(['food_id' => $data['food_id'],'user_id' => $data['user_id']])->first();
                if($checkCartItemFood){
                    $cart = Cart::where('food_id',$checkCartItemFood->food_id)
                                ->update(['food_quantity' => $checkCartItemFood->food_quantity+1]);
                    $etag = md5(json_encode($cart));
                    if($cart)
                        return response()->json(200)->withHeaders([
                            'Cache-Control' => 'max-age=15, public',
                            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                            'Vary' => 'Accept-Encoding',
                            'ETag' => $etag,
                        ]);
                    else  {
                        return response()->json(500)->withHeaders([
                            'Cache-Control' => 'max-age=15, public',
                            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                            'Vary' => 'Accept-Encoding',
                            'ETag' => $etag,
                        ]);     
                    }            
                }
                else {
                    $etag = md5(json_encode($checkCartItemFood));
                        try {
                            $cart = new Cart;
                            $cart->food_id = $data['food_id'];
                            $cart->user_id = $data['user_id'];
                            $cart->food_price = $data['food_price'];
                            $cart->food_quantity = $data['food_quantity'];
                            $cart->flag= $data['flag'];
                            $cart->save();
                            return response()->json(200)->withHeaders([
                                'Cache-Control' => 'max-age=15, public',
                                'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                                'Vary' => 'Accept-Encoding',
                                'ETag' => $etag,
                            ]);
                        } catch (\Throwable $th) {
                            return response()->json(500)->withHeaders([
                                'Cache-Control' => 'max-age=15, public',
                                'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                                'Vary' => 'Accept-Encoding',
                                'ETag' => $etag,
                            ]);
                        }
                }
            }
            
            else if(isset($request->item_id)){
                $checkCartItem = Cart::where(['item_id' => $data['item_id'],'user_id' => $data['user_id']])->first();
                if($checkCartItem){
                    $cart = Cart::where('item_id',$checkCartItem->item_id)
                    ->update(['item_quantity' => $checkCartItem->item_quantity+1]);                   
                     $etag = md5(json_encode($cart));
                    if($cart)
                        return response()->json(200)->withHeaders([
                            'Cache-Control' => 'max-age=15, public',
                            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                            'Vary' => 'Accept-Encoding',
                            'ETag' => $etag,
                        ]);
                    else  {
                        return response()->json(500)->withHeaders([
                            'Cache-Control' => 'max-age=15, public',
                            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                            'Vary' => 'Accept-Encoding',
                            'ETag' => $etag,
                        ]);     
                    }            
                }
                else {
                    try {
                        $cart = new Cart;
                        $cart->item_id = $data['item_id'];
                        $cart->user_id = $data['user_id'];
                        $cart->item_price = $data['item_price'];
                        $cart->item_quantity = $data['item_quantity'];
                        $cart->flag= $data['flag'];
                        $cart->save();
                        $etag = md5(json_encode($cart));
                        return response()->json(200)->withHeaders([
                            'Cache-Control' => 'max-age=15, public',
                            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                            'Vary' => 'Accept-Encoding',
                            'ETag' => $etag,
                        ]);
                    } catch (\Throwable $th) {
                        return response()->json(500)->withHeaders([
                            'Cache-Control' => 'max-age=15, public',
                            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                            'Vary' => 'Accept-Encoding',
                            'ETag' => $etag,
                        ]);
                    }
                }
            }
            else if(isset($request->table_id)){
                $checkCartTable = Cart::where(['table_id' => $data['table_id'],'user_id' => $data['user_id']])->count();
                $checkCartConference = Cart::where(['user_id' => $data['user_id']])->count('conference_id');
                $etag = md5(json_encode($checkCartConference));
                if($checkCartTable==0 && $checkCartConference==0){
                    try {
                        $cart = new Cart;
                        $cart->table_id = $data['table_id'];
                        $cart->user_id = $data['user_id'];
                        $cart->table_price = $data['table_price'];
                        $cart->table_date = $data['table_date'];
                        $cart->table_time_from= $data['table_time_from'];
                        $cart->table_time_to= $data['table_time_to'];
                        $cart->flag= $data['flag'];
                        $cart->save();
                        $etag = md5(json_encode($cart));
                        return response()->json(200)->withHeaders([
                            'Cache-Control' => 'max-age=15, public',
                            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                            'Vary' => 'Accept-Encoding',
                            'ETag' => $etag,
                        ]);
                    } catch (\Throwable $th) {
                        return response()->json(500)->withHeaders([
                            'Cache-Control' => 'max-age=15, public',
                            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                            'Vary' => 'Accept-Encoding',
                            'ETag' => $etag,
                        ]);
                    }
                }
                else {
                    $code = "X";
                    return response()->json($code)->withHeaders([
                        'Cache-Control' => 'max-age=15, public',
                        'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                        'Vary' => 'Accept-Encoding',
                    ]);
                }
            }
            else if(isset($request->conference_id)){
                $checkCartTable = Cart::where(['user_id' => $data['user_id']])->count('table_id');
                $checkCartConference = Cart::where(['conference_id' => $data['conference_id'],'user_id' => $data['user_id']])->count();
                $etag = md5(json_encode($checkCartConference));
                if($checkCartTable==0 && $checkCartConference==0){
                    try {
                        $cart = new Cart;
                        $cart->conference_id = $data['conference_id'];
                        $cart->user_id = $data['user_id'];
                        $cart->conference_price = $data['conference_price'];
                        $cart->conference_date = $data['conference_date'];
                        $cart->conference_time_from= $data['conference_time_from'];
                        $cart->conference_time_to= $data['conference_time_to'];
                        $cart->flag= $data['flag'];
                        $cart->save();
                        $etag = md5(json_encode($cart));
                        return response()->json(200)->withHeaders([
                            'Cache-Control' => 'max-age=15, public',
                            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                            'Vary' => 'Accept-Encoding',
                            'ETag' => $etag,
                        ]);
                    } catch (\Throwable $th) {
                        return response()->json(500)->withHeaders([
                            'Cache-Control' => 'max-age=15, public',
                            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                            'Vary' => 'Accept-Encoding',
                            'ETag' => $etag,
                        ]);
                    }
                }
                else {
                    $code = "X";
                    return response()->json($code)->withHeaders([
                        'Cache-Control' => 'max-age=15, public',
                        'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                        'Vary' => 'Accept-Encoding',
                    ]);
                }
            }
    }

    public function cartRemove(Request $request)
    {
        $data = $request->all();    
        if(isset($request->food_id)){
            $checkCartItem = Cart::where(['food_id' => $data['food_id'],'user_id' => $data['user_id']])->first();
            if($checkCartItem){
                    if ($checkCartItem['food_quantity'] > 1) {
                        $cart = Cart::where('food_id',$checkCartItem->food_id)->update(['food_quantity' => $checkCartItem['food_quantity']-1]);
                    }
                    else{
                        $cart=Cart::where('id',$checkCartItem['id'])->delete(); 
                    }
                    if($cart){
                        $etag = md5(json_encode($cart));
                        return response()->json(200)->withHeaders([
                            'Cache-Control' => 'max-age=15, public',
                            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                            'Vary' => 'Accept-Encoding',
                            'ETag' => $etag,
                        ]);
                }
                    else {
                        $etag = md5(json_encode($cart));
                        return response()->json(100)->withHeaders([
                            'Cache-Control' => 'max-age=15, public',
                            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                            'Vary' => 'Accept-Encoding',
                            'ETag' => $etag,
                        ]);
                    }      
                }   
                else {
                    return response()->json(400)->withHeaders([
                        'Cache-Control' => 'max-age=15, public',
                        'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                        'Vary' => 'Accept-Encoding',
                    ]);
                } 
        }    
        
        else if(isset($request->item_id)){
            $checkCartItem = Cart::where(['item_id' => $data['item_id'],'user_id' => $data['user_id']])->first();
            if($checkCartItem){
                    if ($checkCartItem['item_quantity'] > 1) {
                        $cart = Cart::where('item_id',$checkCartItem->item_id)->update(['item_quantity' => $checkCartItem['item_quantity']-1]);
                    }
                    else{
                        $cart=Cart::where('id',$checkCartItem['id'])->delete(); 
                    }
                    if($cart){
                        $etag = md5(json_encode($cart));
                        return response()->json(200)->withHeaders([
                            'Cache-Control' => 'max-age=15, public',
                            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                            'Vary' => 'Accept-Encoding',
                            'ETag' => $etag,
                        ]);
                }
                    else {
                        $etag = md5(json_encode($cart));
                        return response()->json(100)->withHeaders([
                            'Cache-Control' => 'max-age=15, public',
                            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                            'Vary' => 'Accept-Encoding',
                            'ETag' => $etag,
                        ]);
                    }      
                }   
                else {
                    return response()->json(400)->withHeaders([
                        'Cache-Control' => 'max-age=15, public',
                        'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                        'Vary' => 'Accept-Encoding',
                    ]);
                } 
        }  
        else if(isset($request->table_id)){
            $checkCartItem = Cart::where(['table_id' => $data['table_id'],'user_id' => $data['user_id']])->count();
            if($checkCartItem>=1){
                $cart=Cart::where('table_id',$data['table_id'])->delete(); 
                if($cart){
                    $etag = md5(json_encode($cart));
                    return response()->json(200)->withHeaders([
                        'Cache-Control' => 'max-age=15, public',
                        'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                        'Vary' => 'Accept-Encoding',
                        'ETag' => $etag,
                    ]);
            }
                else {
                    $etag = md5(json_encode($cart));
                    return response()->json(100)->withHeaders([
                        'Cache-Control' => 'max-age=15, public',
                        'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                        'Vary' => 'Accept-Encoding',
                        'ETag' => $etag,
                    ]);
                }     
            }
            else {
                return response()->json(400)->withHeaders([
                    'Cache-Control' => 'max-age=15, public',
                    'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                    'Vary' => 'Accept-Encoding',
                ]);
            }
        }

        else if(isset($request->conference_id)){
            $checkCartItem = Cart::where(['conference_id' => $data['conference_id'],'user_id' => $data['user_id']])->count();
            if($checkCartItem>=1){
                $cart=Cart::where('conference_id',$data['conference_id'])->delete(); 
                if($cart){
                    $etag = md5(json_encode($cart));
                    return response()->json(200)->withHeaders([
                        'Cache-Control' => 'max-age=15, public',
                        'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                        'Vary' => 'Accept-Encoding',
                        'ETag' => $etag,
                    ]);
            }
                else {
                    $etag = md5(json_encode($cart));
                    return response()->json(100)->withHeaders([
                        'Cache-Control' => 'max-age=15, public',
                        'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                        'Vary' => 'Accept-Encoding',
                        'ETag' => $etag,
                    ]);
                }     
            }
            else {
                return response()->json(400)->withHeaders([
                    'Cache-Control' => 'max-age=15, public',
                    'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                    'Vary' => 'Accept-Encoding',
                ]);
            }
        }
        
    }

    public function cartTotal(Request $request)
    {
            $data = $request->all();
            $sum = 0.0;
            $user_id = (int)$data['user_id'];
            $getcartItemTotal = DB::Select('select sum(item_quantity * item_price) as ItemtotalPrice from cart 
                                        WHERE cart.user_id= :user_id', array(':user_id'=>$user_id));
            $getcartFoodTotal = DB::Select('select sum(food_quantity * food_price) as FoodtotalPrice from cart 
                                        WHERE cart.user_id= :user_id', array(':user_id'=>$user_id));
            $getcartTableTotal = DB::Select('select sum(table_price) as TabletotalPrice  from cart 
                                        WHERE cart.user_id= :user_id', array(':user_id'=>$user_id));
            $getcartConferenceTotal = DB::Select('select sum(conference_price) as ConferencePrice  from cart 
                                        WHERE cart.user_id= :user_id', array(':user_id'=>$user_id));
            $sum = $getcartItemTotal[0]->itemtotalprice + $getcartFoodTotal[0]->foodtotalprice +  $getcartTableTotal[0]->tabletotalprice + $getcartConferenceTotal[0]->conferenceprice;
            $data = array($getcartItemTotal, $getcartFoodTotal, $getcartTableTotal,$getcartConferenceTotal);
            $etag = md5(json_encode($data));
            return response()->json($sum)->withHeaders([
                'Cache-Control' => 'max-age=15, public',
                'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
                'Vary' => 'Accept-Encoding',
                'ETag' => $etag,
            ]);
    }
}
