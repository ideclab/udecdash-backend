<?php

namespace App\Observers;

use App\Models\CanvasMail;
use GuzzleHttp\Client;

class CanvasMailObserver
{
    public function creating(CanvasMail $mail){
        $options = ['headers' => ['Authorization' => "Bearer $mail->from_token"]];
        $client = new Client($options);
        $params = [
            'recipients' => implode(",", $mail->options->recipients),
            'subject' => $mail->options->subject,
            'body' => $mail->options->body,
            'force_new' => true,
            'scope' => 'unread',
            'group_conversation' => $mail->options->group_conversation
        ];
        $bulk_message_limit = 100;
        if(!$mail->options->group_conversation && count($mail->options->recipients) >= $bulk_message_limit){
            $params['bulk_message'] = true;
            $params['group_conversation'] = true;
        }
        $res = $client->post($mail->getUrl(), ['form_params' => $params]);
        if($res->getStatusCode() == 201){
            $messages = json_decode($res->getBody()->getContents());
            $ids = [];
            foreach($messages as $message){
                array_push($ids, $message->id);
            }
            $mail->reference_ids = implode("," , $ids);
            $mail->was_sended = true;
        }
        $mail->options = json_encode($mail->options);
    }
}
