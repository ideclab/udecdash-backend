<?php

namespace App\Http\Controllers;

use App\Classes\Course;
use App\Classes\Reports\CourseCommunication;
use App\Classes\Reports\EvaluationPanic;
use App\Classes\Reports\FileTypeUsage;
use App\Classes\Reports\InteractionByResource;
use App\Classes\Reports\ModuleVisualizations;
use App\Classes\Reports\ResourceTypeUsage;
use App\Classes\Reports\ResourceVisualizations;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ReportsManagementController extends Controller
{
    public function rebuildReports($course_canvas_id) : void {
        $course = new Course($course_canvas_id);
        $this->moduleVisualizationsRebuild($course);
        $this->resourceTypeUsageRebuild($course);
        $this->resourceVisualizationsRebuild($course);
        $this->interactionByResourceRebuild($course);
        $this->courseCommunicationRebuild($course);
        $this->evaluationPanicRebuild($course);
        $this->fileTypeUsageRebuild($course);
    }

    private function moduleVisualizationsRebuild(Course $course){
        $report = new ModuleVisualizations($course);
        ModuleVisualizations::clearCache($course->getCanvasId());
        $cache_key = ModuleVisualizations::getCacheIdentifier($course->getCanvasId());
        Cache::put($cache_key, $report->getResults(), env('CACHE_LIFETIME', 86000));
        Log::debug('[ReportsManagmentController::class] [moduleVisualizationsRebuild] rebuilding report',
            ["cache_key" => $cache_key, 'was_cached' => Cache::has($cache_key)]);
    }

    private function resourceTypeUsageRebuild(Course $course){
        $report = new ResourceTypeUsage($course);
        ResourceTypeUsage::clearCache($course->getCanvasId());
        $cache_key = ResourceTypeUsage::getCacheIdentifier($course->getCanvasId());
        Cache::put($cache_key, $report->getResults(), env('CACHE_LIFETIME', 86000));
        Log::debug('[ReportsManagmentController::class] [resourceTypeUsageRebuild] rebuilding report',
            ["cache_key" => $cache_key, 'was_cached' => Cache::has($cache_key)]);
    }

    private function fileTypeUsageRebuild(Course $course){
        $report = new FileTypeUsage($course);
        FileTypeUsage::clearCache($course->getCanvasId());
        $cache_key = FileTypeUsage::getCacheIdentifier($course->getCanvasId());
        Cache::put($cache_key, $report->getResults(), env('CACHE_LIFETIME', 86000));
        Log::debug('[ReportsManagmentController::class] [fileTypeUsageRebuild] rebuilding report',
            ["cache_key" => $cache_key, 'was_cached' => Cache::has($cache_key)]);
    }

    private function interactionByResourceRebuild(Course $course){
        $report = new InteractionByResource($course);
        InteractionByResource::clearCache($course->getCanvasId());
        $cache_key = InteractionByResource::getCacheIdentifier($course->getCanvasId());
        Cache::put($cache_key, $report->getResults(), env('CACHE_LIFETIME', 86000));
        Log::debug('[ReportsManagmentController::class] [interactionByResourceRebuild] rebuilding report',
            ["cache_key" => $cache_key, 'was_cached' => Cache::has($cache_key)]);
    }

    private function resourceVisualizationsRebuild(Course $course){
        $report = new ResourceVisualizations($course);
        ResourceVisualizations::clearCache($course->getCanvasId());
        $cache_key = ResourceVisualizations::getCacheIdentifier($course->getCanvasId());
        Cache::put($cache_key, $report->getResults(), env('CACHE_LIFETIME', 86000));
        Log::debug('[ReportsManagmentController::class] [resourceVisualizationsRebuild] rebuilding report',
            ["cache_key" => $cache_key, 'was_cached' => Cache::has($cache_key)]);
    }

    private function courseCommunicationRebuild(Course $course){
        $report = new CourseCommunication($course);
        CourseCommunication::clearCache($course->getCanvasId());
        $cache_key = CourseCommunication::getCacheIdentifier($course->getCanvasId());
        Cache::put($cache_key, $report->getResults(), env('CACHE_LIFETIME', 86000));
        Log::debug('[ReportsManagmentController::class] [courseCommunicationRebuild] rebuilding report',
            ["cache_key" => $cache_key, 'was_cached' => Cache::has($cache_key)]);
    }

    private function evaluationPanicRebuild(Course $course){
        $report = new EvaluationPanic($course);
        EvaluationPanic::clearCache($course->getCanvasId());
        $cache_key = EvaluationPanic::getCacheIdentifier($course->getCanvasId());
        Cache::put($cache_key, $report->getResults(), env('CACHE_LIFETIME', 86000));
        Log::debug('[ReportsManagmentController::class] [evaluationPanicRebuild] rebuilding report',
            ["cache_key" => $cache_key, 'was_cached' => Cache::has($cache_key)]);
    }

}
