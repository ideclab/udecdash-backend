<?php
namespace App\Classes\DataStructure\Reports\EvaluationPanic;

class ResourceInteraction {
    public int $resource_canvas_id;
    public int $distinct_members_count;
    public int $members_visualization_percentage;
    public int $all_visualizations_count;
    public array $members_interactions;

    function __construct(){
        $this->distinct_members_count = 0;
        $this->members_visualization_percentage = 0;
        $this->all_visualizations_count = 0;
        $this->members_interactions = [];
    }

}
