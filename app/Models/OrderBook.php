<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_id',
        'trader_id',
        'amount',
        'operation',
        'price'
    ];

    protected $hidden = [

    ];
}
