<?php

namespace App;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

trait Necessary
{
    public function generate_salt()
    {
        $salt = Str::random(10);
        $isExist = User::where('salt', $salt)->first();
        return $isExist != null ? $this->generate_salt() : $salt;
    }

    public function generate_remember_token()
    {
        $remember_token = md5(Str::random(10));
        $isExist = User::where('remember_token', $remember_token)->first();
        return $isExist != null ? $this->generate_remember_token() : $remember_token;
    }

    public function send_mail($template, $data, $to, $subject)
    {
        Mail::send($template, $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
            $message->from('yazilim.xyz0@gmail.com', 'yazilim.xyz');
        });
    }

    public function response_message($text, $status_code)
    {
        $type = "";
        $title = "";
        switch ($status_code) {
            case 200:
                $type = 'success';
                $title = 'Başarılı!';
                break;
            case 400:
                $type = 'warn';
                $title = 'Uyarı!';
                break;
            case 500:
                $type = 'error';
                $title = 'Hata!';
                break;
        }
        return response()->json([
            'type' => $type,
            'title' => $title,
            'text' => $text
        ], $status_code);
    }
}