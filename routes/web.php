<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::group(['middleware' => 'auth'], function () {
    // Route::resource('tasks', 'TaskController');
    Route::get('/tasks', 'TaskController@index')->name('tasks.index');
    Route::get('/tasks/create', 'TaskController@create')->name('tasks.create');
    Route::post('/tasks', 'TaskController@store')->name('tasks.store');
    Route::get('/tasks/{task}/edit', 'TaskController@edit')->name('tasks.edit');
    Route::get('/tasks/{task}', 'TaskController@show')->name('tasks.show');
    Route::patch('/tasks/{task}', 'TaskController@update')->name('tasks.update');
    Route::delete('/tasks/{task}', 'TaskController@destroy')->name('tasks.destroy');
    Route::get('/home', 'HomeController@index')->name('home');
});
