<?php
namespace App\Classes\DataStructure\Reports\ResourceVisualizations;

class ResourceView  {
    public int $resource_canvas_id;
    public int $member_canvas_id;
    public int $views_count;

    function __construct(){
        $this->views_count = false;
    }
}
