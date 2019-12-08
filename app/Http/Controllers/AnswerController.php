<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\AnswerScore;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnswerController extends Controller
{
    public function index()
    {

    }

    public function store()
    {
        $validator = Validator::make(request()->all(), [
            'question_id' => 'required',
            'answer' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Lütfen tüm alanları doldurunuz.'], 400);
        }

        $input = request()->all();
        $user = Auth::user();

        DB::beginTransaction();
        try {
            $input['user_id'] = $user->id;
            Answer::create($input);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Cevap eklenirken bir sorun oluştu.'], 400);
        }
        DB::commit();
        return response()->json(['message' => 'Cevap başarıyla eklendi.'], 200);
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

    public function vote()
    {
        $validator = Validator::make(request()->all(), [
            'answer_id' => 'required',
            'status' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Lütfen tüm alanları doldurunuz.'], 400);
        }

        $user = Auth::user();
        $input = request()->all();

        DB::beginTransaction();
        try {
            $exist = AnswerScore::where('answer_id', $input['answer_id'])->where('user_id', $user->id)->first();
            if ($exist != null) {
                if ($exist->status != $input['status']) {
                    $exist->status = $input['status'];
                    $exist->save();
                } else {
                    $exist->delete();
                }
            } else {
                $input['user_id'] = $user->id;
                AnswerScore::create($input);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Oy eklenirken bir sorun oluştu.'], 400);
        }
        DB::commit();
        return response()->json(['message' => 'Oy başarıyla eklendi.'], 200);
    }
}