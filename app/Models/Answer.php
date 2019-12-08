<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $table = 'answers';

    protected $fillable = ['question_id', 'user_id', 'answer'];

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id')->select('id', 'username', 'image');
    }

    public function scores()
    {
        return $this->hasMany('App\Models\AnswerScore', 'answer_id', 'id')->select('answer_id', 'user_id', 'status');
    }
}
