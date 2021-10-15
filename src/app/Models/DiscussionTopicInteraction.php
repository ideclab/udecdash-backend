<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscussionTopicInteraction extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $table = 'discussion_topic_interactions';
    protected $fillable = ['course_id', 'user_id', 'item_id', 'item_canvas_id', 'viewed','url',
    'device'];

}
