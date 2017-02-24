<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
 */
Auth::routes();

Route::group(['prefix' => 'admin', 'middleware' => 'auth', 'as' => 'admin.', 'namespace' => 'Admin'], function () {
    Route::resource('glosarium/word', 'Glosarium\WordController', [
        'only' => ['index', 'edit'],
    ]);

    Route::resource('glosarium/category', 'Glosarium\CategoryController', [
        'only' => ['index'],
    ]);
});

Route::get('/kontak', 'ContactController@form')->name('contact.form');
Route::post('/kontak', 'ContactController@send')->name('contact.post');

Route::get('/external', 'LinkController@external')->name('link.external');

Route::get('/', 'PageController@index')->name('index');

Route::get('/{hash}', 'LinkController@redirect')->name('link.redirect')
    ->where('hash', '[A-Za-z0-9]+');
