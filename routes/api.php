<?php

// KULLANICI İŞLEMLERİ
// Kayıt ol
Route::post('register', 'UserController@register');
// Giriş yap
Route::post('login', 'UserController@login');
// Parolamı unuttum
Route::post('forgot', 'UserController@forgot');
// Parola sıfırla
Route::post('new_password/{remember_token}', 'UserController@new_password');
// Kişi detayı
Route::get('user_detail/{id}', 'UserController@user_detail');

// SORU İŞLEMLERİ
// Anasayfa son sorular
Route::get('last_questions', 'QuestionController@last_questions');
// Soru detayı
Route::get('question_detail/{id}', 'QuestionController@question_detail');
// Arama
Route::post('search', 'QuestionController@search');
// Etiket detayı
Route::get('tag_detail/{tag}', 'QuestionController@tag_detail');

Route::group(['middleware' => 'auth:api'], function() {
    // KULLANICI İŞLEMLERİ
    // Çıkış yap
    Route::get('logout', 'UserController@logout');
    // Profili getir
    Route::get('profile', 'UserController@profile');
    // Profili düzenle
    Route::put('profile', 'UserController@update_profile');

    // SORU İŞLEMLERİ
    // Crud
    Route::apiResource('question', 'QuestionController');

    // CEVAP İŞLEMLERİ
    // Crud
    Route::apiResource('answer', 'AnswerController');
    // Oy ver
    Route::post('vote', 'AnswerController@vote');
});