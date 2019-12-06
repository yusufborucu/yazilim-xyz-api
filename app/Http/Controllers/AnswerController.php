<?php

namespace App\Http\Controllers;

use App\Models\Answer;
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
}