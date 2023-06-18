<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Closure;

class Authenticate extends Middleware
{

//    public function handle($request, Closure $next)
//    {
//
//    }

//    protected function redirectTo($request)
//    {
//        $token = $request->bearerToken();
//        $user = User::where('api_token', $token)->first();
//        if ($user) {
//            auth()->login($user);
//            return $request;
//        }
//        return response([
//            'message' => 'Unauthenticated'
//        ], 403);
//    }

    protected function unauthenticated($request, array $guards)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'message' => 'Unauthorized'
        ], 403));
    }
}
