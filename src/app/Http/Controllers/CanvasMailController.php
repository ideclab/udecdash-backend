<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMailRequest;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CanvasMailController extends Controller
{
    public function sendMail (SendMailRequest $request) {
        $response = ['was_send' => false];
        $token = Auth::user()->getCanvasToken();
        $options = ['headers' => ['Authorization' => "Bearer $token"]];
        $client = new Client($options);
        $params = [
            'recipients' => implode(",", $request->recipients),
            'subject' => $request->subject,
            'body' => $request->body,
            'force_new' => true,
            'scope' => 'unread',
            'group_conversation' => $request->group_conversation
        ];
        $bulk_message_limit = 100;
        if(!$request->group_conversation && count($request->recipients) >= $bulk_message_limit){
            $params['bulk_message'] = true;
            $params['group_conversation'] = true;
        }
        $endpoint = '/api/v1/conversations';
        $res = $client->post($this->buildUrl($endpoint), ['form_params' => $params]);
        if($res->getStatusCode() == 201){
            $response['was_send'] = true;
        }
        return response()->json($response);
    }

    private function buildUrl (?string $endpoint) : string {
        $domain = getenv('CANVAS_URL');
        $endpoint = '/api/v1/conversations';
        if(empty($domain)){
            throw new Exception("CANVAS_URL is not setted on .env file.");
        }
        return "{$domain}{$endpoint}";
    }
}
