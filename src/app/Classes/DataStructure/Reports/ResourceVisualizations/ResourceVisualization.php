<?php
namespace App\Classes\DataStructure\Reports\ResourceVisualizations;

use Illuminate\Database\Eloquent\Collection;

class ResourceVisualization {
    public int $resource_canvas_id;
    public string $content_type;
    public int $visualizations_count;
    public Collection $members_visualizations;

    function __construct(){
        $this->visualizations_count = 0;
        $this->members_visualizations = new Collection();
    }
}
