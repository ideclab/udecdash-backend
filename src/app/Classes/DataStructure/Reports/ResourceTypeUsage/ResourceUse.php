<?php
namespace App\Classes\DataStructure\Reports\ResourceTypeUsage;

use Illuminate\Support\Collection;

class ResourceUse {
    public Collection $resources;
    public Collection $sections;
    public int $resources_count;
    public float $resources_percentage;

    function __construct(){
        $this->resources = new Collection();
        $this->sections = new Collection();
        $this->resources_count = 0;
        $this->resources_percentage = 0;
    }
}
