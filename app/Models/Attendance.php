<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'Attendance';
    protected $fillable = [
        'user_id',
        'check_in',
        'check_out'
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
