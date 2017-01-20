<?php

Route::group(['namespace' => 'User', 'middleware' => 'auth', 'as' => 'user.'], function () {
    // notification
    Route::get('notification', 'NotificationController@index')->name('notification.index');

    // password
    Route::get('user/password', 'PasswordController@form')->name('password.form');
    Route::post('user/password', 'PasswordController@update')->name('password.update');

    Route::group(['prefix' => 'user/glosarium', 'namespace' => 'Glosarium', 'as' => 'glosarium.'], function () {
        Route::put('update/field', 'CategoryController@updateField')->name('category.updateField');
    });

});