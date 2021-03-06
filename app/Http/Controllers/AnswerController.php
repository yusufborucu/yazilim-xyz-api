<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\AnswerScore;
use App\Necessary;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnswerController extends Controller
{
    use Necessary;

    public function store()
    {
        $validator = Validator::make(request()->all(), [
            'question_id' => 'required',
            'answer' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->response_message('Lütfen tüm alanları doldurunuz.', 400);
        }

        $input = request()->all();
        $user = Auth::user();

        DB::beginTransaction();
        try {
            $input['user_id'] = $user->id;
            Answer::create($input);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response_message('Cevap eklenirken bir sorun oluştu.', 400);
        }
        DB::commit();
        return $this->response_message('Cevap başarıyla eklendi.', 200);
    }

    public function update($id)
    {
        $validator = Validator::make(request()->all(), [
            'question_id' => 'required',
            'answer' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->response_message('Lütfen tüm alanları doldurunuz.', 400);
        }

        $input = request()->all();
        $user = Auth::user();

        $isExist = Answer::where('user_id', $user->id)->where('id', $id)->first();
        if ($isExist == null) {
            return $this->response_message('Böyle bir cevap mevcut değil.', 500);
        }

        DB::beginTransaction();
        try {
            $isExist->answer = $input['answer'];
            $isExist->save();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response_message('Cevap düzenlenirken bir sorun oluştu.', 400);
        }
        DB::commit();
        return $this->response_message('Cevap başarıyla düzenlendi.', 200);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $answer = Answer::where('user_id', $user->id)->where('id', $id)->first();
        if ($answer == null) {
            return $this->response_message('Böyle bir cevap mevcut değil.', 500);
        }

        DB::beginTransaction();
        try {
            AnswerScore::where('answer_id', $id)->delete();
            $answer->delete();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response_message('Cevap silinirken bir sorun oluştu.', 400);
        }
        DB::commit();
        return $this->response_message('Cevap başarıyla silindi.', 200);
    }

    public function vote()
    {
        $validator = Validator::make(request()->all(), [
            'answer_id' => 'required',
            'status' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->response_message('Lütfen tüm alanları doldurunuz.', 400);
        }

        $user = Auth::user();
        $input = request()->all();

        $can_vote = Answer::where('user_id', $user->id)->count();
        if ($can_vote < 3) {
            return $this->response_message('Oy verebilmek için en az 3 cevap yazmış olmanız gerekmektedir.', 400);
        }

        DB::beginTransaction();
        try {
            $exist = AnswerScore::where('answer_id', $input['answer_id'])->where('user_id', $user->id)->first();
            $answer_user = Answer::find($input['answer_id'])->user;
            if ($exist != null) {
                if ($exist->status != $input['status']) {
                    $answer_user->score = $input['status'] ? $answer_user->score + 1 : $answer_user->score - 1;
                    $answer_user->save();
                    $exist->status = $input['status'];
                    $exist->save();
                } else {
                    $answer_user->score = $input['status'] ? $answer_user->score - 1 : $answer_user->score;
                    $answer_user->save();
                    $exist->delete();
                }
            } else {
                $answer_user->score = $input['status'] ? $answer_user->score + 1 : $answer_user->score - 1;
                $answer_user->save();
                $input['user_id'] = $user->id;
                AnswerScore::create($input);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response_message('Oy eklenirken bir sorun oluştu.', 400);
        }
        DB::commit();
        return $this->response_message('Oy başarıyla eklendi.', 200);
    }
}