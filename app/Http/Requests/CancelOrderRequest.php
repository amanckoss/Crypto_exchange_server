<?php

namespace App\Http\Requests;

use App\Http\Requests\ApiRequest;

class CancelOrderRequest extends ApiRequest
{
    public function rules()
    {
        return [
            "stock_id" => ["required"],
            "api_token" => ["required"],
        ];
    }
}
