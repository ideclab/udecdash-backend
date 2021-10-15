<?php

namespace App\Classes\Reports;

use App\Classes\Course;
use App\Classes\DataStructure\Reports\FileTypeUsage\FileInteraction;
use App\Classes\DataStructure\LoadFilter;
use App\Classes\DataStructure\Reports\FileTypeUsage\FileInterest;
use App\Classes\DataStructure\Reports\FileTypeUsage\ResourceInteraction;
use App\Classes\DataStructure\Reports\FileTypeUsage\UserInteraction;
use App\Classes\DataStructure\Reports\FileTypeUsage\VisualizationSummary;
use App\Classes\DataStructure\GroupedInteractionBuilder;
use App\Classes\Membership;
use App\Classes\MimeType;
use App\Classes\Reports\Report;
use App\Classes\ResourceType;
use App\Interfaces\Cacheable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FileTypeUsage extends Report implements Cacheable {
    protected $existing_attachments_count;

    function __construct(Course $course){
        parent::__construct($course);
        $this->existing_attachments_count = 0;
    }

    public static function defaultCacheCode() : int {
        return 4;
    }

    protected function build() : void {
        $this->createReportOutputStructure();
        $this->setUsage();
        $this->setFileTypePercentages();
    }

    protected function setLoadFilter() : LoadFilter {
        $filter = new LoadFilter();
        $filter->load_grouped = true;
        $filter->columns_to_group_interactions = ['item_canvas_id','user_id'];
        return $filter;
    }

    protected function createReportOutputStructure() : void {
        $attachments = $this->getAllAttachments();
        $file_categories = MimeType::availableCategories();
        $structure = [];
        foreach($file_categories as $category){
            $file_interaction = new FileInterest();
            $file_interaction->sections = $this->course->getMembership()
            ->getSectionsWithMembers(Membership::STUDENT_ROLES);
            $structure[$category] = $file_interaction;
            $file_interaction->resources = $attachments->filter(function($resource) use ($category){
                return $resource->file_type == $category;
            })->values();
            $file_interaction->file_count = $file_interaction->resources->count();
            $this->addResourcesToSections($file_interaction->sections, $file_interaction->resources);
        }
        $this->results = collect($structure);
    }

    private function addResourcesToSections(Collection $sections, Collection $resources){
        foreach($sections as $section){
            $section->resources = array();
            foreach($resources as $resource){
                $resource_interaction = new ResourceInteraction();
                $resource_interaction->resource_canvas_id = $resource->canvas_id;
                array_push($section->resources, $resource_interaction);
            }
        }
    }

    protected function getAllAttachments() : Collection {
        $resources = $this->course->getAttachments();
        $this->existing_attachments_count = $resources->count();
        foreach($resources as $resource){
            $resource->file_type = MimeType::discovery($resource->mime_type);
        }
        return $resources;
    }

    private function setUsage() : void {
        foreach($this->results as $category_name => $file_interest){
            foreach($file_interest->sections as $section){
                $this->addSectionUse($section, $category_name);
            }
        }
    }

    private function addSectionUse(object $section) : void {
        $members_visualizations_sum = 0;
        $downloads_count  = 0;
        foreach($section->resources as $resource){
            $summary = $this->addVisualizations($section->members, $resource);
            $resource->members_visualizations_percentage = $this->percentage($summary->views_count,
            $section->members->count());
            $members_visualizations_sum += $resource->members_visualizations_percentage;
            $resource->members_downloads_count = $summary->downloads_count;
            $downloads_count += $summary->downloads_count;
        }
        $section->downloads_count = $downloads_count;
        $section->resource_type_usage_percentage = $this->average($members_visualizations_sum,
        count($section->resources));
    }

    private function addVisualizations(object $members, ResourceInteraction $resource) : VisualizationSummary {
        $summary = new VisualizationSummary();
        foreach($members as $member){
            $view = $this->getView($resource->resource_canvas_id, $member->id);
            $interaction = new UserInteraction();
            $interaction->resource_canvas_id = $resource->resource_canvas_id;
            $interaction->member_canvas_id = $member->canvas_id;
            $interaction->viewed = !empty($view);
            if(!empty($view)){
                $interaction->downloads_count = $view->count_downloads;
                $interaction->first_view = $view->viewed;
                $summary->views_count++;
                $summary->downloads_count += $view->count_downloads;
            }
            $resource->members_visualizations->push($interaction);
        }
        return $summary;
    }

    private function getView(int $resource_id, int $user_id) : ?object {
        $view = null;
        if(isset($this->interactions[ResourceType::ATTACHMENT][$resource_id][$user_id][0])){
            $view = $this->interactions[ResourceType::ATTACHMENT][$resource_id][$user_id][0];
        }
        return $view;
    }

    private function setFileTypePercentages() : void {
        foreach($this->results as $category){
            $category->file_percentage = $this->percentage($category->file_count,
            $this->existing_attachments_count);
        }
    }

    // Override method for not load all types of interactions
    protected function loadInteractions() : void {
        $this->interactions = [
            ResourceType::ATTACHMENT => $this->getInteractions(ResourceType::ATTACHMENT)
        ];
    }

    protected function getGroupedInteractionBuilder() : GroupedInteractionBuilder {
        $builder = parent::getGroupedInteractionBuilder();
        $builder->columns = ['course_id','user_id', 'item_id','item_canvas_id',
        DB::raw('min(viewed) as viewed'), DB::raw('sum(downloaded::int) as count_downloads')];
        return $builder;
    }

    protected function cleanResults(Collection|array $files_type) : Collection|array {
        foreach($files_type as $file_type){
            foreach($file_type->resources as $resource){
                unset($resource->id);
                unset($resource->display_name);
                unset($resource->mime_type);
                unset($resource->created_at);
                unset($resource->module_id);
                unset($resource->content_type);
                unset($resource->file_type);
            }
            foreach($file_type->sections as $section){
                unset($section->id);
                unset($section->name);
                unset($section->default_section);
                unset($section->workflow_state);
                unset($section->members);
            }
        }
        return $files_type;
    }
}
