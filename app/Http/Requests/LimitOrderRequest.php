<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LimitOrderRequest extends ApiRequest
{
    public function rules()
    {
        return [
            "api_token" => ["required"],
            "stock_id" => ["required"],
            "amount" => ["required"],
            "price" => ["required"]
        ];
    }
}
