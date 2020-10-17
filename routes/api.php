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


Route::group(['namespace'=>'Auth'],function(){
    
 
     Route::post('/user/signup', 'UserController@signup');
     Route::post('/user/signin', 'UserController@signin');
    //  curl -H "Content-Type: application/json" -H "Access-Control-Request-Headers: X-Requested-With"  -X POST -d '
    //  {"email":"alaa@gmail.com","password":"123456789"}' http://localhost:8000/api/user/signin
});

Route::middleware('jwt')->group(function () {
    Route::get('/quotes', 'QuoteController@getQuotes');  
Route::post('/quote', 'QuoteController@postQuote');
Route::put('/quote/{id}', 'QuoteController@putQuote');
Route::delete('/quote/{id}', 'QuoteController@deleteQuote');
});