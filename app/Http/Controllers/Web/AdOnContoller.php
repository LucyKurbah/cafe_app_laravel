<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
Use Exception;
use App\Models\Item;
use Redirect;

class AdOnContoller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $role   = Auth::user()->role;
        return view('modules.ad-on')->with(compact('role'));
    }

    public function ViewContent(Request $request){
        $sql = Item::select('id','item_name','price','discount_price','featured','active')->get();
        return response()->json($sql)->withHeaders([
            'Cache-Control' => 'max-age=15, public',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
        ]);
    }

    public function StoreData(Request $request){
        $validator = Validator::make($request->all(), [
            'item_name' => 'required',
            'desc' => 'required',
            'price' => 'required',
            'discount'=>'required',
            'item_img' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if($validator->fails()){
            return response()->json(["flag"=>"VE"]);
        }
        else {
            if(isset($request->id)){
               $sql_count = Item::where('id',$request->id)->count();
               if($sql_count>0){
                try{
                    DB::beginTransaction();
                    $image = $request->file('item_img');
                    $imageName = time().'.'.$image->getClientOriginalExtension();
                    $image->move(public_path('images'), $imageName);
                        Item::whereId($request->id)->update([
                            'item_name' => $this->normalizeString($request->item_name),
                            'price'=> $this->normalizeString($request->price),
                            'discount_price' => $this->normalizeString($request->discount),
                            'description'=> $this->normalizeString($request->desc),
                            'path_file'=>$this->normalizeString($imageName),
                            'featured'=>$request->has('feature'),
                            'active'=>$request->has('active'),
                        ]);
                    DB::commit();
                    return response()->json(["flag"=>"YY"]);
                }
                catch(\Exception $e){
                    DB::rollback();
                    return response()->json(["flag"=>"NN"]);
                }
               }
               else {
                return response()->json(["flag"=>"NN"]);
               }
            }
            else {
            try{
                    DB::beginTransaction();
                    $image = $request->file('item_img');
                    $imageName = time().'.'.$image->getClientOriginalExtension();
                    $image->move(public_path('images'), $imageName);
                    $Item = new Item();
                    $Item->item_name = $this->normalizeString($request->item_name);
                    $Item->price = $this->normalizeString($request->price);
                    $Item->discount_price=$this->normalizeString($request->discount);
                    $Item->description = $this->normalizeString($request->desc);
                    $Item->path_file = $this->normalizeString($imageName);
                    $Item->featured = $request->has('feature');
                    $Item->active = $request->has('active');
                    $Item->save();
                    DB::commit();
                    return response()->json(["flag"=>"Y"]);
                    }
                    catch(\Exception $e){
                        DB::rollback();
                        return response()->json(["flag"=>"N"]);
                    }
            }
        }
    }

    public function ShowData(Request $request) {
        $sql = Item::select('id','item_name','discount_price','price','description','featured','active','path_file')->where('id',$request->item_id)->get();
        foreach ($sql as $item){
            $item->path_file =   $request->getSchemeAndHttpHost() .'/images/'.$item->path_file;
        }
        return response()->json($sql);
    }

    public function DeleteData(Request $request){
        try{
            DB::beginTransaction();
            $sql = Item::where('id',$request->item_id)->delete();
            DB::commit();
            return response()->json(["flag"=>"Y"]);
        }
        catch(\Exception $e){
            DB::rollback();
            return response()->json(["flag"=>"N"]);
        }
    }

    public function ChangeActive(Request $request){
        $sql_check = Item::select('active')->whereId($request->item_id)->get();
        if(($sql_check[0]->active==true)){
            $sql = Item::whereId($request->item_id)->update(['active'=>false]);
            return response()->json(["flag"=>"Y"]);
        }
        else {
            $sql = Item::whereId($request->item_id)->update(['active'=>true]);
            return response()->json(["flag"=>"N"]);
        }
    }

    public function ChangeFeature(Request $request){
        $sql_check = Item::select('featured')->whereId($request->item_id)->get();
        if(($sql_check[0]->featured==true)){
            $sql = Item::whereId($request->item_id)->update(['featured'=>false]);
            return response()->json(["flag"=>"Y"]);
        }
        else {
            $sql = Item::whereId($request->item_id)->update(['featured'=>true]);
            return response()->json(["flag"=>"N"]);
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
