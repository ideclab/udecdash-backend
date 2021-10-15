<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalUrlInteraction extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'external_url_interactions';
    protected $fillable = ['course_id', 'user_id', 'item_id', 'item_canvas_id', 'viewed',
    'url' ,'device'];
}
