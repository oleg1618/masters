<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::auth();
Route::get('confirm-email', [
    'as' => 'confirm-email', 'uses' => 'Auth\AuthController@confirmEmail'
]);

Route::get('/home', 'HomeController@index');

Route::get('/id{id}', 'ProfileController@view');
Route::get('/profile', [
    'middleware' => 'auth', 
    'uses' => 'ProfileController@edit'
]);
