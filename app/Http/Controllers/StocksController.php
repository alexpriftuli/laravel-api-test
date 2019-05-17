<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Stock;
use DateTime;
use DateInterval;

class StocksController extends Controller
{
    /**
     *
     *
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function getFluctuationsAndMaxMinRise(Request $request): string
    {
        $dates = $request->all();

        //get all the symbols ordered by symbol
        $stocksSymbol = Stock::select('symbol')->orderBy('symbol')->get()->groupBy('symbol');
        $symbols = array_keys($stocksSymbol->toArray());

        //init the array that we are going to use to put all the results of each symbol
        $response = [];
        foreach ($symbols as $symbol) {

            //get all the stocks of a symbol, in a specific range and ordered by date ASC
            $stocks = $this->getAllStocksByDateRangeAndSymbol($dates, $symbol, 'created_at');

            if (!$stocks->isEmpty()) {

                $maxRise = 0;
                $maxFall = 0;
                $fluctuations = 0;

                foreach ($stocks as $index => $stock) {

                    //For each stock I calculate the difference between the previous and the next stock
                    $prevDifference = isset($stocks[$index - 1]) ? $stock->price - $stocks[$index - 1]->price : null;
                    $nextDifference = isset($stocks[$index + 1]) ? $stock->price - $stocks[$index + 1]->price : null;

                    //if the previous difference is negative, it is a fall
                    //I compare it with the max value of fall
                    if ($prevDifference < 0 && abs($prevDifference) > $maxFall) {
                        $maxFall = $prevDifference;
                    }

                    //if the previous difference is positive, it is a rise
                    //I compare it with the max value of rise
                    if ($prevDifference > 0 && abs($prevDifference) > $maxRise) {
                        $maxRise = $prevDifference;
                    }

                    //if the next difference is negative, it is a rise
                    //I compare it with the max value of rise
                    if ($nextDifference < 0 && abs($nextDifference) > $maxRise) {
                        $maxRise = $nextDifference;
                    }

                    //if the next difference is positive, it is a fall
                    //I compare it with the max value of fall
                    if ($nextDifference > 0 && abs($nextDifference) > $maxFall) {
                        $maxFall = $nextDifference;
                    }

                    //if the previous and the next difference has the same sign it is a fluctuation
                    if ($prevDifference * $nextDifference > 0) {
                        $fluctuations++;
                    }

                }

                $response[] = [
                    'symbol' => $symbol,
                    'fluctuations' => $fluctuations,
                    'max_rise' => round($maxRise, 2),
                    'max_fall' => round($maxFall, 2),
                ];

            } else {

                $response[] = [
                    'symbol' => $symbol,
                    'message' => 'There are no trades in the given date range',
                ];

            }

        }

        return response()->json($response, 200);
    }

    /**
     * Return a Stock object based on a range of dates and a symbol, and orders it in a specific order
     *
     * @param $dates
     * @param $symbol
     * @param $order
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Exception
     */
    public function getAllStocksByDateRangeAndSymbol($dates, $symbol, $order): \Illuminate\Database\Eloquent\Collection
    {
        //initialization of the dates in compatible format for mongo
        $start = new DateTime($dates['start']);
        $start->format(DateTime::ATOM);

        $end = new DateTime($dates['end']);
        //We add 1 day to get all the the stocks included in the end date
        $end->add(new DateInterval('P1D'));
        $end->format(DateTime::ATOM);

        $stocks = Stock::where(['symbol' => $symbol])
            ->whereBetween('created_at', [
                $start, $end
            ])
            ->orderBy($order)
            ->get();

        return $stocks;
    }

    /**
     * Return the max price and the min price of the stock of a specific symbol in a period of time
     *
     * @param Request $request
     * @param $symbol
     * @return string
     */
    public function getMaxAndMinPricesBySymbol(Request $request, $symbol): string
    {
        $dates = $request->all();

        //get all the stocks of a symbol, in a specific range and ordered by price ASC
        $stocks = $this->getAllStocksByDateRangeAndSymbol($dates, $symbol, 'price');

        //if is set "message", there is no stock in that range and/or for that symbol
        if (!$stocks->isEmpty()) {

            return response()->json([
                'symbol' => $symbol,
                'highest' => $stocks->last()->price,
                'lowest' => $stocks->first()->price,
            ], 200);

        }

        return response()->json([
            'symbol' => $symbol,
            'message' => 'There are no trades in the given date range',
        ], 404);
    }
}
