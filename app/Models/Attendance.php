<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'Attendance';
    protected $fillable = [
        'user_id',
        'check_in_pc_name',
        'check_in',
        'check_out',
        'comment',
        'comment_out',
        'status',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
