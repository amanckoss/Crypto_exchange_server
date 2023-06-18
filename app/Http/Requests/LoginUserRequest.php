<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginUserRequest extends ApiRequest
{
    public function rules()
    {
        return [
            "email" => ["required"]
        ];
    }
}
