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
use App\Models\User;
use App\Models\DocumentType;
use Illuminate\Support\Facades\Hash;
use Redirect;

class UserContoller extends Controller
{
    public function index()
    {
        $role   = Auth::user()->role;
        $sql = DocumentType::get();
        return view('modules.user')->with(compact('sql','role'));
    }

    public function ViewContent(Request $request){
        $sql = DocumentType::rightjoin('users', 'users.document_id', '=', 'document_type.id')
                            ->select('document_type.document_name','users.name', 'users.id','users.email','users.phone_no','users.active')
                            ->where([['users.id','!=','1'],['users.id','!=','2']])->get();       
         return response()->json($sql)->withHeaders([
            'Cache-Control' => 'max-age=15, public',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
        ]);
    }
    
    public function StoreData(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'phone_no'=> 'required',
            'document_type'=> 'required',
            'user_img' => 'required|image|mimes:jpeg,png,jpg,gif,pdf|max:2048',
        ]);

        if($validator->fails()){
            return response()->json(["flag"=>"VE"]);
        }
        else {
            if(isset($request->id)){
               $sql_count = User::where('id',$request->id)->count();
               if($sql_count>0){
                try{
                    DB::beginTransaction();
                    $image = $request->file('user_img');
                    $imageName = time().'.'.$image->getClientOriginalExtension();
                    $image->move(public_path('documents'), $imageName);
                        User::whereId($request->id)->update([
                            'name' => $this->normalizeString($request->name),
                            'email'=> $this->normalizeString($request->email),
                            'phone_no' => $this->normalizeString($request->phone_no),
                            'password' => Hash::make($request->password),
                            'document_id'=> $this->normalizeString($request->document_type),
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
                    $image = $request->file('user_img');
                    $imageName = time().'.'.$image->getClientOriginalExtension();
                    $image->move(public_path('documents'), $imageName);
                    $User = new User();
                    $User->name = $this->normalizeString($request->name);
                    $User->email = $this->normalizeString($request->email);
                    $User->password = Hash::make($request->password);
                    $User->phone_no=$this->normalizeString($request->phone_no);
                    $User->document_id = $this->normalizeString($request->document_type);
                    $User->path_file = $this->normalizeString($imageName);
                    $User->active = $request->has('active');
                    $User->save();
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
        $sql = User::select('id','name','email','document_id','active','phone_no',)->where('id',$request->id)->get();
        return response()->json($sql);
    }

    public function DeleteData(Request $request){
        try{
            DB::beginTransaction();
            $sql = User::where('id',$request->id)->delete();
            DB::commit();
            return response()->json(["flag"=>"Y"]);
        }
        catch(\Exception $e){
            DB::rollback();
            return response()->json(["flag"=>"N"]);
        }
    }

    public function GetIdType(Request $request){
        $sql = DocumentType::join('users', 'users.document_id', '=', 'document_type.id')
                             ->select('users.path_file','document_type.document_name','users.name')->where('users.id',$this->normalizeString($request->id))->get();
        foreach ($sql as $item){
            $item->path_file =   $request->getSchemeAndHttpHost() .'/documents/'.$item->path_file;
        }
        return response()->json($sql);
    }

    public function ChangeActive(Request $request){
        $sql_check = User::select('active')->whereId($this->normalizeString($request->id))->get();
        if(($sql_check[0]->active==true)){
            $sql = User::whereId($this->normalizeString($request->id))->update(['active'=>false]);
            return response()->json(["flag"=>"Y"]);
        }
        else {
            $sql = User::whereId($this->normalizeString($request->id))->update(['active'=>true]);
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
