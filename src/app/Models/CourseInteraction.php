<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseInteraction extends Model
{
    public $incrementing = false;
    public $timestamps = false;
    protected $table = 'course_interactions';
    protected $fillable = ['course_id', 'user_id', 'interaction_date','year_month'];
}
