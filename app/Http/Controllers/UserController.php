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
        $validator = Validator::make(request()->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Lütfen tüm alanları doldurunuz.'], 400);
        }

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

    public function forgot()
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required|email'
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Lütfen tüm alanları doldurunuz.'], 400);
        }

        $email = request()->email;
        $isExist = User::where('email', $email)->first();
        if ($isExist != null) {
            $remember_token = $this->generate_remember_token();
            $isExist->remember_token = $remember_token;
            $isExist->save();

            $url = "http://localhost:8080/new-password/$remember_token";
            $content = "Parolanızı sıfırlamak için lütfen aşağıdaki butona tıklayınız.";
            $subject = "Parola Sıfırlama Linki";
            $data = array(
                'username' => $isExist->username,
                'content' => $content,
                'url' => $url
            );
            $this->send_mail('email.forgot', $data, $email, $subject);
            return response()->json(['message' => 'Parola sıfırlama linki başarıyla gönderildi. Lütfen e-posta adresinize gelen linke tıklayınız.'], 200);
        } else {
            return response()->json(['message' => 'Bu e-posta adresi sistemde kayıtlı değil.'], 401);
        }
    }

    public function new_password($remember_token)
    {
        $validator = Validator::make(request()->all(), [
            'password' => 'required',
            'c_password' => 'required|same:password'
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Lütfen tüm alanları doldurunuz.'], 400);
        }

        $password = request()->password;

        $isExist = User::where('remember_token', $remember_token)->first();
        if ($isExist != null) {
            $salt = $this->generate_salt();
            $new_password = bcrypt($password . $salt);
            $isExist->password = $new_password;
            $isExist->salt = $salt;
            $isExist->remember_token = null;
            $isExist->save();

            $content = "Parola sıfırlama işleminiz başarıyla tamamlandı.";
            $subject = "Yeni Parola";
            $data = array(
                'username' => $isExist->username,
                'content' => $content
            );
            $this->send_mail('email.new_password', $data, $isExist->email, $subject);

            return response()->json(['message' => 'Parola sıfırlama işlemi başarıyla tamamlandı.'], 200);
        } else {
            return response()->json(['message' => 'Böyle bir kullanıcı mevcut değil.'], 401);
        }
    }
}