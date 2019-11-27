<?php

namespace App;

use Illuminate\Support\Str;

trait Necessary
{
    public function generate_salt()
    {
        $salt = Str::random(10);
        $isExist = User::where('salt', $salt)->first();
        return $isExist != null ? $this->generate_salt() : $salt;
    }
}