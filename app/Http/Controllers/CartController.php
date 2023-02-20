<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Cart;
use Illuminate\Support\Facades\Storage;
use Validator;
use DB;

class CartController extends Controller
{
    public function getCart(Request $request)
    {
       
        if($request->isMethod('post')){
            $data = $request->all();

            $user_id = (int)$data['user_id'];
            $cartItems = Cart::SELECT('*')
                        ->join('items as it','it.id','item_id')
                        ->where('user_id', $user_id)
                        ->get();

            foreach($cartItems AS $item){
                            // dd($item->item_img_loc);
                            // foreach($item AS $index => $image){
                              $item->item_img_loc ='http://10.179.2.187:8000'.Storage::url($item->item_img_loc);
                            // }
            }
            $cartTotal = Cart::where('user_id', $user_id)
            ->sum('item_price');
                   
            return $cartItems;
        }
        
    }
    
    public function cartAdd(Request $request)
    {
        if($request->isMethod('post')){
            $data = $request->all();

            //Check Item Stock of Items
            $getItemStock = Item::getItemStock($data['id']);
            
            if($getItemStock)
            {
                $checkCartItem = Cart::where(['item_id' => $data['id'],
                                            'user_id' => $data['user_id']
                                    ])
                                    ->first();
         
                if($checkCartItem)
                {
                    $cart = Cart::whereId($checkCartItem['id'])->update(['quantity' => $checkCartItem['quantity']+1]);
                    if($cart)
                        return 200;
                    else 
                        return 500;
                }
                    
                else
                {
                    try {
                        $cart = new Cart;
                        $cart->item_id = $data['id'];
                        $cart->user_id = $data['user_id'];
                        $cart->item_price = $data['item_price'];
                        $cart->quantity = $data['quantity'];
                        $cart->save();
                        return 200;
                    } catch (\Throwable $th) {
                        return 500;
                    }
                   
                }
            }
        }
    }

    public function cartRemove(Request $request)
    {
        
        if($request->isMethod('post')){
            $data = $request->all();
           
            //Check Item Stock of Items
         
                $checkCartItem = Cart::where(['item_id' => $data['item_id'],
                                            'user_id' => $data['user_id']
                                    ])
                                    ->first();

                if($checkCartItem)
                {
                    if ($checkCartItem['quantity'] > 1) {
                        $cart = Cart::whereId($checkCartItem['id'])->update(['quantity' => $checkCartItem['quantity']-1]);
                    }
                    else
                    {
                        $cart=Cart::where('id',$checkCartItem['id'])->delete(); 
                    }
                
                    if($cart)
                        return 200;
                    else 
                        return 100;
                }
                    
               return 400;
            
        }
    }

    public function cartTotal(Request $request)
    {
        if($request->isMethod('post')){
            $data = $request->all();
            
            $user_id = (int)$data['user_id'];
            $cartTotal = DB::Select('select sum(quantity * item_price) as totalPrice
                                    from cart
                                    WHERE cart.user_id = ?', 
                                    array($user_id));
                   
            return $cartTotal;
        }
    }
}
