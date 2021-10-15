<?php

namespace App\Models;

use App\Classes\Auth\CanvasAuth;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CanvasToken extends Model
{
    use HasFactory;

    protected $table = 'canvas_tokens';
    protected $primaryKey = 'user_canvas_id';
    public $incrementing = false;
    public $timestamps = false;
    private $leeway = 120;

    protected $fillable = ['user_canvas_id', 'access_token', 'token_type', 'refresh_token',
    'expires_in','created_at'];


    public function isValid() : bool {
        $expired_at =  $this->created_at + ($this->expires_in - $this->leeway);
        return time() < $expired_at;
    }

    public function refresh() : void {
        $canvas_auth = new CanvasAuth();
        $client = new Client();
        $params = [
            'grant_type' => 'refresh_token',
            'client_id' => $canvas_auth->getClientId(),
            'client_secret' => $canvas_auth->getClientSecret(),
            'redirect_uri' => route('check_permission'),
            'refresh_token' => $this->refresh_token
        ];
        $res = $client->request('POST', $canvas_auth->getTokenUrl(), ['form_params' => $params]);
        if($res->getStatusCode() == 200){
            $payload = json_decode($res->getBody()->getContents());
            $this->access_token = $payload->access_token;
            $this->expires_in = $payload->expires_in;
            $this->created_at = time();
            $this->update();
        }
    }
}
