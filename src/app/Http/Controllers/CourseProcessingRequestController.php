<?php

namespace App\Http\Controllers;

use App\Classes\Membership;
use App\Classes\QueueManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessCourseInteractions;
use App\Models\CourseProcessingRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CourseProcessingRequestController extends Controller {

    public function addCourseToQueue(int $courseId){
        $response = ["was_added" => false, "code" => null];
        $course = DB::table('course_dim')->where('canvas_id', $courseId)->first();
        if(empty($course)){
            $response["code"] = "COURSE_NOT_FOUND";
            return response()->json($response);
        }
        $has_pending_job = QueueManager::hasPendingJob($course->canvas_id);
        if(!env('APP_DEBUG', false) && $has_pending_job){
            $response["code"] = "HAS_PENDING_JOB";
            return response()->json($response);
        }
        $was_processed_recently  = QueueManager::wasProcessedRecently($course->canvas_id);
        if(!env('APP_DEBUG', false) && $was_processed_recently){
            $response["code"] = "WAS_PROCESSED_RECENTLY";
            return response()->json($response);
        }
        $request = CourseProcessingRequest::create(['course_id' => $course->id,
        'user_id' => Auth::user()->id,'course_canvas_id' => $course->canvas_id]);
        $queue_manager = new QueueManager($request, $course->id);
        $response["was_added"] = $queue_manager->add();
        $response["minutes_estimated"] = $queue_manager->getEstimatedTime();
        if(!$response["was_added"]){
            $response["code"] = "COURSE_TOO_LONG";
        }
        return response()->json($response);
    }

    public function restoreUnprocessableInteractions(){
        CourseProcessingRequest::restoreUnprocessableInteractions();
    }
}
