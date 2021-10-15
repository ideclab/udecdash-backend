<?php
namespace App\Classes\DataStructure\Reports\CourseCommunication;

use Illuminate\Database\Eloquent\Collection;

class CommunicationSummary {
    public int $mail_messages_count;
    public int $discussion_entry_count;
    public float $mail_messages_percentage;
    public float $discussion_entry_percentage;
    public Collection $mail_messages_list;
    public Collection $discussion_entry_list;

    function __construct(){
        $this->mail_messages_count = 0;
        $this->mail_messages_list = new Collection();
        $this->mail_messages_percentage = 0;
        $this->discussion_entry_count = 0;
        $this->discussion_entry_list = new Collection();
        $this->discussion_entry_percentage = 0;
    }
}
