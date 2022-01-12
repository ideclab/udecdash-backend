<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CanvasMail extends Model
{
    use HasFactory;

    protected $table = 'canvas_mails';

    protected $fillable = ['reference_ids','options','was_sended', 'author_id'];

    public $from_token = null;

    public function getUrl() : string {
        $domain = getenv('CANVAS_URL');
        $endpoint = '/api/v1/conversations';
        if(empty($domain)){
            throw new \Exception("CANVAS_URL is not setted on .env file.");
        }
        return "{$domain}{$endpoint}";
    }
}
