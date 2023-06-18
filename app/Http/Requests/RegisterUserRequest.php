<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends ApiRequest
{
    public function rules()
    {
        return [
            "first_name" => ["required"],
            "surname" => ["required"],
            "email" => ["required", "unique:users"],
            "password" => ["required", "min:8", "confirmed"]
        ];
    }
}
