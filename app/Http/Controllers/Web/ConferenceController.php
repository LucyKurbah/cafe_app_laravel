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
use App\Models\Conference;
use Redirect;


class ConferenceController extends Controller
{
    public function index()
    {
        $role   = Auth::user()->role;
        return view('modules.conference')->with(compact('role'));
    }

    public function ViewContent(Request $request){
        $sql = Conference::select('id','conference_name','price','path_file','active')->get();
        return response()->json($sql)->withHeaders([
            'Cache-Control' => 'max-age=15, public',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
        ]);
    }

    public function StoreData(Request $request){
        $validator = Validator::make($request->all(), [
            'conference_name' => 'required',
            'price' => 'required',
            'conference_img' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if($validator->fails()){
            return response()->json(["flag"=>"VE"]);
        }
        else {
            if(isset($request->id)){
               $sql_count = Conference::where('id',$request->id)->count();
               if($sql_count>0){
                try{
                    DB::beginTransaction();
                    $image = $request->file('conference_img');
                    $imageName = time().'.'.$image->getClientOriginalExtension();
                    $image->move(public_path('conference'), $imageName);
                    Conference::whereId($request->id)->update([
                            'conference_name' => $this->normalizeString($request->conference_name),
                            'price'=> $this->normalizeString($request->price),
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
                    $image = $request->file('conference_img');
                    $imageName = time().'.'.$image->getClientOriginalExtension();
                    $image->move(public_path('conference'), $imageName);
                    $Item = new Conference();
                    $Item->conference_name = $this->normalizeString($request->conference_name);
                    $Item->price = $this->normalizeString($request->price);
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
        $sql = Conference::select('id','conference_name','price','active','path_file')->where('id',$request->conference_id)->get();
        foreach ($sql as $item){
            $item->path_file =   $request->getSchemeAndHttpHost() .'/conference/'.$item->path_file;
        }
        return response()->json($sql);
    }

    public function DeleteData(Request $request){
        try{
            DB::beginTransaction();
            $sql = Conference::where('id',$request->conference_id)->delete();
            DB::commit();
            return response()->json(["flag"=>"Y"]);
        }
        catch(\Exception $e){
            DB::rollback();
            return response()->json(["flag"=>"N"]);
        }
    }

    public function ChangeActive(Request $request){
        $sql_check = Conference::select('active')->whereId($request->conference_id)->get();
        if(($sql_check[0]->active==true)){
            $sql = Conference::whereId($request->conference_id)->update(['active'=>false]);
            return response()->json(["flag"=>"Y"]);
        }
        else {
            $sql = Conference::whereId($request->conference_id)->update(['active'=>true]);
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
