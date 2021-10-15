<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentInteraction extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'assignment_interactions';
    protected $fillable = ['course_id', 'user_id', 'item_id', 'item_canvas_id', 'url',
    'viewed', 'device'];
}
