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
use App\Models\Slider;
use Redirect;

class SliderController extends Controller
{
    public function index()
    {
        $role   = Auth::user()->role;
        return view('modules.slider')->with(compact('role'));
    }

    public function ViewContent(Request $request){
        $sql = Slider::select('id','slider_name','path_file','active')->get();
        return response()->json($sql)->withHeaders([
            'Cache-Control' => 'max-age=15, public',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
        ]);
    }

    public function StoreData(Request $request){
        $validator = Validator::make($request->all(), [
            'slider_name' => 'required',
            'slider_img' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if($validator->fails()){
            return response()->json(["flag"=>"VE"]);
        }
        else {
            if(isset($request->id)){
               $sql_count = Slider::where('id',$request->id)->count();
               if($sql_count>0){
                try{
                    DB::beginTransaction();
                    $image = $request->file('slider_img');
                    $imageName = time().'.'.$image->getClientOriginalExtension();
                    $image->move(public_path('sliders'), $imageName);
                        Slider::whereId($request->id)->update([
                            'slider_name' => $this->normalizeString($request->slider_name),
                            'path_file'=>$this->normalizeString($imageName),
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
                    $image = $request->file('slider_img');
                    $imageName = time().'.'.$image->getClientOriginalExtension();
                    $image->move(public_path('sliders'), $imageName);
                    $Item = new Slider();
                    $Item->slider_name = $this->normalizeString($request->slider_name);
                    $Item->path_file = $this->normalizeString($imageName);
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
        $sql = Slider::select('id','slider_name','active','path_file')->where('id',$request->slider_id)->get();
        foreach ($sql as $item){
            $item->path_file =   $request->getSchemeAndHttpHost() .'/sliders/'.$item->path_file;
        }
        return response()->json($sql);
    }

    public function DeleteData(Request $request){
        try{
            DB::beginTransaction();
            $sql = Slider::where('id',$request->slider_id)->delete();
            DB::commit();
            return response()->json(["flag"=>"Y"]);
        }
        catch(\Exception $e){
            DB::rollback();
            return response()->json(["flag"=>"N"]);
        }
    }

    public function ChangeActive(Request $request){
        $sql_check = Slider::select('active')->whereId($request->slider_id)->get();
        if(($sql_check[0]->active==true)){
            $sql = Slider::whereId($request->slider_id)->update(['active'=>false]);
            return response()->json(["flag"=>"Y"]);
        }
        else {
            $sql = Slider::whereId($request->slider_id)->update(['active'=>true]);
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
