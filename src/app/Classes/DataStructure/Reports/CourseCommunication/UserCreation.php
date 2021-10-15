<?php
namespace App\Classes\DataStructure\Reports\CourseCommunication;

use Illuminate\Database\Eloquent\Collection;

class UserCreation {
    public int $member_canvas_id;
    public int $creation_count;

    function __construct(){
        $this->creation_count = 0;
    }
}
