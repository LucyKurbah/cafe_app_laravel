<?php

namespace App\Http\Controllers\API;
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

class OrderController extends Controller
{
  public function ValidateTable(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'table_id' => 'required',
      'timeFrom' => 'required',
      'timeTo' => 'required',
      'bookDate' => 'required',
    ]);
    if($validator->fails()){
      return response()->json("VE")->withHeaders([
        'Cache-Control' => 'max-age=15, public',
        'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
        'Vary' => 'Accept-Encoding',
      ]);
    }
    else {
      $table_id = $this->normalizeString($request->table_id);
      $bookDate = $request->bookDate;
      $timeFrom = $request->timeFrom;
      $timeTo = $request->timeTo;
      $sql = $sql = Order::join('table_order','table_order.order_id', '=', 'public.order.id')
                    ->where([
                        ['table_order.table_id','=',$table_id],
                        ['table_order.table_date','=',$bookDate],
                        ['table_order.table_time_from', '>=', $timeFrom],
                        ['table_order.table_time_to', '<=', $timeTo]
                    ])->count();

       $etag = md5(json_encode($sq));
      if($sql==0)
      {
        return response()->json(200)->withHeaders([
          'Cache-Control' => 'max-age=15, public',
          'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
          'Vary' => 'Accept-Encoding',
          'ETag' => $etag,
          ]);
      }
      else
      {
        return response()->json(300)->withHeaders([
          'Cache-Control' => 'max-age=15, public',
          'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
          'Vary' => 'Accept-Encoding',
          'ETag' => $etag,
          ]);
      }
    }
  }

  public function ValidateConference(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'table_id' => 'required',
      'timeFrom' => 'required',
      'timeTo' => 'required',
      'bookDate' => 'required',
    ]);
    if($validator->fails()){
      return response()->json("VE")->withHeaders([
        'Cache-Control' => 'max-age=15, public',
        'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
        'Vary' => 'Accept-Encoding',
      ]);
    }
    else {
      $conference_id = $this->normalizeString($request->conference);
      $bookDate = $request->bookDate;
      $timeFrom = $request->timeFrom;
      $timeTo = $request->timeTo;
      $sql = $sql = Order::join('conference_order','conference_order.order_id', '=', 'public.order.id')
                    ->where([
                        ['conference_order.conference_order_id','=',$conference_id],
                        ['conference_order.conference_order_date','=',$bookDate],
                        ['conference_order.conference_order_time_from', '>=', $timeFrom],
                        ['conference_order.conference_order_time_to', '<=', $timeTo]
                    ])->count();

       $etag = md5(json_encode($sql));
      if($sql==0)
      {
        return response()->json(200)->withHeaders([
          'Cache-Control' => 'max-age=15, public',
          'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
          'Vary' => 'Accept-Encoding',
          'ETag' => $etag,
          ]);
      }
      else
      {
        return response()->json(300)->withHeaders([
          'Cache-Control' => 'max-age=15, public',
          'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
          'Vary' => 'Accept-Encoding',
          'ETag' => $etag,
          ]);
      }
    }
  }

  public function getCartDetails(Request $request){
    $validator = Validator::make($request->all(), [
      'user_id' => 'required',
    ]);

    if($validator->fails()){
        return response()->json("VE")->withHeaders([
          'Cache-Control' => 'max-age=15, public',
          'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
          'Vary' => 'Accept-Encoding',
      ]);
    }
    else {
      $user_id = $this->normalizeString($request->user_id);
      $sql_check = Cart::where('user_id',$user_id)->count();
      if($sql_check==0){
        $etag = md5(json_encode($sql_check));
        $code = "X";
        return response()->json($code)->withHeaders([
        'Cache-Control' => 'max-age=15, public',
        'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
        'Vary' => 'Accept-Encoding',
        'ETag' => $etag,
        ]);
      }
      else {
        $sql =  Cart::where('user_id',$user_id)->orderby('id')->get();
        $etag = md5(json_encode($sql));
        return response()->json($sql)->withHeaders([
          'Cache-Control' => 'max-age=15, public',
          'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
          'Vary' => 'Accept-Encoding',
          'ETag' => $etag,
          ]);
      }
    }
  }

  public function saveDetails(Request $request){
    $validator = Validator::make($request->all(), [
      'user_id' => 'required',
    ]);

    if($validator->fails()){
        return response()->json("VE")->withHeaders([
          'Cache-Control' => 'max-age=15, public',
          'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
          'Vary' => 'Accept-Encoding',
      ]);
    }
    else {
      $user_id = $this->normalizeString($request->user_id);
     
      $tax = null; //later
      $hint = null; //later
      $order_id = Order::insertGetId([
        'user_id' => $user_id, 'order_status_id'=>1,
        'tax'=>$tax , 'hint'=>$hint, 'active'=>true,
        'created_at' => now(),
        'updated_at' => now(),
      ]);
      $cartItems = Cart::where('user_id', $user_id)->get();
    
      $groupedItems = [];
    
      foreach ($cartItems as $cartItem) {
          $groupedItems[$cartItem->flag][] = $cartItem;
      }
      
      foreach ($groupedItems as $flag => $items) {
          try {
              DB::beginTransaction();
              switch ($flag) {
                  case 'F': // Food items
                      $foodOrders = [];
                      foreach ($items as $item) {
                          $foodOrders[] = [
                              'food_id' => $item->food_id,
                              'quantity' => $item->food_quantity,
                              'food_price' => $item->food_price,
                              'order_id' => $order_id,
                          ];
                      }
                      FoodOrder::insert($foodOrders);
                      break;

                  case 'I': // Item orders
                      $itemOrders = [];
                      foreach ($items as $item) {
                          $itemOrders[] = [
                              'item_id' => $item->item_id,
                              'quantity' => $item->item_quantity,
                              'item_price' => $item->item_price,
                              'order_id' => $order_id,
                          ];
                      }
                      ItemOrder::insert($itemOrders);
                      break;

                  case 'T': // Table orders
                      $tableOrders = [];
                      foreach ($items as $item) {
                          $tableOrders[] = [
                              'table_id' => $item->table_id,
                              'table_price' => $item->table_price,
                              'table_date' => $item->table_date,
                              'table_time_from' => $item->table_date . ' ' . $item->table_time_from,
                              'table_time_to' => $item->table_date . ' ' . $item->table_time_to,
                              'order_id' => $order_id,
                          ];
                      }
                      TableOrder::insert($tableOrders);
                      break;

                  case 'C': // Conference orders
                      $conferenceOrders = [];
                      foreach ($items as $item) {
                          $conferenceOrders[] = [
                              'conference_id' => $item->conference_id,
                              'conference_price' => $item->conference_price,
                              'conference_date' => $item->conference_date,
                              'conference_time_from' => $item->conference_date . ' ' . $item->conference_time_from,
                              'conference_time_to' => $item->conference_date . ' ' . $item->conference_time_to,
                              'order_id' => $order_id,
                          ];
                      }
                      ConferenceOrder::insert($conferenceOrders);
                      break;

                  default:
                      throw new Exception('Unknown flag: ' . $flag);
              }
              // DB::commit();
          } catch (Exception $e) {
              DB::rollback();
              throw $e;
          }
      }
      $sql_remove = Cart::where('user_id',$user_id)->delete(); 
      $etag = md5(json_encode($sql_remove));
      $code = "Y";
      return response()->json($code)->withHeaders([
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
