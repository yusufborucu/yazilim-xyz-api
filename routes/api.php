<?php

Route::post('register', 'UserController@register');
Route::post('login', 'UserController@login');
Route::group(['middleware' => 'auth:api'], function() {
    Route::get('logout', 'UserController@logout');
});
