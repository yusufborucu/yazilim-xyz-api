<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'questions';

    protected $fillable = ['user_id', 'title', 'description'];

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id')->select('id', 'username', 'image');
    }

    public function tags()
    {
        return $this->hasMany('App\Models\QuestionTag', 'question_id', 'id')->select('id', 'question_id', 'tag');
    }

    public function answers()
    {
        return $this->hasMany('App\Models\Answer', 'question_id', 'id')->select('id', 'user_id', 'question_id', 'answer', 'created_at');
    }
}