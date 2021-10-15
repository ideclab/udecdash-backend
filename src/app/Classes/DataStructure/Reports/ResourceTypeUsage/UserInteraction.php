<?php
namespace App\Classes\DataStructure\Reports\ResourceTypeUsage;

use Illuminate\Support\Collection;

class UserInteraction {
    public int $resource_canvas_id;
    public int $member_canvas_id;
    public bool $viewed;
    public ?string $first_view;

    function __construct(){
        $this->viewed = false;
        $this->first_view = null;
    }
}
