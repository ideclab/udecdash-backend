<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizInteraction extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'quiz_interactions';
    protected $fillable = ['course_id', 'user_id', 'item_id', 'item_canvas_id', 'url',
    'viewed', 'device'];
}
