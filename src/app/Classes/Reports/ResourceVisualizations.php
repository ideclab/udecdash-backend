<?php

namespace App\Classes\Reports;

use App\Classes\Course;
use App\Classes\DataStructure\GroupedInteractionBuilder;
use App\Classes\DataStructure\Reports\ResourceVisualizations\ResourceVisualization;
use App\Classes\DataStructure\Reports\ResourceVisualizations\ResourceView;
use App\Classes\DataStructure\LoadFilter;
use App\Classes\Membership;
use App\Classes\Reports\Report;
use App\Interfaces\Cacheable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ResourceVisualizations extends Report implements Cacheable {
    function __construct(Course $course){
        parent::__construct($course);
    }

    public static function defaultCacheCode() : int {
        return 8;
    }

    protected function setLoadFilter() : LoadFilter {
        $filter = new LoadFilter();
        $filter->load_grouped = true;
        $filter->columns_to_group_interactions = ['item_canvas_id','user_id'];
        return $filter;
    }

    protected function build() : void {
        $this->createReportOutputStructure();
        $this->addSectionsVisualizations();
    }

    private function createReportOutputStructure() : void {
        $modules = $this->course->getStructure();
        foreach($modules as $module){
            $module = $this->formatModule($module);
        }
        $this->results = $modules;
    }

    private function formatModule(object $module){
        $module->sections = $this->course->getMembership()
        ->getSectionsWithMembers(Membership::STUDENT_ROLES);
        foreach($module->sections as $section){
            $section->resources_visualizations = $this->createResourceVisualization($module->resources);
        }
        return $module;
    }

    private function createResourceVisualization(Collection $resources){
        $module_resources = array();
        foreach($resources as $resource){
            $resource_visualization = new ResourceVisualization();
            $resource_visualization->resource_canvas_id = $resource->canvas_id;
            $resource_visualization->content_type = $resource->content_type;
            array_push($module_resources, $resource_visualization);
        }
        return $module_resources;
    }

    private function addSectionsVisualizations() : void {
        foreach($this->results as $module){
            foreach($module->sections as $section){
                $this->addVisualizations($section);
            }
        }
    }

    private function addVisualizations(object $section) : void {
        foreach($section->resources_visualizations as $resource){
            $this->addMembersViews($section->members, $resource);
        }
    }

    private function addMembersViews(Collection $members, object $resource) : void {
        foreach($members as $member){
            $view = $this->getView($resource->resource_canvas_id, $member->id, $resource->content_type);
            $resource_view = new ResourceView();
            $resource_view->resource_canvas_id = $resource->resource_canvas_id;
            $resource_view->member_canvas_id = $member->canvas_id;
            if(!is_null($view)){
                $resource_view->views_count = $view->count_interactions;
                $resource->visualizations_count += $view->count_interactions;
            }
            $resource->members_visualizations->push($resource_view);
        }
    }

    private function getView(int $resource_id, int $user_id, string $resource_type) : ?object {
        $view = null;
        if(isset($this->interactions[$resource_type][$resource_id][$user_id][0])){
            $view = $this->interactions[$resource_type][$resource_id][$user_id][0];
        }
        return $view;
    }

    protected function getGroupedInteractionBuilder() : GroupedInteractionBuilder {
        $builder = parent::getGroupedInteractionBuilder();
        $builder->columns = ['course_id','user_id', 'item_id','item_canvas_id',
        DB::raw('min(viewed) as viewed'), DB::raw('count(*) as count_interactions')];
        return $builder;
    }

    protected function cleanResults(Collection|array $modules) : Collection|array {
        foreach($modules as $module){
            unset($module->id);
            unset($module->resources);
            foreach($module->sections as $section){
                unset($section->id);
                unset($section->name);
                unset($section->default_section);
                unset($section->workflow_state);
                unset($section->members);
                foreach($section->resources_visualizations as $resource_visualization){
                    unset($resource_visualization->content_type);
                }
            }
        }
        return $modules;
    }
}
