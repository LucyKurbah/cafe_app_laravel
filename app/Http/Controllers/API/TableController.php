<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;
use App\Models\Table;

class TableController extends Controller
{
    public function getTables(Request $req)
    {
        $ip = $req->ip();
        $tables = Table::all()->where('active',true);
        $etag = md5(json_encode($tables));
        foreach($tables AS $table){       
              $table->path_file =   $req->getSchemeAndHttpHost() .'/images/'.$table->path_file;
          }
          return response()->json($tables)->withHeaders([
            'Cache-Control' => 'max-age=15, public',
            'Expires' => gmdate('D, d M Y H:i:s', time() + 15) . ' IST',
            'Vary' => 'Accept-Encoding',
            'ETag' => $etag,
        ]);
    }   
}
