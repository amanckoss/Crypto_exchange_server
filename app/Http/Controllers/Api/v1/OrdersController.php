<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LimitOrderRequest;
use App\Http\Requests\MaketerOrderReqest;
use App\Models\User;
use App\Http\Requests\CancelOrderRequest;
use Illuminate\Support\Facades\DB;

class OrdersController extends Controller
{
    public function market_order(MaketerOrderReqest $request) {
        $user = User::rtrieve($request->api_token);

        if ($request->operation == 'buy') {
            if ($request->amount * $request->price > $user->money) {
                return response([
                    "status" => false,
                    "error" => "Недостатьно коштів"
                ]);
            }
        }
        if ($user) {
            $order_amount = $request->amount;
            while(true) {
                // fill order data
                $orders = DB::select(
                    'SELECT o.amount, o.stock_id, o.id, o.trader_id, o.price FROM `order_books` o, `stock` s WHERE o.operation = ? and s.id = ? and s.id = o.stock_id and o.price = ?',
                    [$request->operation, $request->stock_name, $request->price]
                );

                // iteration threw users orders
                $amount = 0;
                foreach ($orders as $order) {
                    $amount += $order->amount;
                }
                if ($request->amount > $amount) {
                    return response([
                        "status" => false,
                        "error" => "Недостатьно ордерів на біржі"
                    ]);
                }
                if ($request->operation == 'sell') {
                    $my_orders_amount = DB::select('SELECT w.amount FROM wallets w, stock s where w.trader_id = ? and w.stock_id = ?', [$user->id, $request->stock_name]);
                    if (!$my_orders_amount || $my_orders_amount[0]->amount < $request->amount) {
                        $i = $my_orders_amount[0]->amount;
                        return response([
                            "status" => false,
                            "error" => "Недостатьно валюти в гаманці $i"
                        ]);
                    }
                }
                foreach ($orders as $order) {
                    // fill buyer, seller
                    if ($request->operation == 'buy') {
                        $seller_wallet_id = $order->trader_id;
                        $buyer_wallet_id = $user->id;
                    }
                    else {
                        $seller_wallet_id = $user->id;
                        $buyer_wallet_id = $order->trader_id;
                    }
                    if ($order->amount <= $order_amount) {
                        // closed hole order
                        $this->addToWallet($order->stock_id, $buyer_wallet_id, $order_amount, $order->price);
                        DB::update('Update users set money = money + ? where id = ?', [$order->amount * $order->price, $seller_wallet_id]);
                        DB::update('Update users set money = money - ? where id = ?', [$order->amount * $order->price, $buyer_wallet_id]);
                        DB::delete('delete from order_books WHERE id = ?', [$order->id]);
                        DB::insert('INSERT INTO order_history (`stock_id`, `amount`, `purchase_price`, `sell_trader_id`, `buy_trader_id`)
                                VALUES (?, ?, ?, ?, ?)', [$order->stock_id, $order->amount, $order->price, $seller_wallet_id, $buyer_wallet_id]);
                        $order_amount -= $order->amount;

                        if($order_amount == 0) {
                            return response()
                                ->json([
                                    "status" => true
                                ])
                                ->setStatusCode(200, "Ордер виконаний");
                        }
                    }
                    else {
                        // close part of order
                        $this->addToWallet($order->stock_id, $buyer_wallet_id, $order_amount, $order->price);
                        DB::update('Update users set money = money + ? where id = ?', [$order_amount * $order->price, $seller_wallet_id]);
                        DB::update('Update users set money = money - ? where id = ?', [$order_amount * $order->price, $buyer_wallet_id]);
                        DB::update('UPDATE order_books set amount = ? where id = ?',[$order->amount - $order_amount,$order->id]);
                        DB::insert('INSERT INTO order_history (`stock_id`, `amount`, `purchase_price`, `sell_trader_id`, `buy_trader_id`)
                                VALUES (?, ?, ?, ?, ?)', [$order->stock_id, $order_amount, $order->price, $seller_wallet_id, $buyer_wallet_id]);
                        return response()
                            ->json([
                                "status" => true
                            ])
                            ->setStatusCode(200, "Ордер виконаний");
                    }
                }
                return response([
                    "status" => false,
                    "error" => "Недостатьно ордерів"
                ]);
            }
        }
        return response([
            "status" => false,
            "error" => "Помилка операції"
        ]);
    }

    private function addToWallet($stock_id, $trader_id, $order_amount, $price) {
        $exist = DB::select('SELECT * FROM wallets where stock_id = ? and trader_id = ?', [$stock_id, $trader_id]);
        if ($exist) {
            DB::update('UPDATE wallets set amount = amount + ? where stock_id = ? and trader_id = ?', [$order_amount, $stock_id, $trader_id]);
        } else {
            DB::insert('INSERT INTO wallets (`stock_id`, `trader_id`, `amount`, `purchase_price`)
                          VALUES (?, ?, ?, ?)', [$stock_id, $trader_id, $order_amount, $price]);
        }
    }

    public function limit_order(LimitOrderRequest $request) {
        $user = User::where('api_token', $request->api_token)->first();

        if($user) {
            $count = DB::select('SELECT amount FROM wallets where trader_id = ? and stock_id = ?', [$user->id, $request->stock_id]);
            if ($count && $count[0]->amount >= $request->amount) {

                //open a transaction
                DB::insert('INSERT INTO order_books(`stock_id`, `trader_id`, `amount`, `operation`, `price`)
                                VALUES (?, ?, ?, "sell", ?)', [$request->stock_id, $user->id, $request->amount, $request->price,]);
                if ($count[0]->amount == $request->amount) {
                    DB::delete('DELETE FROM wallets where stock_id = ? and trader_id = ?', [$request->stock_id, $user->id]);
                }
                else {
                    DB::update('UPDATE wallets set amount = amount - ? where stock_id = ? and trader_id = ?', [$request->amount, $request->stock_id, $user->id]);
                }
                // check transaction for complete
                $orders = $this->check_orders($request->stock_id, $request->price);
                if ($orders) {
                    $this->closedOrder($orders, $user->id, $request->price, $request->amount, "sell");
                }
                return response()
                    ->json(["status" => true])
                    ->setStatusCode(200, "Ордер виконаний");
            }
            return response([
                "status" => false,
                "error" => 'недостатньо валюти'
            ]);
        }
        return response([
            "status" => false,
            "error" => 'помилка в обробці'
        ]);
    }

    public function check_orders($stock_id, $price, $operation = "buy") {
        return DB::select('SELECT id, stock_id, trader_id, amount, price FROM order_books where stock_id = ? and operation = ? and price >= ? order by price desc, created_at',
            [$stock_id, $operation, $price]);
    }

    public function closedOrder($orders, $user_id, $userPrice, $amount, $operation) {
        foreach ($orders as $order) {
            // fill buyer, seller
            if ($operation == 'buy') {
                $seller_wallet_id = $order->trader_id;
                $buyer_wallet_id = $user_id;
                $price = $userPrice;
            }
            else {
                $seller_wallet_id = $user_id;
                $buyer_wallet_id = $order->trader_id;
                $price = $order->price;
            }
            if ($order->amount <= $amount) {
                // closed hole order
                $this->addToWallet($order->stock_id, $buyer_wallet_id, $order->amount, $price);
                DB::update('Update users set money = money + ? where id = ?', [$order->amount * $price, $seller_wallet_id]);
                DB::update('Update users set money = money - ? where id = ?', [$order->amount * $price, $buyer_wallet_id]);
                DB::delete('delete from order_books WHERE id = ?', [$order->id]);
                DB::insert('INSERT INTO order_history (`stock_id`, `amount`, `purchase_price`, `sell_trader_id`, `buy_trader_id`)
                                VALUES (?, ?, ?, ?, ?)', [$order->stock_id, $order->amount, $price, $seller_wallet_id, $buyer_wallet_id]);

                if($amount == 0) {
                    return response()
                        ->json(["status" => true])
                        ->setStatusCode(200, "Ордер виконаний");
                }
            }
            else {
                // close part of order
                $this->addToWallet($order->stock_id, $buyer_wallet_id, $amount, $price);
                DB::update('Update users set money = money + ? where id = ?', [$amount * $price, $seller_wallet_id]);
                DB::update('Update users set money = money - ? where id = ?', [$amount * $price, $buyer_wallet_id]);
                DB::update('UPDATE order_books set amount = ? where id = ?',[$order->amount - $amount, $order->id]);
                DB::insert('INSERT INTO order_history (`stock_id`, `amount`, `purchase_price`, `sell_trader_id`, `buy_trader_id`)
                                VALUES (?, ?, ?, ?, ?)', [$order->stock_id, $amount, $price, $seller_wallet_id, $buyer_wallet_id]);
                return response()
                    ->json(["status" => true])
                    ->setStatusCode(200, "Ордер виконаний");
            }
        }
    }

    public function cancelOrder(CancelOrderRequest $request) {
        $user = User::where('api_token', $request->api_token)->first();
        $tt = 'dfgg';
        if ($user) {
            $tt = 'tttttt';
            $order = DB::select('SELECT stock_id, amount, price FROM order_books where id = ?', [$request->stock_id]);
            if ($order) {
                $this->addToWallet($order[0]->stock_id, $user->id, $order[0]->amount, $order[0]->price);
                DB::delete('delete from order_books WHERE id = ?', [$request->stock_id]);
                return response()
                    ->json(["status" => true])
                    ->setStatusCode(200, "Ордер ордер відмінений");
            }

        }
        return response([
            "status" => false,
            "error" => $tt
        ]);
    }
}
