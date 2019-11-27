<?php

namespace App\Http\Controllers;

use App\Models\OAuthAccessToken;
use App\Necessary;
use App\User;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    use Necessary;

    public function register()
    {
        $validator = Validator::make(request()->all(), [
            'username' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'c_password' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Lütfen tüm alanları doldurunuz.'], 400);
        }

        $input = request()->all();
        $salt = $this->generate_salt();
        $input['salt'] = $salt;
        $input['password'] = bcrypt($input['password'] . $salt);
        DB::beginTransaction();
        try {
            User::create($input);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Kullanıcı eklenirken bir sorun oluştu.'], 400);
        }
        DB::commit();
        return response()->json(['message' => 'Kullanıcı başarıyla eklendi.'], 200);
    }

    public function login()
    {
        $existUser = User::where('email', request('email'))->first();
        if ($existUser != null) {
            if (Auth::attempt(['email' => request('email'), 'password' => request('password') . $existUser->salt])) {
                $user = Auth::user();
                $success['token'] = $user->createToken('MyApp')->accessToken;
                $success['user_id'] = $user->id;
                $success['email'] = $user->email;
                $success['username'] = $user->username;
                return response()->json($success, 200);
            } else {
                return response()->json(['message' => 'E-posta adresi veya parola yanlış.'], 401);
            }
        } else {
            return response()->json(['message' => 'Bu e-posta adresi sistemde kayıtlı değil.'], 401);
        }
    }

    public function logout()
    {
        $user = Auth::user();
        OAuthAccessToken::where('user_id', $user->id)->delete();
        return response()->json(['message' => 'Başarıyla çıkış yaptınız.'], 200);
    }
}