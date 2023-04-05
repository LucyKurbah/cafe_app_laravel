<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Order;
use App\Models\TableOrder;
use App\Models\ConferenceOrder;
use App\Models\ItemOrder;
use App\Models\FoodOrder;

use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $role   = Auth::user()->role;
        $users  = User::where([['id','!=',1],['id','!=',2]])->count();
        $orders  = Order::count();
        $s1 = TableOrder::sum('table_price');
        $s2 = ConferenceOrder::sum('conference_price');
        $s3 = ItemOrder::sum('item_price');
        $s4 = FoodOrder::sum('food_price');
        $sum = 0;
        $sum = $s1 + $s2 + $s3 + $s4;

        return view('home')->with(compact('role','users','orders','sum'));
    }
}
