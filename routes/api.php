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

// SORU İŞLEMLERİ
// Anasayfa son sorular
Route::get('last_questions', 'QuestionController@last_questions');
// Soru detayı
Route::get('question_detail/{id}', 'QuestionController@question_detail');

// GENEL İŞLEMLER
// Arama
Route::post('search', 'GeneralController@search');
// Kişi detayı
Route::get('user_detail/{id}', 'GeneralController@user_detail');
// Etiket detayı
Route::get('tag_detail/{tag}', 'GeneralController@tag_detail');
// Tüm en iyiler
Route::get('all_best', 'GeneralController@all_best');
// Tüm etiketler
Route::get('all_tags', 'GeneralController@all_tags');

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