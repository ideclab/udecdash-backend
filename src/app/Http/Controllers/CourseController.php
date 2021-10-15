<?php

namespace App\Http\Controllers;

use App\Classes\Course;
use App\Classes\Export\ReportExport;
use App\Classes\Helpers\CourseNameFormatter;
use Carbon\Carbon;

class CourseController extends Controller
{
    public function getInformation(int $course_canvas_id){
        $course = new Course($course_canvas_id);
        $response = [
            'canvas_id' => $course->getCanvasId(),
            'course_name' => $course->getName(),
            'udec_format' => $course->getInformation(),
            'code' => $course->getCode(),
            'workflow_state' => $course->getWorkflowState(),
            'sections' => $course->getMembership()->getSectionsWithMembers()->values(),
            'structure' => $course->getStructure()->values()
        ];
        return response()->json($response);
    }

    public function currentCourses(){
        $courses = auth()->user()?->coursesWhereIsTeacher();
        $ids = $courses->pluck('course_canvas_id')->toArray();
        $status = Course::getLastUpdateRequest($ids);
        foreach($courses as $course){
            $course->udec_format = (new CourseNameFormatter($course->course_name))->getSummary();
            $course->update = new \StdClass();
            $course->update->status = 'NEVER_UPDATED';
            $course->update->created_at = null;
            $course->update->finished_at = null;
        }
        if(!$status->isEmpty()){
            $status = $status->groupBy('course_canvas_id')->toArray();
            foreach($courses as $course){
                if(isset($status[$course->course_canvas_id]) &&
                    isset($status[$course->course_canvas_id][0])){
                        $update = $status[$course->course_canvas_id][0];
                        $course->update->status = $update->process_status;
                        $course->update->created_at = $this->toChileTz($update->created_at);
                        $course->update->finished_at = $this->toChileTz($update->finished_at);
                }
            }
        }
        $terms = $courses->groupBy('enrollment_term_dim_id');
        $output = array();
        foreach($terms as $term_id => $courses){
            $term = new \StdClass();
            $term->id = "id_$term_id";
            $term->name = null;
            $term->courses = array();
            foreach($courses as $course){
                $term->name = $course?->term_name;
                if($term->name === "Default Term"){
                    $term->name = "Global";
                }
                unset($course->term_name);
                unset($course->enrollment_term_dim_id);
                array_push($term->courses, $course);
            }
            array_push($output, $term);
        }
        return response()->json($output);
    }

    private function toChileTz(?string $date, string $format = 'Y-m-d H:i:s'){
        if(empty($date)) return null;
        $date = Carbon::createFromFormat($format, $date, 'UTC');
        $date->setTimezone('America/Santiago');
        return $date->format('Y-m-d H:i:s');
    }
}
