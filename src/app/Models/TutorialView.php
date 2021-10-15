<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TutorialView extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tutorial_views';

    protected $fillable = ['user_id','identifier'];
}
