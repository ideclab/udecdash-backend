<?php
namespace App\Classes\DataStructure\Reports\InteractionByResource;

class ResourceInteraction {
    public int $resource_canvas_id;
    public float $visualization_percentage;
    public int $viewed_resources_count;

    function __construct(){
        $this->visualization_percentage = 0;
        $this->viewed_resources_count = 0;
    }
}
