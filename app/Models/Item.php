<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    protected $table = 'items';

    public static function getItemStock($item_id)
    {
        $getItemStock = Item::select('item_stock')->where([
                'id' => $item_id
        ])->first();
        return $getItemStock->item_stock;
    } 

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
