<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', 'App\Http\Controllers\Api\v1\UserController@register');
Route::post('/login', 'App\Http\Controllers\Api\v1\UserController@login');
Route::post('/password', 'App\Http\Controllers\Api\v1\UserController@password');
Route::get('/data', 'App\Http\Controllers\Api\v1\UserController@getData');
Route::get('/data_market', 'App\Http\Controllers\Api\v1\UserController@getMarketData');
Route::get('/data_wallet', 'App\Http\Controllers\Api\v1\UserController@getWalletData');
Route::get('/data_account', 'App\Http\Controllers\Api\v1\UserController@getAccountData');
Route::get('/data_sell', 'App\Http\Controllers\Api\v1\UserController@getSellOrder');
Route::get('/my_orders', 'App\Http\Controllers\Api\v1\UserController@getMyOrders');

Route::post('/market_order', '\App\Http\Controllers\Api\v1\OrdersController@market_order');
Route::post('/limit_order', '\App\Http\Controllers\Api\v1\OrdersController@limit_order');
Route::post('/cancel_orders', '\App\Http\Controllers\Api\v1\OrdersController@cancelOrder');
