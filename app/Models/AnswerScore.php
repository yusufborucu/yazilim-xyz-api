<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnswerScore extends Model
{
    protected $table = 'answer_scores';

    protected $fillable = ['answer_id', 'user_id', 'status'];
}
