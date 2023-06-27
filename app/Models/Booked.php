<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booked extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'booking_id',
        'start_date',
        'end_date',
        'duration',
        'status',
        'price',
        'currency_code',
        'type',
        'created_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
