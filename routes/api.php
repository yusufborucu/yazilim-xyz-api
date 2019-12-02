<?php

// KULLANICI İŞLEMLERİ
Route::post('register', 'UserController@register');
Route::post('login', 'UserController@login');
Route::post('forgot', 'UserController@forgot');
Route::post('new_password/{remember_token}', 'UserController@new_password');

// SORU İŞLEMLERİ
// Anasayfa son sorular
Route::get('last_questions', 'QuestionController@last_questions');

Route::group(['middleware' => 'auth:api'], function() {
    // KULLANICI İŞLEMLERİ
    Route::get('logout', 'UserController@logout');

    // SORU İŞLEMLERİ
    Route::apiResource('question', 'QuestionController');
});