<?php
namespace App\Classes\Reports;

use App\Classes\Course;
use App\Classes\DataStructure\LoadFilter;
use App\Classes\DataStructure\Reports\ModuleVisualizations\ModuleItemInteraction;
use App\Classes\Membership;
use App\Interfaces\Cacheable;
use Illuminate\Support\Collection;

class ModuleVisualizations extends Report implements Cacheable {

    function __construct(Course $course){
        parent::__construct($course);
    }

    public static function defaultCacheCode() : int {
        return 6;
    }

    protected function setLoadFilter() : LoadFilter {
        $filter = new LoadFilter();
        $filter->load_grouped = true;
        $filter->columns_to_group_interactions = ['item_id','user_id'];
        return $filter;
    }

    protected function build() : void {
        $this->createReportOutputStructure();
        $this->addMoulesVisualizations();
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
            $section->visualizations_percentage = 0;
        }
        return $module;
    }

    private function addMoulesVisualizations() : void {
        foreach($this->results as $module){
            foreach($module->sections as $section){
                $this->addSectionVisualizations($section, $module->resources);
            }
        }
    }

    private function addSectionVisualizations(object $section, Collection $module_resources){
        $count_viewed_all = 0;
        foreach($section->members as $member){
            $this->addVisualizations($member, $module_resources);
            if($member->all_resources_visualized){
                $count_viewed_all++;
            }
        }
        $section->visualizations_percentage = $this->percentage($count_viewed_all, $section->members->count());
    }

    private function addVisualizations(object $member, Collection $module_resources){
        $member->all_resources_visualized = false;
        $member->module_resources = array();
        $count_visualized = 0;
        foreach ($module_resources as $resource){
            $interaction = new ModuleItemInteraction();
            $interaction->resource_canvas_id = $resource->canvas_id;
            $viewed = isset($this->interactions[$resource->content_type][$resource->id][$member->id][0]);
            if($viewed){
                $summary = $this->interactions[$resource->content_type][$resource->id][$member->id][0];
                $interaction->first_view = $summary->viewed;
            }
            $interaction->viewed = $viewed;
            array_push($member->module_resources, $interaction);
            if($viewed){
                $count_visualized++;
            }
        }
        if($count_visualized == $module_resources->count()){
            $member->all_resources_visualized = true;
        }
    }

    // OVERRIDE
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
