<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessCode extends Model
{
    use HasFactory;

    protected $table = 'access_codes';
    protected $primaryKey = 'code';
    public $incrementing = false;
    public $timestamps = false;
    public $lifetime = 120;

    protected $fillable = ['code', 'user_canvas_id', 'expired_at'];

    public function isExpired(){
        return time() > $this->expired_at;
    }
}
