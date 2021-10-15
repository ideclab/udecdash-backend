<?php
namespace App\Classes\Auth;

use Illuminate\Http\Request;

class CanvasAuth {
    private string $canvas_url;
    private string $client_id;
    private string $client_secret;
    private string $response_type;
    private string $redirect_uri;

    public function __construct(){
        $this->canvas_url = env('CANVAS_URL', null);
        $this->client_id = env('CANVAS_CLIENT_ID', null);
        $this->client_secret = env('CANVAS_CLIENT_SECRET', null);
        $this->assertEnviromentVarsExists();
        $this->response_type = 'code';
        $this->redirect_uri = route('check_permission');
    }

    private function assertEnviromentVarsExists() {
        if(empty($this->canvas_url) || empty($this->client_id) || empty($this->client_secret)){
            throw new \Exception("Some canvas credentials are missing in .env file.
            Check that exists CANVAS_URL, CANVAS_CLIENT_ID and CANVAS_CLIENT_SECRET");
        }
    }

    public function getAuthorizationCodeUrl() : string {
        $url = "{$this->getAuthUrl()}?client_id={$this->client_id}&response_type={$this->response_type}&redirect_uri={$this->redirect_uri}";
        return $url;
    }

    public function getAuthUrl() : string {
        return "{$this->canvas_url}/login/oauth2/auth";
    }

    public function getTokenUrl() : string {
        return "{$this->canvas_url}/login/oauth2/token";
    }

    public function getCanvasUrl() : string {
        return $this->canvas_url;
    }

    public function getClientId() : string | int {
        return $this->client_id;
    }

    public function getClientSecret() : string {
        return $this->client_secret;
    }

    public function getChangeCodeParams(string $code) : array {
        return ["client_id" => $this->getClientId(), "client_secret" => $this->getClientSecret(),
            "code" => $code];
    }

    public static function permissionWasGranted(Request $request) : bool {
        return isset($request->code);
    }
}
