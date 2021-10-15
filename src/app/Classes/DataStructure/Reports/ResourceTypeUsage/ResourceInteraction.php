<?php
namespace App\Classes\DataStructure\Reports\ResourceTypeUsage;

use Illuminate\Support\Collection;

class ResourceInteraction {
    public int $resource_canvas_id;
    public float $use_percentage;
    public Collection $members_visualizations;

    function __construct(){
        $this->resource_canvas_id = 0;
        $this->members_visualizations = new Collection();
    }
}
