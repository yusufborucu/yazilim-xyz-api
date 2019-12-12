<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionTag;
use App\Necessary;
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
        return response()->json($last_questions, 200);
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
}