<?php

use App\Http\Controllers\CanvasMailController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseProcessingRequestController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\devController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TutorialsController;

Route::get('login', function () { abort(401); })->name('login');

Route::group(['prefix' => 'account'], function () {
    Route::get('/redeem_code/{code}', [LoginController::class, 'redeemCode']);
    Route::get('/logout', [LoginController::class, 'logout'])
        ->middleware(['auth:sanctum'])->name('logout');
});

Route::group(['prefix' => 'courses', 'middleware' => ['auth:sanctum']], function () {
    // Route::get('/restore/unprocessable_interactions', [CourseProcessingRequestController::class, 'restoreUnprocessableInteractions']);
    Route::get('/process_data/{courseId}', [CourseProcessingRequestController::class, 'addCourseToQueue'])
        ->middleware(['teacher.enrolled.in.course']);
    Route::get('/information/{courseId}', [CourseController::class, 'getInformation'])
        ->middleware(['teacher.enrolled.in.course']);
    Route::get('/list', [CourseController::class, 'currentCourses']);
});


Route::group(['prefix' => 'canvas', 'middleware' => ['auth:sanctum']], function () {
    Route::post('/send_mail', [CanvasMailController::class, 'sendMail']);
});

Route::group(['prefix' => 'logs', 'middleware' => ['auth:sanctum']], function () {
    Route::post('/create', [LogController::class, 'create']);
});

Route::group(['prefix' => 'reports', 'middleware' => ['auth:sanctum', 'teacher.enrolled.in.course']], function () {
    Route::get('/module_visualizations/{courseId}/{sectionId}', [ReportsController::class, 'moduleVisualizations']);
    Route::get('/resource_type_usage/{courseId}/{sectionId}', [ReportsController::class, 'resourceTypeUsage']);
    Route::get('/file_type_usage/{courseId}/{sectionId}', [ReportsController::class, 'fileTypeUsage']);
    Route::get('/interaction_by_resource/{courseId}/{sectionId}', [ReportsController::class, 'interactionByResource']);
    Route::get('/resource_visualizations/{courseId}/{sectionId}', [ReportsController::class, 'resourceVisualizations']);
    Route::get('/course_interactions/{courseId}/{sectionId}/{year}/{month}', [ReportsController::class, 'courseInteractions']);
    Route::get('/course_interactions/{courseId}/{sectionId}/{year}/{month}/summary', [ReportsController::class, 'courseInteractionsSummary']);
    Route::get('/course_communication/{courseId}/{sectionId}', [ReportsController::class, 'courseCommunication']);
    Route::get('/evaluation_panic/{courseId}/{sectionId}', [ReportsController::class, 'evaluationPanic']);
});

Route::group(['prefix' => 'tutorial', 'middleware' => ['auth:sanctum']], function () {
    Route::get('/viewed', [TutorialsController::class, 'viewed']);
    Route::post('/mark_as_viewed', [TutorialsController::class, 'markAsViewed']);
    Route::post('/reset_all', [TutorialsController::class, 'resetAll']);
});


Route::get('/dev/{courseId}', [devController::class, 'rebuildReports']);
Route::get('/test', [devController::class, 'test'])->middleware('auth:sanctum');
Route::get('/all_courses_to_queue', [devController::class, 'processAllCourses']);
