<?php

namespace Tests\Feature;

use App\Http\Controllers\devController;
use App\Http\Controllers\ReportsController;
use App\Models\AssignmentInteraction;
use App\Models\AttachmentInteraction;
use App\Models\ContextExternalToolInteraction;
use App\Models\DiscussionTopicInteraction;
use App\Models\ExternalUrlInteraction;
use App\Models\QuizInteraction;
use App\Models\WikiPageInteraction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\CourseBuilder;
use Tests\ExampleCourses\algebraCourse;
use Tests\TestCase;

class CourseInteractionsSummaryTest extends TestCase {
    use algebraCourse;

    public function test_month_without_interactions(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $now = Carbon::now()->format('Y-m-d H:m:s');
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $year = 2020;
        $month = 10;
        $report = $controller->courseInteractionsSummary($course->getCourseCanvasId(),1000,$year,$month);
        $result = json_decode($report->getContent());
        $days = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado", "Domingo"];
        foreach($days as $day){
            $this->assertEquals(0, $result->$day->{'Mañana'});
            $this->assertEquals(0, $result->$day->Tarde);
            $this->assertEquals(0, $result->$day->Noche);
            $this->assertEquals(0, $result->$day->Madrugada);
        }
    }

    public function test_morning_interactions(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-04 06:20:00", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(3),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-04 08:00:00", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(5),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-25 11:59:59", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(5),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-10 11:59:59", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(5),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-20 11:59:59", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseInteractionsSummary($course->getCourseCanvasId(),1000,2020,5);
        $result = json_decode($report->getContent());
        $days = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado", "Domingo"];
        foreach($days as $day){
            if($day == "Lunes"){
                $this->assertEquals(3, $result->$day->{'Mañana'});
            }else if ($day == "Miercoles"){
                $this->assertEquals(1, $result->$day->{'Mañana'});
            }else if ($day == "Domingo"){
                $this->assertEquals(1, $result->$day->{'Mañana'});
            }else{
                $this->assertEquals(0, $result->$day->{'Mañana'});
            }
            $this->assertEquals(0, $result->$day->Tarde);
            $this->assertEquals(0, $result->$day->Noche);
            $this->assertEquals(0, $result->$day->Madrugada);
        }
    }

    public function test_afternoon_interactions(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-04 12:00:00", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(3),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-04 17:59:59", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(5),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-25 14:59:59", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(5),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-10 16:05:00", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(5),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-20 12:32:29", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseInteractionsSummary($course->getCourseCanvasId(),1000,2020,5);
        $result = json_decode($report->getContent());
        $days = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado", "Domingo"];
        foreach($days as $day){
            if($day == "Lunes"){
                $this->assertEquals(3, $result->$day->{'Tarde'});
            }else if ($day == "Miercoles"){
                $this->assertEquals(1, $result->$day->{'Tarde'});
            }else if ($day == "Domingo"){
                $this->assertEquals(1, $result->$day->{'Tarde'});
            }else{
                $this->assertEquals(0, $result->$day->{'Tarde'});
            }
            $this->assertEquals(0, $result->$day->{'Mañana'});
            $this->assertEquals(0, $result->$day->Noche);
            $this->assertEquals(0, $result->$day->Madrugada);
        }
    }

    public function test_night_interactions(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-04 18:00:00", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(3),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-04 23:59:59", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(5),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-25 19:59:59", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(5),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-10 18:05:00", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(5),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-20 22:32:29", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseInteractionsSummary($course->getCourseCanvasId(),1000,2020,5);
        $result = json_decode($report->getContent());
        $days = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado", "Domingo"];
        foreach($days as $day){
            if($day == "Lunes"){
                $this->assertEquals(3, $result->$day->{'Noche'});
            }else if ($day == "Miercoles"){
                $this->assertEquals(1, $result->$day->{'Noche'});
            }else if ($day == "Domingo"){
                $this->assertEquals(1, $result->$day->{'Noche'});
            }else{
                $this->assertEquals(0, $result->$day->{'Noche'});
            }
            $this->assertEquals(0, $result->$day->{'Mañana'});
            $this->assertEquals(0, $result->$day->Tarde);
            $this->assertEquals(0, $result->$day->Madrugada);
        }
    }

