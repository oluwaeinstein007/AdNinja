<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'currency_code',
        'rate',
        'created_at',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
