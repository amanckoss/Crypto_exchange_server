<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DataUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\PasswordUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\OrderBook;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function register(RegisterUserRequest $request)
    {
        User::create(
            [
                "first_name" => $request->first_name,
                "surname" => $request->surname,
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "money" => 0.00
            ]
        );

        return response()
            ->json(["status" => true])
            ->setStatusCode("200", "Account registered");
    }

    public function login(LoginUserRequest $request)
    {
        $user = User::where('email', $request->email)->first();
//        echo "start";

        if($user) {
            $user ->api_token = Str::random(100);
            $user->save();

            return response()
                ->json([
                    "status" => true,
                    "api_token" => $user -> api_token
                ])
                ->setStatusCode(200, "Login Authorized");
        } else {
            return  response([
                "status" => false
            ]);
        }
    }

    public function password(PasswordUserRequest $request) {
        $user = User::where('api_token', $request->api_token)->first();

        if($user && Hash::check($request->password, $user->password)) {
            $user ->status = 1;
            $user -> save();

            return response()
                ->json([
                    "status" => true,
                    "api_token" => $user -> api_token
                ])
                ->setStatusCode(200, "Password Authorized");
        } else {
            return response([
                "status" => false
            ]);
        }
    }

    public function getWalletData(DataUserRequest $request) {
        $user = User::where('api_token', $request->api_token)->first();
        $money = DB::select('SELECT sum(amount * purchase_price) money FROM wallets where trader_id = ?', [$user->id]);

        if ($user && $user->status == 1) {
            $orderBook = OrderBook::all();
            $orderBook->where('');
           return response()
               ->json([
                   "status" => true,
                   "money" => $user -> money,
                   "invested_money" => $money[0]->money,
                   "wallet" => DB::select('SELECT s.name, w.amount, w.purchase_price
                                                    FROM wallets w, stock s
                                                    WHERE w.trader_id = ?
                                                    and w.stock_id = s.id', [$user->id]),
               ]);
        } else {
            return response([
                "status" => false
            ]);
        }
    }

    public function getMyOrders(DataUserRequest $request) {
        $user = User::where('api_token', $request->api_token)->first();
        if ($user && $user->status == 1) {
            $orderBook = OrderBook::all();
            $orderBook->where('');
            return response()
                ->json([
                    "status" => true,
                    "invested_stock" => DB::select('SELECT o.id, s.name, o.amount, o.price purchase_price FROM order_books o, stock s where o.trader_id = ? and o.stock_id = s.id',
                        [$user->id])
                ]);
        } else {
            return response([
                "status" => false
            ]);
        }
    }

    public function getSellOrder(DataUserRequest $request) {
        $user = User::where('api_token', $request->api_token)->first();
        if ($user && $user->status == 1) {
            return response()
                ->json([
                    "status" => true,
                    "data" => DB::select(
                        'SELECT e.id, SUM(o.amount) amount, min(o.price) price FROM stock e, order_books o where e.name = ? and o.stock_id = e.id group by e.id;',
                        [$request->stock_name])
                ]);
        } else {
            return response([
                "status" => false
            ]);
        }
    }

    public function getMarketData(DataUserRequest $request)
    {
        $user = User::where('api_token', $request->api_token)->first();

        if ($user && $user->status == 1) {
            return response()
                ->json([
                    "status" => true,
                    "order_book_buy" => DB::select('SELECT s.name, s.id, b1.amount, b1.operation, b1.price
                                                    FROM `order_books` b1,
                                                    (SELECT MAX(price) price, stock_id FROM `order_books` where operation = ?
                                                    GROUP BY stock_id) b2,
                                                      stock s
                                                    WHERE operation = ?
                                                    and b2.stock_id = b1.stock_id
                                                    and b1.price = b2.price
                                                    and s.id = b1.stock_id', ['buy', 'buy']),
                    "order_book_sell" => DB::select('SELECT s.name, s.id, b1.amount, b1.operation, b1.price
                                                    FROM `order_books` b1,
                                                    (SELECT MIN(price) price, stock_id FROM `order_books` where operation = ?
                                                    GROUP BY stock_id) b2,
                                                      stock s
                                                    WHERE operation = ?
                                                    and b2.stock_id = b1.stock_id
                                                    and b1.price = b2.price
                                                    and s.id = b1.stock_id', ['sell', 'sell'])
                ]);
        } else {
            return response([
                "status" => false
            ]);
        }
    }

    public function getAccountData(DataUserRequest $request) {
        $user = User::where('api_token', $request->api_token)->first();

        if ($user && $user->status == 1) {
            return response()
                ->json([
                    "status" => true,
                    "email" => $user -> email
                ]);
        } else {
            return response([
                "status" => false
            ]);
        }
    }
}
