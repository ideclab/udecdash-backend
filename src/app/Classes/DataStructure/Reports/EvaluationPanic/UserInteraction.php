<?php
namespace App\Classes\DataStructure\Reports\EvaluationPanic;

class UserInteraction {
    public int $member_canvas_id;
    public int $resource_canvas_id;
    public int $views_count;

    function __construct(){
        $this->count_views = 0;
    }

}
