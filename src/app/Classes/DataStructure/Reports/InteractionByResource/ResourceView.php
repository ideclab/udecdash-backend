<?php
namespace App\Classes\DataStructure\Reports\InteractionByResource;

class ResourceView  {
    public int $resource_canvas_id;
    public bool $viewed;
    public ?string $first_view;

    function __construct(){
        $this->resource_canvas_id = 0;
        $this->viewed = false;
        $this->first_view = null;
    }
}
