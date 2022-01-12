<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMailRequest;
use App\Models\CanvasMail;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CanvasMailController extends Controller
{
    public function sendMail (SendMailRequest $request) {
        $response = ['was_send' => false];
        $mail = new CanvasMail();
        $mail->options = (object) [
            'recipients' => $request->recipients,
            'subject' => $request->subject,
            'body' => $request->body,
            'force_new' => true,
            'scope' => 'unread',
            'group_conversation' => $request->group_conversation
        ];
        $mail->from_token = Auth::user()->getCanvasToken();
        $mail->author_id = Auth::id();
        try{
            $mail->save();
            $response = ['was_send' => true];
        }catch(\Exception $e){
        }
        return response()->json($response);
    }
}
