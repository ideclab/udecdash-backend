<?php
namespace App\Classes\DataStructure\Reports\FileTypeUsage;

use Illuminate\Support\Collection;

class ResourceInteraction {
    public int $resource_canvas_id;
    public float $members_visualizations_percentage;
    public Collection $members_visualizations;
    public int $members_downloads_count;

    function __construct(){
        $this->members_visualizations_percentage = 0;
        $this->members_visualizations = new Collection();
        $this->members_downloads_count = 0;
    }
}