    public function test_early_morning_interactions(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-04 00:00:00", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(3),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-04 03:59:59", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(5),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-25 05:59:59", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(5),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-10 04:05:00", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(5),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-20 01:32:29", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseInteractionsSummary($course->getCourseCanvasId(),1000,2020,5);
        $result = json_decode($report->getContent());
        $days = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado", "Domingo"];
        foreach($days as $day){
            if($day == "Lunes"){
                $this->assertEquals(3, $result->$day->{'Madrugada'});
            }else if ($day == "Miercoles"){
                $this->assertEquals(1, $result->$day->{'Madrugada'});
            }else if ($day == "Domingo"){
                $this->assertEquals(1, $result->$day->{'Madrugada'});
            }else{
                $this->assertEquals(0, $result->$day->{'Madrugada'});
            }
            $this->assertEquals(0, $result->$day->{'Mañana'});
            $this->assertEquals(0, $result->$day->Tarde);
            $this->assertEquals(0, $result->$day->Noche);
        }
    }

    public function test_count_interactions_of_all_resources_types(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        AssignmentInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-04 00:00:00",
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-04 00:00:00",
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        AttachmentInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(1200),
            'item_canvas_id' => 1200,
            'viewed' => "2020-05-04 00:00:00",
            'url' => 'whatever',
            'device' => 'DESKTOP',
            'downloaded' => 0
        ]);
        QuizInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(2000),
            'item_canvas_id' => 2000,
            'url' => 'whatever',
            'viewed' => "2020-05-04 00:00:00",
            'device' => 'DESKTOP'
        ]);
        ContextExternalToolInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(9000),
            'item_canvas_id' => 9000,
            'viewed' => "2020-05-04 00:00:00",
            'url' => 'whatever',
            'device' => 'DESKTOP'
        ]);
        DiscussionTopicInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-04 00:00:00",
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        ExternalUrlInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-04 00:00:00",
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseInteractionsSummary($course->getCourseCanvasId(),1000,2020,5);
        $result = json_decode($report->getContent());
        $days = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado", "Domingo"];
        foreach($days as $day){
            if($day == "Lunes"){
                $this->assertEquals(7, $result->$day->{'Madrugada'});
            }else{
                $this->assertEquals(0, $result->$day->{'Madrugada'});
            }
            $this->assertEquals(0, $result->$day->{'Mañana'});
            $this->assertEquals(0, $result->$day->Tarde);
            $this->assertEquals(0, $result->$day->Noche);
        }
    }

    public function test_dont_merge_interactions_with_old_interactions(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-04 06:20:00", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(5),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2019-05-20 11:00:00", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseInteractionsSummary($course->getCourseCanvasId(),1000,2020,5);
        $result = json_decode($report->getContent());
        $days = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado", "Domingo"];
        foreach($days as $day){
            if($day == "Lunes"){
                $this->assertEquals(1, $result->$day->{'Mañana'});
            }else{
                $this->assertEquals(0, $result->$day->{'Mañana'});
            }
            $this->assertEquals(0, $result->$day->Tarde);
            $this->assertEquals(0, $result->$day->Noche);
            $this->assertEquals(0, $result->$day->Madrugada);
        }
    }

    public function test_dont_include_interactions_of_last_and_next_month(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-05-04 06:20:00", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(5),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-04-30 23:59:59", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(5),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => "2020-06-01 00:00:01", // Lunes
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseInteractionsSummary($course->getCourseCanvasId(),1000,2020,5);
        $result = json_decode($report->getContent());
        $days = ["Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado", "Domingo"];
        foreach($days as $day){
            if($day == "Lunes"){
                $this->assertEquals(1, $result->$day->{'Mañana'});
            }else{
                $this->assertEquals(0, $result->$day->{'Mañana'});
            }
            $this->assertEquals(0, $result->$day->Tarde);
            $this->assertEquals(0, $result->$day->Noche);
            $this->assertEquals(0, $result->$day->Madrugada);
        }
    }

    private function processReports(int $course_canvas_id) {
        (new devController())->rebuildReports($course_canvas_id);
    }
}
