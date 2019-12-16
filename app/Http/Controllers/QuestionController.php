<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionTag;
use App\Necessary;
use App\User;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    use Necessary;

    public function index()
    {

    }

    public function store()
    {
        $validator = Validator::make(request()->all(), [
            'title' => 'required',
            'description' => 'required',
            'tags' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->response_message('Lütfen tüm alanları doldurunuz.', 400);
        }

        $input = request()->all();
        $user = Auth::user();

        DB::beginTransaction();
        try {
            $input['user_id'] = $user->id;
            $question = Question::create($input);

            $tags = explode(',', $input['tags']);
            foreach ($tags as $tag) {
                $question_tag = new QuestionTag;
                $question_tag->question_id = $question->id;
                $question_tag->tag = $tag;
                $question_tag->save();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response_message('Soru eklenirken bir sorun oluştu.', 400);
        }
        DB::commit();
        return $this->response_message('Soru başarıyla eklendi.', 200);
    }

    public function show($id)
    {

    }

    public function update($id)
    {

    }

    public function destroy($id)
    {

    }

    public function last_questions()
    {
        $last_questions = Question::with('user', 'tags', 'answers')
                          ->select('id', 'user_id', 'title', DB::raw('substr(description, 1, 200) as description'), 'reading', 'created_at')
                          ->get()
                          ->take(10);
        Carbon::setLocale('tr');
        foreach ($last_questions as $last_question) {
            $last_question['answer_count'] = $last_question->answers->count();
            unset($last_question['answers']);
            $last_question->date = Carbon::parse($last_question->created_at)->diffForHumans();
            unset($last_question['created_at']);
        }
        $response['last_questions'] = $last_questions;
        $response['last_tags'] = QuestionTag::select('tag', DB::raw('count(*) as total'))->groupBy('tag')->orderBy('created_at', 'desc')->limit(7)->get();
        $response['popular_tags'] = QuestionTag::select('tag', DB::raw('count(*) as total'))->groupBy('tag')->orderBy('total', 'desc')->limit(7)->get();
        $response['best'] = User::select('id', 'username', 'image', 'score')->where('score', '>', 0)->orderBy('score', 'desc')->limit(3)->get();
        return response()->json($response, 200);
    }

    public function question_detail($id)
    {
        $question = Question::with('user', 'tags', 'answers')->find($id);
        $question->reading = $question->reading + 1;
        $question->save();

        Carbon::setLocale('tr');
        $question->date = Carbon::parse($question->created_at)->diffForHumans();
        unset($question['created_at']);
        $question->answer_count = $question->answers->count();
        foreach ($question->answers as $answer) {
            $answer->user;
            $answer->date = Carbon::parse($answer->created_at)->diffForHumans();
            unset($answer['created_at']);
            $answer->scores;
            $answer->score = $answer->scores()->where('status', true)->count() - $answer->scores()->where('status', false)->count();
        }
        return response()->json($question, 200);
    }
}