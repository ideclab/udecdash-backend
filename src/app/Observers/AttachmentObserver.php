<?php

namespace App\Observers;
use App\Models\AttachmentInteraction;

class AttachmentObserver
{
    public function creating(AttachmentInteraction $attachment){
        $attachment->downloaded = $this->isDownloaded($attachment->url);
    }


    private function isDownloaded(string $url){
        $isDownloaded = false;
        if(!empty($url)){
            $pattern = "/\/download\?(.)*/";
            $isDownloaded = preg_match($pattern, $url);
        }
        return $isDownloaded;
    }
}
