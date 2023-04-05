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
use App\Models\Table;
use Redirect;

class TableContoller extends Controller
{
    public function index()
    {
        $role   = Auth::user()->role;
        return view('modules.table')->with(compact('role'));
    }

    public function ViewContent(Request $request){
        $sql = Table::select('id','table_name','seat','price','active')->get();
        return response()->json($sql)->withHeaders([
            'Cache-Control' => 'max-age=15, public',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
        ]);
    }

    public function StoreData(Request $request) {
        $validator = Validator::make($request->all(), [
            'table_name' => 'required',
            'seat' => 'required',
            'price' => 'required',
            'desc' => 'required',
            'table_img' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        if($validator->fails()){
            return response()->json(["flag"=>"VE"]);
        }
        else {
            if(isset($request->id)){
               $sql_count = Table::where('id',$request->id)->count();
               if($sql_count>0){
                try{
                    DB::beginTransaction();
                    $image = $request->file('table_img');
                    $imageName = time().'.'.$image->getClientOriginalExtension();
                    $image->move(public_path('images'), $imageName);
                        Table::whereId($request->id)->update([
                            'table_name' => $this->normalizeString($request->table_name),
                            'seat' => $this->normalizeString($request->seat),
                            'price'=> $this->normalizeString($request->price),
                            'desc'=> $this->normalizeString($request->desc),
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
            $image = $request->file('table_img');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('images'), $imageName);
            try{
                        DB::beginTransaction();
                        $Table = new Table();
                        $Table->table_name = $this->normalizeString($request->table_name);
                        $Table->price= $this->normalizeString($request->price);
                        $Table->seat=$this->normalizeString($request->seat);
                        $Table->desc=$this->normalizeString($request->desc);
                        $Table->active = $request->has('active');
                        $Table->path_file=$this->normalizeString($imageName);
                        $Table->save();
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
        $sql = Table::select('id','table_name','seat','price','desc','active','path_file')->where('id',$request->table_id)->get();
        foreach ($sql as $item){
            $item->path_file =   $request->getSchemeAndHttpHost() .'/images/'.$item->path_file;
        }
        return response()->json($sql);
    }

    public function DeleteData(Request $request){
        try{
            DB::beginTransaction();
            $sql = Table::where('id',$request->table_id)->delete();
            DB::commit();
            return response()->json(["flag"=>"Y"]);
        }
        catch(\Exception $e){
            DB::rollback();
            return response()->json(["flag"=>"N"]);
        }
    }

    public function ChangeActive(Request $request){
        $sql_check = Table::select('active')->whereId($request->table_id)->get();
        if(($sql_check[0]->active==true)){
            $sql = Table::whereId($request->table_id)->update(['active'=>false]);
            return response()->json(["flag"=>"Y"]);
        }
        else {
            $sql = Table::whereId($request->table_id)->update(['active'=>true]);
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