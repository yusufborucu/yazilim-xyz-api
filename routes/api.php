<?php

// KULLANICI İŞLEMLERİ
Route::post('register', 'UserController@register');
Route::post('login', 'UserController@login');
Route::post('forgot', 'UserController@forgot');
Route::post('new_password/{remember_token}', 'UserController@new_password');

// SORU İŞLEMLERİ
// Anasayfa son sorular
Route::get('last_questions', 'QuestionController@last_questions');
// Soru detayı
Route::get('question_detail/{id}', 'QuestionController@question_detail');

Route::group(['middleware' => 'auth:api'], function() {
    // KULLANICI İŞLEMLERİ
    Route::get('logout', 'UserController@logout');
    Route::get('profile', 'UserController@profile');
    Route::put('profile', 'UserController@update_profile');

    // SORU İŞLEMLERİ
    Route::apiResource('question', 'QuestionController');

    // CEVAP İŞLEMLERİ
    Route::apiResource('answer', 'AnswerController');
    Route::post('vote', 'AnswerController@vote');
});