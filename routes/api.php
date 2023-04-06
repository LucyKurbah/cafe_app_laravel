<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ItemController;
use App\Http\Controllers\API\TableController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\FoodController;
use App\Http\Controllers\API\SliderController;
use App\Http\Controllers\API\ConferenceController;
use App\Http\Controllers\API\FAQController;
use App\Http\Controllers\API\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|Lucy & Brandon
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

Route::middleware('throttle')->group(function(){
    Route::post('getItems',[ItemController::class,'getItems'])->middleware('cache.headers');
    Route::post('getFoodItems',[FoodController::class,'getItems'])->middleware('cache.headers');
    Route::post('getTables',[TableController::class,'getTables'])->middleware('cache.headers');
    Route::post('cart/getCart',[CartController::class,'getCart'])->middleware('cache.headers');
    Route::post('cart/add',[CartController::class,'cartAdd'])->middleware('cache.headers');
    Route::post('cart/remove',[CartController::class,'cartRemove'])->middleware('cache.headers');
    Route::post('cart/getTotal',[CartController::class,'cartTotal'])->middleware('cache.headers');
    Route::post('getSliders',[SliderController::class,'getSliders'])->middleware('cache.headers');
    Route::post('forgotPassword',[AuthController::class,'forgotPassword'])->middleware('cache.headers');
    Route::post('getConferenceDetails',[ConferenceController::class,'getConferenceDetails'])->middleware('cache.headers');
    Route::post('checkConferenceDetails',[ConferenceController::class,'checkConference'])->middleware('cache.headers');
    Route::post('getFAQ',[FAQController::class,'getFAQ'])->middleware('cache.headers');
    Route::post('order/getCartDetails',[OrderController::class,'getCartDetails'])->middleware('cache.headers');
    Route::post('order/saveDetails',[OrderController::class,'saveDetails'])->middleware('cache.headers');
    Route::post('order/getOrders',[OrderController::class,'getOrders'])->middleware('cache.headers');
    Route::post('makePayment',[OrderController::class,'makePayment'])->middleware('cache.headers');
    Route::post('validateTable',[OrderController::class,'ValidateTable'])->middleware('cache.headers');
    Route::post('validateConference',[OrderController::class,'ValidateConference'])->middleware('cache.headers');
});






