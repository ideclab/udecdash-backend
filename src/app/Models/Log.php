<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['user_id', 'course_id', 'session_id', 'context',
    'report', 'deep', 'params', 'reference','created_at'];
}
