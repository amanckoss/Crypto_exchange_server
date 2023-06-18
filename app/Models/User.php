<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use phpDocumentor\Reflection\Types\Boolean;

class User extends Authenticatable
{
    use HasFactory;
    protected $fillable = [
        'id',
        'first_name',
        'surname',
        'email',
        'password',
        'money',
        'api_token',
        'status'
    ];

    protected $hidden = [
        'password', 'remember_token'
    ];

    public static function rtrieve(String $api_token): User {
        return User::where('api_token', $api_token)->first();
    }
}
