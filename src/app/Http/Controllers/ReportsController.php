<?php

namespace App\Http\Controllers;

use App\Classes\Course;
use App\Classes\Reports\CourseCommunication;
use App\Classes\Reports\CourseInteractions;
use App\Classes\Reports\CourseInteractionsSummary;
use App\Classes\Reports\EvaluationPanic;
use App\Classes\Reports\FileTypeUsage;
use App\Classes\Reports\InteractionByResource;
use App\Classes\Reports\ModuleVisualizations;
use App\Classes\Reports\ResourceTypeUsage;
use App\Classes\Reports\ResourceVisualizations;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportsController extends Controller
{
    public function moduleVisualizations($course_canvas_id, $section_canvas_id){
        $this->assertValidParams($course_canvas_id, $section_canvas_id);
        $cache_key = ModuleVisualizations::getCacheIdentifier($course_canvas_id);
        $modules = $this->getReportFromCache($cache_key);
        if($modules?->isNotEmpty()){
            foreach($modules as $module){
                foreach($module->sections as $index => $section){
                    if($section->canvas_id == $section_canvas_id){
                        $module->visualizations_percentage = $section->visualizations_percentage;
                        $module->members = $section->members;
                        break;
                    }
                }
                unset($module->sections);
            }
        }else{
            return abort(204, "Data not found in the cache");
        }
        return response()->json($modules->toArray());
    }

    public function resourceTypeUsage($course_canvas_id, $section_canvas_id){
        $this->assertValidParams($course_canvas_id, $section_canvas_id);
        $cache_key = ResourceTypeUsage::getCacheIdentifier($course_canvas_id);
        $resources_type = $this->getReportFromCache($cache_key);
        if($resources_type?->isNotEmpty()){
            foreach($resources_type as $resource_type){
                foreach($resource_type->sections as $section){
                    if($section->canvas_id == $section_canvas_id){
                        $resource_type->resource_type_use_percentage =
                            $section->resource_type_use_percentage;
                        $resource_type->resources_interactions = $section->resources;
                        break;
                    }
                }
                unset($resource_type->sections);
            }
        }else{
            abort(204, "Data not found in the cache");
        }
        return response()->json($resources_type->toArray());
    }

    public function fileTypeUsage($course_canvas_id, $section_canvas_id){
        $this->assertValidParams($course_canvas_id, $section_canvas_id);
        $cache_key = FileTypeUsage::getCacheIdentifier($course_canvas_id);
        $files_type = $this->getReportFromCache($cache_key);
        if($files_type?->isNotEmpty()){
            foreach($files_type as $file_type){
                foreach($file_type->sections as $section){
                    if($section->canvas_id == $section_canvas_id){
                        $file_type->resources_interactions = $section->resources;
                        $file_type->downloads_count = $section->downloads_count;
                        $file_type->resource_type_usage_percentage =
                            $section->resource_type_usage_percentage;
                        break;
                    }
                }
                unset($file_type->sections);
            }
        }else{
            abort(204, "Data not found in the cache");
        }
        return response()->json($files_type->toArray());
    }

    public function interactionByResource($course_canvas_id, $section_canvas_id){
        $this->assertValidParams($course_canvas_id, $section_canvas_id);
        $cache_key = InteractionByResource::getCacheIdentifier($course_canvas_id);
        $modules = $this->getReportFromCache($cache_key);
        if($modules?->isNotEmpty()){
            foreach($modules as $module){
                foreach($module->sections as $section){
                    if($section->canvas_id == $section_canvas_id){
                        $module->members = $section->members;
                        $module->resources_interaction = $section->resources_interaction;
                        $module->members_count = $section->members_count;
                        break;
                    }
                }
                unset($module->sections);
            }
        }else{
            abort(204, "Data not found in the cache");
        }
        return response()->json($modules->toArray());
    }

    public function resourceVisualizations($course_canvas_id, $section_canvas_id){
        $this->assertValidParams($course_canvas_id, $section_canvas_id);
        $cache_key = ResourceVisualizations::getCacheIdentifier($course_canvas_id);
        $modules = $this->getReportFromCache($cache_key);
        if($modules?->isNotEmpty()){
            foreach($modules as $module){
                foreach($module->sections as $section){
                    if($section->canvas_id == $section_canvas_id){
                        $module->resources_visualizations = $section->resources_visualizations;
                        break;
                    }
                }
                unset($module->sections);
            }
        }else{
            abort(204, "Data not found in the cache");
        }
        return response()->json($modules->toArray());
    }

    public function courseInteractions($course_canvas_id, $section_canvas_id, $year, $month){
        $cache_key = "i_$course_canvas_id$section_canvas_id$year$month";
        $result = Cache::get($cache_key, null);
        if(!empty($result)){
            return response()->json($result);
        }
        $this->assertValidParamsForInteractions($course_canvas_id, $section_canvas_id,
        $year, $month);
        $course = new Course($course_canvas_id);
        $report = new CourseInteractions($course, $section_canvas_id, $year, $month);
        $result = $report->getResults();
        Cache::put($cache_key, $result, 3600);
        return response()->json($result);
    }

    public function courseInteractionsSummary($course_canvas_id, $section_canvas_id, $year, $month){
        $cache_key = "is_$course_canvas_id$section_canvas_id$year$month";
        $result = Cache::get($cache_key, null);
        if(!empty($result)){
            return response()->json($result);
        }
        $this->assertValidParamsForInteractions($course_canvas_id, $section_canvas_id,
        $year, $month);
        $course = new Course($course_canvas_id);
        $report = new CourseInteractionsSummary($course, $section_canvas_id, $year, $month);
        $result = $report->getResults();
        Cache::put($cache_key, $result, 3600);
        return response()->json($result);
    }

    public function courseCommunication($course_canvas_id, $section_canvas_id){
        $this->assertValidParams($course_canvas_id, $section_canvas_id);
        $cache_key = CourseCommunication::getCacheIdentifier($course_canvas_id);
        $sections = $this->getReportFromCache($cache_key);
        $report = null;
        if($sections?->isNotEmpty()){
            foreach($sections as $section){
                if($section->canvas_id == $section_canvas_id){
                    $report = $section->communication;
                    break;
                }
            }
        }else{
            abort(204, "Data not found in the cache");
        }
        return response()->json($report);
    }

    public function evaluationPanic($course_canvas_id, $section_canvas_id){
        $this->assertValidParams($course_canvas_id, $section_canvas_id);
        $cache_key = EvaluationPanic::getCacheIdentifier($course_canvas_id);
        $sections = $this->getReportFromCache($cache_key);
        $report = null;
        if($sections?->isNotEmpty()){
            foreach($sections as $section){
                if($section->canvas_id == $section_canvas_id){
                    $report = $section->quizzes;
                    break;
                }
            }
        }else{
            abort(204, "Data not found in the cache");
        }
        return response()->json($report);
    }

    private function getReportFromCache($cache_identifier) : Collection | array | null {
        $response = null;
        if(Cache::has($cache_identifier)){
            $response = Cache::get($cache_identifier);
        }
        return $response;
    }

    private function assertValidParams($course_canvas_id, $section_canvas_id){
        $data = ['course_canvas_id' => $course_canvas_id, 'section_canvas_id' => $section_canvas_id];
        $validator = Validator::make($data, [
            'course_canvas_id' => 'required|int',
            'section_canvas_id' => 'required|int'
        ]);
        if($validator->fails()) {
            abort(400, "Invalid params for the route");
        }
    }

    private function assertValidParamsForInteractions($course_canvas_id, $section_canvas_id, $year, $month){
        $data = ['course_canvas_id' => $course_canvas_id, 'section_canvas_id' => $section_canvas_id,
        'year' => $year, 'month' => $month];
        $validator = Validator::make($data, [
            'course_canvas_id' => 'required|int',
            'section_canvas_id' => 'required|int',
            'year' => 'required|int',
            'month' => 'required|int'
        ]);
        if($validator->fails()) {
            abort(400, "Invalid params for the route");
        }
    }
}
