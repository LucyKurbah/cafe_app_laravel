<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class ItemController extends Controller
{

    public function index()
    {   
        return response([
            'items' =>Item::orderBy('created_at','desc')->get()
        ],200);
    }

    public function create()
    {
        //
    }

    public function store(Request $req)
    {
        $rules = array(
            "item_name"=>"required",
            "item_price"=>"required",
            // "item_desc"=>"required"
        );
        // dd($req);
        $validator=Validator::make($req->all(),$rules);
        if($validator->fails())
            return response()->json($validator->errors(),401);

        // $image = $this->saveImage($req->item_img_loc, 'items');

        $item = new Item;
        $item->item_name = $req->item_name;
        $item->item_price = $req->item_price;
        $item->item_desc = $req->item_desc;
       
        if($req->hasFile('item_img_loc')){
           
            $path = $req->file('item_img_loc')->store('public/items');  //store in the db
            $url = Storage::url($path);      // retrieving the image
        }
       
        $item->item_img_loc = $path; 
        $res = $item->save();
        

        return response([
            'message' => 'Item created',
            'item' => $item
        ], 200);


        return response([
            "message" => "Item saved",
            'item' => $item
        ],200);
    }

    public function show(Item $item)
    {
        return response([
            'items' =>Item::where('id',$id)->get()
        ],200);
    }

    public function edit(Item $item)
    {
        //
    }

    public function destroy(Item $item)
    {
        //
    }

    public function list($id=null)
    {
        return $id?Item::find($id):Item::all();
    }

    public function add(Request $req)
    {
       
        $item = new Item;
        $item->item_name = $req->item_name;
        $item->item_price = $req->item_price;
        $item->item_desc = $req->item_desc;
        $res = $item->save();
        if($res)
        {
            return ["Result"=>$res];
        }
        return ["Result"=>$res];
    }
    
    public function update(Request $req)
    {
        $item = Item::find($req->id);
        $item->item_name = $req->item_name;
        $item->item_price = $req->item_price;
        $res = $item->save();
        if($res)
        {
            return ["Result"=>$res];
        }
        return ["Result"=>$res];
    }

    public function search($id)
    {
        return Item::where("id", $id)->get();
    }

    public function save(Request $req)
    {
        $rules = array(
            "item_name"=>"required",
            "item_price"=>"required",
            // "item_desc"=>"required"
        );

        $validator=Validator::make($req->all(),$rules);
        if($validator->fails())
            return response()->json($validator->errors(),401);

        $image = $this->saveimage($req->image, 'items');
        $item = new Item;
        $item->item_name = $req->item_name;
        $item->item_price = $req->item_price;
        $item->item_desc = $req->item_desc;
        $item->item_img_loc = $req->image;
        $res = $item->save();
        
        if($res)
        {
            return ["Result"=>$res];
        }
        return ["Result"=>$res];
    }

    public function getItems(Request $req)
    {

        $items=Item::all();
        // return array($items,Storage::url($items[0]["item_img_loc"]));
        //  return($items);
        $items = Item::all();
        foreach($items AS $item){
            // dd($item->item_img_loc);
            // foreach($item AS $index => $image){
              $item->item_img_loc ='http://10.179.2.187:8000'.Storage::url($item->item_img_loc);
            // }
          }
   
        return($items);
    }

    public function getImage(Request $req)
    {

        $image=Item::find($req->id);
       
       
        if($image)
        {
            return ["Result"=>Storage::url($image->item_img_loc)];
        }
        return ["Result"=>$image];
    }

    public function getCart()
    {
        $items=Item::all();
        // return array($items,Storage::url($items[0]["item_img_loc"]));
        //  return($items);
        $items = Item::all();
        foreach($items AS $item){
            // dd($item->item_img_loc);
            // foreach($item AS $index => $image){
              $item->item_img_loc ='http://10.179.2.187:8000'.Storage::url($item->item_img_loc);
            // }
          }
   
        return($items);
    }
}
