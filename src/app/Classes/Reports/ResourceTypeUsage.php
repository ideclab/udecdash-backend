<?php

namespace App\Classes\Reports;

use App\Classes\Course;
use App\Classes\DataStructure\LoadFilter;
use App\Classes\DataStructure\Reports\ResourceTypeUsage\ResourceInteraction;
use App\Classes\DataStructure\Reports\ResourceTypeUsage\ResourceUse;
use App\Classes\DataStructure\Reports\ResourceTypeUsage\UserInteraction;
use App\Classes\Membership;
use App\Classes\Reports\Report;
use App\Classes\ResourceType;
use App\Interfaces\Cacheable;
use Illuminate\Support\Collection;

class ResourceTypeUsage extends Report implements Cacheable {

    function __construct(Course $course){
        parent::__construct($course);
    }

    public static function defaultCacheCode() : int {
        return 7;
    }

    protected function build() : void {
        $this->createReportOutputStructure();
        $this->addResourcesTypeUsage();
        $this->calculateResourceTypePercentage();
    }

    protected function setLoadFilter() : LoadFilter {
        $filter = new LoadFilter();
        $filter->load_grouped = true;
        $filter->columns_to_group_interactions = ['item_canvas_id','user_id'];
        return $filter;
    }

    protected function createReportOutputStructure() : void {
        $wiki_pages = $this->course->getWikipages();
        $quizzes = $this->course->getQuizzes();
        $attachments = $this->course->getAttachments();
        $discussion_topics = $this->course->getDiscussionTopics();
        $assignments = $this->course->getAssignments();
        $external_urls = $this->course->getExternalUrls();
        $external_tools = $this->course->getExternalTools();
        $this->results = collect([
            ResourceType::WIKI_PAGE => $this->buildResourceUse($wiki_pages),
            ResourceType::QUIZ =>  $this->buildResourceUse($quizzes),
            ResourceType::ATTACHMENT =>  $this->buildResourceUse($attachments),
            ResourceType::DISCUSSION_TOPIC =>  $this->buildResourceUse($discussion_topics),
            ResourceType::ASSIGNMENT =>  $this->buildResourceUse($assignments),
            ResourceType::EXTERNAL_URL =>  $this->buildResourceUse($external_urls),
            ResourceType::CONTEXT_EXTERNAL_TOOL =>  $this->buildResourceUse($external_tools)
        ]);
    }

    private function buildResourceUse(Collection $resources) : ResourceUse {
        $resource_use = new ResourceUse();
        $resource_use->resources = $resources;
        $resource_use->resources_count = $resources->count();
        $resource_use->sections = $this->course->getMembership()
        ->getSectionsWithMembers(Membership::STUDENT_ROLES);
        foreach($resource_use->sections as $section){
            $section->resources = $this->buildResourceInteraction($resources);
            $section->resource_type_use_percentage = 0;
        }
        return $resource_use;
    }

    private function buildResourceInteraction(Collection $resources) : array {
        $resource_interactions = array();
        foreach($resources as $resource){
            $resource_interaction = new ResourceInteraction();
            $resource_interaction->resource_canvas_id = $resource->canvas_id;
            array_push($resource_interactions, $resource_interaction);
        }
        return $resource_interactions;
    }

    private function addResourcesTypeUsage() : void {
        foreach($this->results as $resource_type => $resource_use){
            foreach($resource_use->sections as $section){
                $this->addSectionUse($section, $resource_type);
            }
        }
    }

    private function addSectionUse(object $section, string $resource_type) : void {
        $members_visualizations_sum = 0;
        foreach($section->resources as $resource){
            $views_count = $this->addUse($section->members, $resource, $resource_type);
            $resource->members_visualizations_percentage = $this->percentage($views_count,
            $section->members->count());
            $members_visualizations_sum += $resource->members_visualizations_percentage;
        }
        $section->resource_type_use_percentage = $this->average($members_visualizations_sum,
        count($section->resources));
    }

    private function addUse(object $members, ResourceInteraction $resource, string $resource_type) : int {
        $views_count = 0;
        foreach($members as $member){
            $view = $this->getView($resource->resource_canvas_id, $member->id, $resource_type);
            $interaction = new UserInteraction();
            $interaction->resource_canvas_id = $resource->resource_canvas_id;
            $interaction->member_canvas_id = $member->canvas_id;
            $interaction->viewed = !empty($view);
            if(!empty($view)){
                $interaction->first_view = $view->viewed;
                $views_count++;
            }
            $resource->members_visualizations->push($interaction);
        }
        return $views_count;
    }

    private function getView(int $resource_id, int $user_id, string $resource_type) : ?object {
        $view = null;
        if(isset($this->interactions[$resource_type][$resource_id][$user_id][0])){
            $view = $this->interactions[$resource_type][$resource_id][$user_id][0];
        }
        return $view;
    }

    private function calculateResourceTypePercentage(){
        $existing_resources_count = 0;
        foreach($this->results as $resource_type => $resource_use){
            $existing_resources_count += $resource_use->resources_count;
        }
        foreach($this->results as $resource_type => $resource_use){
            $resource_use->resources_percentage = $this->percentage($resource_use->resources_count,
            $existing_resources_count);
        }
    }

    protected function cleanResults(Collection|array $resources_type) : Collection|array {
        foreach($resources_type as $type => $resource_type){
            foreach($resource_type->resources as $resource){
                unset($resource->display_name);
                unset($resource->mime_type);
                unset($resource->created_at);
                unset($resource->module_id);
                unset($resource->content_type);
            }
            foreach($resource_type->sections as $section){
                unset($section->id);
                unset($section->name);
                unset($section->default_section);
                unset($section->workflow_state);
                unset($section->members);
            }
        }
        return $resources_type;
    }
}
