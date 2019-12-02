<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionTag extends Model
{
    protected $table = 'question_tags';

    protected $fillable = ['question_id', 'tag'];
}
