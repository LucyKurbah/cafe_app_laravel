<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
  
});

Route::post('register',[AuthController::class,'register']);
Route::post('login',[AuthController::class,'login']);

Route::group(['middleware' => ['auth:sanctum']],function(){
    Route::get('user',[AuthController::class,'user']);
    Route::post('logout',[AuthController::class,'logout']);
});

Route::apiresource('items',ItemController::class);
Route::get('list/{id?}',[ItemController::class,'list']);
Route::get('search/{id}',[ItemController::class,'search']);
Route::post('add', [ItemController::class,'add']);
Route::post('save',[ItemController::class,"save"]);
Route::put('update',[ItemController::class,'update']);
Route::post('getItems',[ItemController::class,'getItems']);
Route::post('getImage',[ItemController::class,'getImage']);

Route::post('upload',[FileController::class,"upload"]);

Route::post('cart/getCart',[CartController::class,'getCart']);

Route::post('cart/add',[CartController::class,'cartAdd']);

Route::post('cart/remove',[CartController::class,'cartRemove']);







