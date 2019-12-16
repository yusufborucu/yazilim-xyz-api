<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\OAuthAccessToken;
use App\Models\Question;
use App\Necessary;
use App\User;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\ImageManagerStatic as Image;
use File;

class UserController extends Controller
{
    use Necessary;

    public function register()
    {
        $validator = Validator::make(request()->all(), [
            'username' => 'required',
            'email' => 'required',
            'password' => 'required',
            'c_password' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->response_message('Lütfen tüm alanları doldurunuz.', 400);
        }

        $input = request()->all();

        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->response_message('Lütfen geçerli bir e-posta adresi giriniz.', 400);
        }

        if ($input['password'] != $input['c_password'])
            return $this->response_message('Girdiğiniz parolalar eşleşmiyor.', 400);

        $existUsername = User::where('username', $input['username'])->first();
        if ($existUsername != null) {
            return $this->response_message('Bu kullanıcı adı zaten sistemde kayıtlı.', 400);
        }

        $existEmail = User::where('email', $input['email'])->first();
        if ($existEmail != null) {
            return $this->response_message('Bu e-posta adresi zaten sistemde kayıtlı.', 400);
        }

        $salt = $this->generate_salt();
        $input['salt'] = $salt;
        $input['password'] = bcrypt($input['password'] . $salt);
        DB::beginTransaction();
        try {
            User::create($input);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->response_message('Kullanıcı eklenirken bir sorun oluştu.', 500);
        }
        DB::commit();
        return $this->response_message('Kullanıcı başarıyla eklendi.', 200);
    }

    public function login()
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->response_message('Lütfen tüm alanları doldurunuz.', 400);
        }

        if (!filter_var(request('email'), FILTER_VALIDATE_EMAIL)) {
            return $this->response_message('Lütfen geçerli bir e-posta adresi giriniz.', 400);
        }

        $existUser = User::where('email', request('email'))->first();
        if ($existUser != null) {
            if (Auth::attempt(['email' => request('email'), 'password' => request('password') . $existUser->salt])) {
                $user = Auth::user();
                $success['username'] = $user->username;
                $success['user_id'] = $user->id;
                $success['token'] = $user->createToken('MyApp')->accessToken;
                return response()->json($success, 200);
            } else {
                return $this->response_message('E-posta adresi veya parola yanlış.', 400);
            }
        } else {
            return $this->response_message('Bu e-posta adresi sistemde kayıtlı değil.', 400);
        }
    }

    public function forgot()
    {
        $validator = Validator::make(request()->all(), [
            'email' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->response_message('Lütfen tüm alanları doldurunuz.', 400);
        }

        if (!filter_var(request('email'), FILTER_VALIDATE_EMAIL)) {
            return $this->response_message('Lütfen geçerli bir e-posta adresi giriniz.', 400);
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
            return $this->response_message('Parola sıfırlama linki başarıyla gönderildi. Lütfen e-posta adresinize gelen linke tıklayınız.', 200);
        } else {
            return $this->response_message('Bu e-posta adresi sistemde kayıtlı değil.', 400);
        }
    }

    public function new_password($remember_token)
    {
        $validator = Validator::make(request()->all(), [
            'password' => 'required',
            'c_password' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->response_message('Lütfen tüm alanları doldurunuz.', 400);
        }

        if (request('password') != request('c_password'))
            return $this->response_message('Girdiğiniz parolalar eşleşmiyor.', 400);

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

            return $this->response_message('Parola sıfırlama işlemi başarıyla tamamlandı.', 200);
        } else {
            return $this->response_message('Böyle bir kullanıcı mevcut değil.', 400);
        }
    }

    public function logout()
    {
        $user = Auth::user();
        OAuthAccessToken::where('user_id', $user->id)->delete();
        return $this->response_message('Başarıyla çıkış yaptınız.', 200);
    }

    public function profile()
    {
        $user = Auth::user();
        $questions = Question::with('answers')->where('user_id', $user->id)->get();
        $answer_count_sum = 0;
        foreach ($questions as $question) {
            $answer_count = Answer::where('question_id', $question->id)->count();
            $answer_count_sum += $answer_count;
        }
        $response = (object)array(
            'username' => $user->username,
            'image' => $user->image,
            'score' => $user->score,
            'about' => $user->about,
            'question_count' => $questions->count(),
            'answer_count' => $answer_count_sum
        );
        return response()->json($response, 200);
    }

    public function update_profile()
    {
        $validator = Validator::make(request()->all(), [
            'about' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->response_message('Lütfen tüm alanları doldurunuz.', 400);
        }

        $user = Auth::user();
        $input = request()->all();

        if (request()->file('image') != null) {
            $image = request()->file('image');
            $extension = $image->getClientOriginalExtension();
            $fileName = $user->id . '_' . Carbon::now()->format('YmdHis') . '.' . $extension;
            $destinationPath = base_path() . '/public/uploads/images/' . $fileName;
            Image::make($image)->fit(120, 120)->save($destinationPath);
            // aşağıdaki kısım canlı ortama aktarırken '/public/uploads/images/' yapılacak.
            $input['image'] = '/uploads/images/' . $fileName;
            File::delete(base_path() . $user->image);
        } else {
            $input['image'] = $user->image;
        }

        $user->about = $input['about'];
        $user->image = $input['image'];

        if ($user->save()) {
            return $this->response_message('Kullanıcı başarıyla düzenlendi.', 200);
        } else {
            return $this->response_message('Kullanıcı düzenlenirken bir sorun oluştu.', 400);
        }
    }

    public function user_detail($id)
    {
        $user = User::find($id);
        $questions = Question::with('answers')->where('user_id', $user->id)->get();
        $answer_count_sum = 0;
        foreach ($questions as $question) {
            $answer_count = Answer::where('question_id', $question->id)->count();
            $answer_count_sum += $answer_count;
        }
        $response = (object)array(
            'username' => $user->username,
            'image' => $user->image,
            'score' => $user->score,
            'about' => $user->about,
            'question_count' => $questions->count(),
            'answer_count' => $answer_count_sum
        );
        return response()->json($response, 200);
    }
}