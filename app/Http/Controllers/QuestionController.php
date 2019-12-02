<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionTag;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function store()
    {
        $validator = Validator::make(request()->all(), [
            'title' => 'required',
            'description' => 'required',
            'tags' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Lütfen tüm alanları doldurunuz.'], 400);
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
            return response()->json(['message' => 'Soru eklenirken bir sorun oluştu.'], 400);
        }
        DB::commit();
        return response()->json(['message' => 'Soru başarıyla eklendi.'], 200);
    }

    public function index()
    {

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
        $last_questions = Question::with('user', 'tags', 'answers')->select('id', 'user_id', 'title', 'reading', 'created_at')->get()->take(10);
        Carbon::setLocale('tr');
        foreach ($last_questions as $last_question) {
            $last_question['answer_count'] = $last_question->answers->count();
            unset($last_question['answers']);
            $last_question->date = Carbon::parse($last_question->created_at)->diffForHumans();
            unset($last_question['created_at']);
        }
        return response()->json($last_questions, 200);
    }
}