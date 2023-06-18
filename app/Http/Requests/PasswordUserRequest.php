<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PasswordUserRequest extends ApiRequest
{
    public function rules()
    {
        return [
            "password" => ["required", "min:8"],
            "api_token" => ["required"]
        ];
    }
}
