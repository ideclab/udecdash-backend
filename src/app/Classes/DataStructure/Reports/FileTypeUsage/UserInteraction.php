<?php
namespace App\Classes\DataStructure\Reports\FileTypeUsage;

use Illuminate\Support\Collection;

class UserInteraction {
    public int $resource_canvas_id;
    public int $member_canvas_id;
    public bool $viewed;
    public ?string $first_view;
    public int $downloads_count;

    function __construct(){
        $this->viewed = false;
        $this->first_view = null;
        $this->downloads_count = 0;
    }
}

