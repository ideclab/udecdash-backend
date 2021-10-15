<?php

namespace App\Classes\Reports;

use App\Classes\Course;
use App\Classes\DataStructure\Reports\EvaluationPanic\UserInteraction;
use App\Classes\DataStructure\Reports\EvaluationPanic\ResourceInteraction;
use App\Classes\DataStructure\GroupedInteractionBuilder;
use App\Classes\DataStructure\LoadFilter;
use App\Classes\DataStructure\Reports\EvaluationPanic\Activity;
use App\Classes\Membership;
use App\Interfaces\Cacheable;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EvaluationPanic extends Report implements Cacheable {

    function __construct(Course $course){
        parent::__construct($course);
    }

    private function removeInteractionOfNonExistenResources() : void {
        $existing_resources = $this->course->getResources()->groupBy('content_type');
        foreach($this->interactions as $type => $days){
            foreach($days as $date => $day){
                $resources_id = $day->keys()->toArray();
                foreach($resources_id as $resource_id){
                    if(isset($existing_resources[$type])){
                        $exist = $existing_resources[$type]->search(function($item) use ($resource_id){
                            return $item->canvas_id == $resource_id;
                        });
                        if($exist === false){
                            $this->interactions[$type][$date]->forget($resource_id);
                        }
                    }
                }
            }
        }
    }

    public static function defaultCacheCode() : int {
        return 3;
    }

    protected function setLoadFilter() : LoadFilter {
        $filter = new LoadFilter();
        $filter->load_grouped = true;
        $filter->columns_to_group_interactions = ['date_key','item_canvas_id','user_id'];
        return $filter;
    }

    protected function build() : void {
        $this->removeInteractionOfNonExistenResources();
        $this->createReportOutputStructure();
        $this->addSectionsInteractions();
        $this->sectionResourcesInteractionSummary();
    }

    private function createReportOutputStructure() : void {
        $quizzes = $this->course->getQuizzesWithAssignedDate();
        $this->results = $this->course->getMembership()
            ->getSectionsWithMembers(Membership::STUDENT_ROLES);
        foreach($this->results as $section){
            $section->members_count = $section->members->count();
            $section->quizzes = $this->buildActivity($quizzes);
        }
    }

    private function buildActivity(Collection $quizzes) : array {
        $quizzes_activity = array();
        foreach($quizzes as $quiz){
            $activity = new Activity($quiz);
            array_push($quizzes_activity, $activity);
        }
        return $quizzes_activity;
    }

    private function addSectionsInteractions() : void {
        foreach($this->results as $section){
            foreach($section->quizzes as $quiz_activity){
                $this->addQuizActivity($quiz_activity, $section->members);
            }
        }
    }

    private function addQuizActivity(object $quiz_activity, Collection $members) : void {
        $this->addBeforeInteractions($quiz_activity, $members);
        $this->addAfterInteractions($quiz_activity, $members);
    }

    private function addBeforeInteractions(object $quiz_activity, Collection $members) : void {
        foreach($quiz_activity->viewed_before as $date_key => $viewed_resources){
            foreach($this->interactions as $resourceType => $interactions_by_date){
                if(isset($interactions_by_date[$date_key])){
                    foreach($interactions_by_date[$date_key] as $resource_canvas_id => $members_interactions){
                        $resource_interaction = new ResourceInteraction();
                        $resource_interaction->resource_canvas_id = $resource_canvas_id;
                        $start = $quiz_activity->before_start;
                        $end = $quiz_activity->before_end;
                        $resource_interaction->members_interactions = $this->setInteractions(
                            $start, $end, $members, $members_interactions, $resource_canvas_id
                        );
                        if($this->hasInteractions($resource_interaction)){
                            array_push($quiz_activity->viewed_before[$date_key], $resource_interaction);
                        }
                    }
                }
            }
        }
    }

    private function addAfterInteractions(object $quiz_activity, Collection $members) : void {
        foreach($quiz_activity->viewed_after as $date_key => $viewed_resources){
            foreach($this->interactions as $resourceType => $interactions_by_date){
                if(isset($interactions_by_date[$date_key])){
                    foreach($interactions_by_date[$date_key] as $resource_canvas_id => $members_interactions){
                        $resource_interaction = new ResourceInteraction();
                        $resource_interaction->resource_canvas_id = $resource_canvas_id;
                        $start = $quiz_activity->after_start;
                        $end = $quiz_activity->after_end;
                        $resource_interaction->members_interactions = $this->setInteractions(
                            $start, $end, $members, $members_interactions, $resource_canvas_id
                        );
                        if($this->hasInteractions($resource_interaction)){
                            array_push($quiz_activity->viewed_after[$date_key], $resource_interaction);
                        }
                    }
                }
            }
        }
    }

    private function hasInteractions($resource_interactions) : bool {
        $has = false;
        foreach($resource_interactions->members_interactions as $member){
            if($member->count_views > 0){
                $has = true;
                break;
            }
        }
        return $has;
    }

    function setInteractions(string $start, string $end, Collection $members, Collection $interactions, int $resource_canvas_id) : array{
        $members_interactions = array();
        foreach($members as $member){
            $user_interaction = new UserInteraction();
            $user_interaction->member_canvas_id = $member->canvas_id;
            $user_interaction->resource_canvas_id = $resource_canvas_id;
            if(isset($interactions[$member->id])){
                $user_interaction->count_views = $interactions[$member->id]
                ->filter(function($e) use ($start, $end){
                    $created_at = Carbon::createFromFormat('Y-m-d H:i:s', $e->viewed, "America/Santiago");
                    return  $created_at->greaterThanOrEqualTo($start) &&
                            $created_at->lessThanOrEqualTo($end); })->count();
            }
            array_push($members_interactions, $user_interaction);
        }
        return $members_interactions;
    }

    private function sectionResourcesInteractionSummary(){
        foreach($this->results as $section){
            foreach($section->quizzes as $quiz){
                $this->calculateResourcesSummary($quiz->viewed_before, $section);
                $this->calculateResourcesSummary($quiz->viewed_after, $section);
            }
        }
    }

    private function calculateResourcesSummary(array $days, object $section) : void {
        foreach($days as $resources){
            foreach($resources as $resource){
                if(!empty($resource)){
                    $this->calculateResourceSummary($resource, $section);
                }
            }
        }
    }

    private function calculateResourceSummary(object $resource, object $section) : void {
        foreach($resource->members_interactions as $member_interaction){
            if($member_interaction->count_views > 0){
                $resource->distinct_members_count++;
                $resource->all_visualizations_count += $member_interaction->count_views;
            }
        }
        $resource->members_visualization_percentage = $this->percentage(
            $resource->distinct_members_count, $section->members_count
        );
    }

    protected function getGroupedInteractionBuilder() : GroupedInteractionBuilder {
        $builder = parent::getGroupedInteractionBuilder();
        $builder->columns = [
            'item_canvas_id','user_id', 'viewed',
            DB::raw('count (*) as resource_views_count'),
            DB::raw('to_char(viewed, \'YYYY-MM-DD\') as date_key'),
        ];
        $builder->group_by = ['date_key', 'item_canvas_id', 'user_id', 'viewed'];
        return $builder;
    }

    protected function cleanResults(Collection|array $sections) : Collection|array {
        foreach($sections as $section){
            unset($section->id);
            unset($section->name);
            unset($section->default_section);
            unset($section->workflow_state);
            unset($section->members);
        }
        return $sections;
    }
}
