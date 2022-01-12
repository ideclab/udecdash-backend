<?php

namespace App\Http\Controllers;

use App\Classes\Auth\CanvasAuth;
use App\Models\AccessCode;
use App\Models\CanvasToken;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    const ADMIN_USERS_ID = [121, 79];

    public function index (){
        $canvas_auth = new CanvasAuth();
        $auth_url = $canvas_auth->getAuthorizationCodeUrl();
        return View('login')->with(['authorization_url' => $auth_url]);
    }

    public function logout(){
        $response = ['session_close' => false];
        if(Auth::user()->tokens()->delete()){
            $response['session_close'] = true;
        }
        return response()->json($response);
    }

    public function redeemCode(string $code){
        $response = ["user" => null, "token" => null];
        $access_code = AccessCode::find($code);
        $user = User::find($access_code?->user_canvas_id);
        if(!empty($user) && !$access_code->isExpired()){
            $user->tokens()->delete();
            $response["user"] = $user->summary();
            $scopes = $this->buildTokenScopes($user);
            $token = $user->createToken('authentication', $scopes);
            $response["token"] = $token->plainTextToken;
        }
        return response()->json($response);
    }

    private function buildTokenScopes(User $user) : array {
        $scopes = ['role:teacher'];
        if(in_array($user->canvas_id, self::ADMIN_USERS_ID)){
            array_push($scopes, 'role:admin');
        }
        return $scopes;
    }

    public function test(){
        return response()->json(["oks" => "oks?"]);
    }

    public function checkPermission(Request $request){
        $permission_was_granted = CanvasAuth::permissionWasGranted($request);
        if($permission_was_granted){
            $canvas_auth = new CanvasAuth();
            $params = $canvas_auth->getChangeCodeParams($request->code);
            $options = ['form_params' => $params];
            try{
                $client = new Client();
                $res = $client->request('POST', $canvas_auth->getTokenUrl(), $options);
                if($res->getStatusCode() == 200){
                    $payload = json_decode($res->getBody()->getContents());
                    $user_canvas_id = $payload?->user?->id;
                    $user = User::find($user_canvas_id);
                    if(empty($user)){
                        return $this->rejectedPermission('USER_NOT_EXIST');
                    }else if (!$user->isTeacherInSomeCourse()){
                        return $this->rejectedPermission('NOT_IS_TEACHER');
                    }else{
                        CanvasToken::updateOrCreate(['user_canvas_id' => $user->canvas_id],
                        ['access_token' => $payload->access_token,
                        'token_type' => $payload->token_type,
                        'refresh_token' => $payload->refresh_token,
                        'expires_in' => $payload->expires_in, 'created_at' => time()]);
                        $access_code = AccessCode::create(['user_canvas_id' => $user->canvas_id]);
                        return $this->grantedPermission($access_code->code);
                    }
                }else{
                    return $this->rejectedPermission('LOCAL_AUTHENTICATION_FAILED');
                }
            }catch(\Exception $e){
                return $this->rejectedPermission('TOKEN_BUILDING_ERROR');
            }
        }else{
            return $this->rejectedPermission('PERMISSION_REJECTED');
        }
    }

    private function rejectedPermission(string $code = "null"){
        $url = "{$this->getFrontendUrl()}/permission_rechazed/$code";
        return redirect($url);
    }

    private function grantedPermission(string $token){
        $url = "{$this->getFrontendUrl()}/login/$token";
        return redirect($url);
    }

    private function getFrontendUrl() : string {
        $url = env('FRONTEND_URL', null);
        if(is_null($url)){
            abort(404);
        }
        return $url;
    }


}
