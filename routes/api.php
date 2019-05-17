<?php

use Illuminate\Http\Request;

/*
 * add new trade
 */
Route::post('trades', 'TradesController@addNew');

/*
 * delete all the trades
 */
Route::delete('erase', 'TradesController@eraseAll');

/*
 * get all the trades
 */
Route::get('trades', 'TradesController@getAll');

/*
 * get all the trades for the given user id
 */
Route::get('trades/users/{userId}', 'TradesController@getTradesByUserId');

/*
 * get max and min of the stock price for the given symbol in the given period
 */
Route::get('stocks/{symbol}/price', 'StocksController@getMaxAndMinPricesBySymbol');

/*
 * get fluctuations and max and min rise of all the stock prices for the given period
 */
Route::get('stocks/stats', 'StocksController@getFluctuationsAndMaxMinRise');
