<?php

namespace App\Classes\Reports;

use App\Classes\Course;
use App\Classes\DataStructure\Reports\InteractionByResource\ResourceView;
use App\Classes\DataStructure\Reports\InteractionByResource\ResourceInteraction;
use App\Classes\DataStructure\LoadFilter;
use App\Classes\Membership;
use App\Classes\Reports\Report;
use App\Interfaces\Cacheable;
use Illuminate\Support\Collection;

class InteractionByResource extends Report implements Cacheable {
    function __construct(Course $course){
        parent::__construct($course);
    }

    public static function defaultCacheCode() : int {
        return 5;
    }

    protected function setLoadFilter() : LoadFilter {
        $filter = new LoadFilter();
        $filter->load_grouped = true;
        $filter->columns_to_group_interactions = ['item_id','user_id'];
        return $filter;
    }

    protected function build() : void {
        $this->createReportOutputStructure();
        $this->addSectionsVisualizations();
        $this->calculateViewsPercentages();
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
            $section->resources_interaction = $this->createResourceInteractions($module->resources);
            $section->members_count = $section->members->count();
        }
        return $module;
    }

    private function createResourceInteractions(Collection $resources){
        $module_resources = array();
        foreach($resources as $resource){
            $viewed_resource = new ResourceInteraction();
            $viewed_resource->resource_canvas_id = $resource->canvas_id;
            $module_resources[$viewed_resource->resource_canvas_id] = $viewed_resource;
        }
        return $module_resources;
    }

    private function addSectionsVisualizations() : void {
        foreach($this->results as $module){
            foreach($module->sections as $section){
                $this->addVisualizations($section, $module->resources);
            }
        }
    }

    private function addVisualizations(object $section, Collection $module_resources){
        foreach($section->members as $member){
            $this->setVisualizedResources($member, $section, $module_resources);
        }
    }

    private function setVisualizedResources(object $member, object $section, Collection $module_resources){
        $member->module_resources = array();
        foreach ($module_resources as $resource){
            $interaction = new ResourceView();
            $interaction->resource_canvas_id = $resource->canvas_id;
            $viewed = isset($this->interactions[$resource->content_type][$resource->id][$member->id][0]);
            if($viewed){
                $view = $this->interactions[$resource->content_type][$resource->id][$member->id][0];
                $interaction->first_view = $view->viewed;
            }
            if(isset($section->resources_interaction[$resource->canvas_id]->viewed_resources_count)
                && $viewed){
                $section->resources_interaction[$resource->canvas_id]->viewed_resources_count++;
            }
            $interaction->viewed = $viewed;
            array_push($member->module_resources, $interaction);
        }
    }

    private function calculateViewsPercentages(){
        foreach($this->results as $module){
            foreach ($module->sections as $section){
                $this->addViewPercentage($section);
            }
        }
    }

    private function addViewPercentage(object $section){
        foreach($section->resources_interaction as $canvas_id => $resource){
            $resource->visualization_percentage = $this->percentage($resource->viewed_resources_count,
            $section->members_count);
            $section->resources_interaction = array_values($section->resources_interaction);
        }
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
                foreach($section->members as $member){
                    unset($member->id);
                    unset($member->course_section_id);
                    unset($member->role_type);
                }
            }
        }
        return $modules;
    }
}
