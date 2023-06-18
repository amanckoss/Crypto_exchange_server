<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DataUserRequest extends ApiRequest
{
    public function rules()
    {
        return [
            "api_token" => ["required"]
        ];
    }
}
