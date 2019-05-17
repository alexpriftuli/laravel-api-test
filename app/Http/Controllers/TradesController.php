<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Trade;
use App\Stock;
use App\User;
use Mockery\Exception;

class TradesController extends Controller
{

    /**
     * Add a new trade
     *
     * @param Request $request
     * @return string
     */
    public function addNew(Request $request): string
    {
        if (null !== $request->post()) {

            //I get all the trades that I have to add and store them in an array
            $tradesToAdd = [];
            foreach ($request->post() as $input) {

                $input = json_decode($input);

                if (is_array($input)) {
                    $tradesToAdd = $tradesToAdd + $input;
                } else {
                    $tradesToAdd[] = $input;
                }

            }

            foreach ($tradesToAdd as $tradeToAdd) {

                try {
                    $trade = Trade::where(['trade_id' => $tradeToAdd->id])->first();

                    if ($trade) {
                        return response()->json([
                            'message' => "Trade id {$tradeToAdd->id} already existing"
                        ], 400);
                    }

                    $user = User::create([
                        'user_id' => $tradeToAdd->user->id,
                        'name' => $tradeToAdd->user->name,
                    ])->toArray();

                    $stock = Stock::create([
                        'symbol' => $tradeToAdd->symbol,
                        'price' => $tradeToAdd->price,
                        'created_at' => $tradeToAdd->timestamp,
                    ])->toArray();

                    $trade = Trade::create([
                        'trade_id' => $tradeToAdd->id,
                        'type' => $tradeToAdd->type,
                        'user' => $user,
                        'shares' => $tradeToAdd->shares,
                        'stock' => $stock,
                        'created_at' => $tradeToAdd->timestamp,
                    ]);

                    $trade->save();
                } catch (Exception $e) {
                    return response()->json($e, 400);
                }

            }

            return response()->json(null, 201);

        }

        return response()->json([
            'message' => "Bad POST request"
        ], 400);
    }

    /**
     * Delete all the existing trades
     *
     * @return string
     */
    public function eraseAll(): string
    {
        Trade::query()->delete();
        Stock::query()->delete();
        User::query()->delete();
        return response()->json(null, 200);
    }


    /**
     * Return a list of all the existing trades
     *
     * @return \App\Trade
     */
    public function getAll(): string
    {
        return response()->json(Trade::orderBy('trade_id')->get(), 200);
    }

    /**
     * Return a list of all the existing trades of a specific user
     *
     * @param $userId
     * @return int
     */
    public function getTradesByUserId($userId): string
    {
        $trades = Trade::where(['user.user_id' => (int)$userId])
            ->orderBy('trade_id')
            ->get();

        if (!$trades->isEmpty()) {

            $response = [];
            foreach ($trades as $trade) {

                $response[] = [
                    'timestamp' => \date('Y-m-d H:i:s', strtotime($trade->created_at)),
                    'price' => $trade->stock['price'],
                    'shares' => $trade->shares,
                    'symbol' => $trade->stock['symbol'],
                    'user' => [
                        'name' => $trade->user['name'],
                        'user_id' => $trade->user['user_id'],
                    ],
                    'type' => $trade->type,
                    'id' => $trade->trade_id,
                ];

            }

            return response()->json($response, 200);
        }

        return response()->json([
            "message" => "Not found any trade with this user id"
        ], 404);
    }
}
