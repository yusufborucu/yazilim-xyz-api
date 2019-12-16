<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\QuestionTag;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GeneralController extends Controller
{
    public function search()
    {
        $text = request()->text;
        $questions = Question::with('user', 'tags', 'answers')
            ->select('id', 'user_id', 'title', DB::raw('substr(description, 1, 200) as description'), 'reading', 'created_at')
            ->where(function ($query) use ($text) { $query->where('title', 'like', '%' . $text . '%'); })
            ->orWhere(function ($query) use ($text) { $query->where('description', 'like', '%' . $text . '%'); })
            ->orderBy('created_at', 'desc')
            ->get();
        Carbon::setLocale('tr');
        foreach ($questions as $question) {
            $question['answer_count'] = $question->answers->count();
            unset($question['answers']);
            $question->date = Carbon::parse($question->created_at)->diffForHumans();
            unset($question['created_at']);
        }
        return response()->json($questions, 200);
    }

    public function user_detail($id)
    {
        $user = User::find($id);
        $question_count = Question::where('user_id', $id)->count();
        $answer_count = Answer::where('user_id', $id)->count();
        $response = (object)array(
            'username' => $user->username,
            'image' => $user->image,
            'score' => $user->score,
            'about' => $user->about,
            'question_count' => $question_count,
            'answer_count' => $answer_count
        );
        return response()->json($response, 200);
    }

    public function tag_detail($tag)
    {
        $response = array();
        Carbon::setLocale('tr');
        $question_ids = QuestionTag::select('question_id')->where('tag', $tag)->get();
        foreach ($question_ids as $question_id) {
            $question = Question::with('user', 'tags', 'answers')
                ->select('id', 'user_id', 'title', DB::raw('substr(description, 1, 200) as description'), 'reading', 'created_at')
                ->where('id', $question_id->question_id)
                ->first();
            $question['answer_count'] = $question->answers->count();
            unset($question['answers']);
            $question->date = Carbon::parse($question->created_at)->diffForHumans();
            unset($question['created_at']);
            array_push($response, $question);
        }
        return response()->json($response, 200);
    }

    public function all_best()
    {
        $all_best = User::select('id', 'username', 'image', 'score')->where('score', '>', 0)->orderBy('score', 'desc')->get();
        return response()->json($all_best, 200);
    }

    public function all_tags()
    {
        $all_tags = QuestionTag::select('tag', DB::raw('count(*) as total'))->groupBy('tag')->orderBy('total', 'desc')->get();
        return response()->json($all_tags, 200);
    }
}