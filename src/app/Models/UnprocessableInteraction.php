<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnprocessableInteraction extends Model
{
    public $timestamps = false;
    protected $table = 'unprocessable_interactions';
    protected $fillable = ['course_id', 'user_id','error_label', 'url', 'error_message', 'log'];
}
