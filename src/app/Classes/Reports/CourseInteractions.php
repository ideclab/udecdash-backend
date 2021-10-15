<?php

namespace App\Classes\Reports;

use App\Classes\Course;
use App\Classes\DataStructure\GroupedInteractionBuilder;
use App\Classes\DataStructure\LoadFilter;
use App\Classes\Membership;
use App\Interfaces\Cacheable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CourseInteractions extends Report {

    private int $year;
    private int $month;
    private array $students_id;

    function __construct(Course $course, int $section_canvas_id, int $year, int $month){
        $this->year = $year;
        $this->month = $month;
        $this->students_id = Membership::getStudentIds($course->getId(), $section_canvas_id);
        parent::__construct($course);
    }

    protected function setLoadFilter() : LoadFilter {
        return new LoadFilter();
    }

    // OVERRIDE METHOD FOR NOT LOAD INTERACTIONS
    protected function loadInteractions() : void {
        $this->interactions = array();
    }

    private function getYearMonthCode(){
        if($this->month < 10){
            $code = (int) "{$this->year}0{$this->month}";
        }else{
            $code = (int) "{$this->year}{$this->month}";
        }
        return $code;
    }

    protected function build() : void {
        $interactions = $this->getCourseInteractions();
        $this->results = $this->groupInteractions($interactions);
    }

    public function getCourseInteractions() : Collection {
        $interactions = DB::table('course_interactions')
        ->select(['user_dim.canvas_id as user_canvas_id', 'user_dim.name', 'interaction_date'])
        ->join('user_dim', 'course_interactions.user_id', 'user_dim.id')
        ->where('course_interactions.course_id', $this->course->getId())
        ->where('course_interactions.year_month', $this->getYearMonthCode())
        ->whereIn('course_interactions.user_id', $this->students_id)
        ->get();
        return $interactions;
    }

    public function groupInteractions(Collection $interactions){
        $activity = array();
        foreach($interactions as $interaction){
            if(!isset($activity[$interaction->user_canvas_id])){
                $activity[$interaction->user_canvas_id] = array();
                $activity[$interaction->user_canvas_id]['canvas_id'] = $interaction->user_canvas_id;
                $activity[$interaction->user_canvas_id]['name'] = $interaction->name;
                $activity[$interaction->user_canvas_id]['interactions'] = array();
            }
            array_push($activity[$interaction->user_canvas_id]['interactions'],
            $interaction->interaction_date);
        }
        return array_values($activity);
    }

}
