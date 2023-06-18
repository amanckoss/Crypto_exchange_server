<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaketerOrderReqest extends ApiRequest
{
    public function rules()
    {
        return [
            "stock_name" => ["required"],
            "api_token" => ["required"],
            "amount" => ["required"],
            "operation" => ["required"]
        ];
    }
}
