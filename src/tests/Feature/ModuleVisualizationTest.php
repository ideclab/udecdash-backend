<?php

namespace Tests\Feature;

use App\Classes\Course;
use App\Http\Controllers\devController;
use App\Http\Controllers\ReportsController;
use App\Models\AttachmentInteraction;
use App\Models\ContextExternalToolInteraction;
use App\Models\QuizInteraction;
use App\Models\WikiPageInteraction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\CourseBuilder;
use Tests\ExampleCourses\algebraCourse;
use Tests\TestCase;

class ModuleVisualizationTest extends TestCase
{
    use algebraCourse;

    public function test_module_without_interactions(){
        // Prepare
        $course = $this->buildAlgebraCourse();
        $first_section = 1000;
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->moduleVisualizations($course->getCourseCanvasId(), $first_section);
        $report = json_decode($report->getContent());
        $first_module = $report[0];
        $second_module = $report[1];
        $fake_module = $report[2];
        // Assertions
        $this->assertIsArray($first_module->members);
        $this->assertIsArray($second_module->members);
        $this->assertIsArray($fake_module->members);
        $this->assertCount(4, $first_module->members);
        $this->assertCount(4, $second_module->members);
        $this->assertCount(4, $fake_module->members);
        $this->assertEquals(0, $first_module->visualizations_percentage);
        $this->assertEquals(0, $second_module->visualizations_percentage);
        $this->assertEquals(0, $fake_module->visualizations_percentage);
        foreach($report as $module){
            foreach($module->members as $member){
                $this->assertEquals(false, $member->all_resources_visualized);
                foreach($member->module_resources as $resource){
                    $this->assertEquals(false, $resource->viewed);
                    $this->assertEquals(null, $resource->first_view);
                }
            }
        }
    }

    public function test_all_resources_of_module_viewed_by_only_a_student(){
        // Prepare
        $course = $this->buildAlgebraCourse();
        $first_section = 1000;
        $student_canvas_id = 2; // Juan Valdez
        $now = Carbon::now()->format('Y-m-d H:m:s');
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId($student_canvas_id),
            'item_id' => CourseBuilder::idFromCanvasId(200),
            'item_canvas_id' => 200,
            'viewed' => $now,
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        AttachmentInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId($student_canvas_id),
            'item_id' => CourseBuilder::idFromCanvasId(1200),
            'item_canvas_id' => 1200,
            'viewed' => $now,
            'url' => 'whatever',
            'device' => 'DESKTOP',
            'downloaded' => 0
        ]);
        QuizInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId($student_canvas_id),
            'item_id' => CourseBuilder::idFromCanvasId(2000),
            'item_canvas_id' => 2000,
            'url' => 'whatever',
            'viewed' => $now,
            'device' => 'DESKTOP'
        ]);
        ContextExternalToolInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId($student_canvas_id),
            'item_id' => CourseBuilder::idFromCanvasId(9000),
            'item_canvas_id' => 9000,
            'viewed' => $now,
            'url' => 'whatever',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $controller = new ReportsController();
        $report = $controller->moduleVisualizations($course->getCourseCanvasId(), $first_section);
        $report = json_decode($report->getContent());
        // Assertions
        $first_module = $report[0];
        $second_module = $report[1];
        $fake_module = $report[2];
        $this->assertEquals(0, $first_module->visualizations_percentage);
        $this->assertEquals(25, $second_module->visualizations_percentage);
        $this->assertEquals(0, $fake_module->visualizations_percentage);
        foreach($report as $module){
            foreach($module->members as $member){
                $is_module_with_activty = $module->canvas_id == $second_module->canvas_id;
                $is_student_with_activity = $member->canvas_id == $student_canvas_id;
                if($is_module_with_activty && $is_student_with_activity){
                    $this->assertEquals(true, $member->all_resources_visualized);
                }else{
                    $this->assertEquals(false, $member->all_resources_visualized);
                }
                foreach($member->module_resources as $resource){
                    if($is_module_with_activty && $is_student_with_activity){
                        $this->assertEquals(true, $resource->viewed);
                        $this->assertEquals($now, $resource->first_view);
                    }else{
                        $this->assertEquals(false, $resource->viewed);
                        $this->assertEquals(null, $resource->first_view);
                    }
                }
            }
        }
    }

    public function test_some_resources_viewed_by_only_a_student(){
        // Prepare
        $course = $this->buildAlgebraCourse();
        $first_section = 1000;
        $student_canvas_id = 2; // Juan Valdez
        $now = Carbon::now()->format('Y-m-d H:m:s');
        QuizInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId($student_canvas_id),
            'item_id' => CourseBuilder::idFromCanvasId(2000),
            'item_canvas_id' => 2000,
            'url' => 'whatever',
            'viewed' => $now,
            'device' => 'DESKTOP'
        ]);
        ContextExternalToolInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId($student_canvas_id),
            'item_id' => CourseBuilder::idFromCanvasId(9000),
            'item_canvas_id' => 9000,
            'viewed' => $now,
            'url' => 'whatever',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $controller = new ReportsController();
        $report = $controller->moduleVisualizations($course->getCourseCanvasId(), $first_section);
        $report = json_decode($report->getContent());
        // Assertions
        $first_module = $report[0];
        $second_module = $report[1];
        $fake_module = $report[2];
        $this->assertEquals(0, $first_module->visualizations_percentage);
        $this->assertEquals(0, $second_module->visualizations_percentage);
        $this->assertEquals(0, $fake_module->visualizations_percentage);
        $user_activity = $second_module->members[0];
        $resources = $second_module->members[0]->module_resources;
        $this->assertEquals(false, $user_activity->all_resources_visualized);
        $this->assertEquals(9000, $resources[0]->resource_canvas_id);
        $this->assertEquals(true, $resources[0]->viewed);
        $this->assertEquals($now, $resources[0]->first_view);
        $this->assertEquals(200, $resources[1]->resource_canvas_id);
        $this->assertEquals(false, $resources[1]->viewed);
        $this->assertEquals(null, $resources[1]->first_view);
        $this->assertEquals(2000, $resources[2]->resource_canvas_id);
        $this->assertEquals(true, $resources[2]->viewed);
        $this->assertEquals($now, $resources[2]->first_view);
        $this->assertEquals(1200, $resources[3]->resource_canvas_id);
        $this->assertEquals(false, $resources[3]->viewed);
        $this->assertEquals(null, $resources[3]->first_view);
    }

    public function test_module_viewed_by_the_majority_of_students () {
        // Prepare
        $course = $this->buildAlgebraCourse();
        $first_section = 1000;
        $students_with_activity = [2, 3, 5]; // Todos excepto: Oscar Alvarez
        $now = Carbon::now()->format('Y-m-d H:m:s');
        foreach($students_with_activity as $student_id){
            WikiPageInteraction::create([
                'course_id' => $course->getCourseId(),
                'user_id' => CourseBuilder::idFromCanvasId($student_id),
                'item_id' => CourseBuilder::idFromCanvasId(200),
                'item_canvas_id' => 200,
                'viewed' => $now,
                'url' => 'whatever',
                'device' => 'MOBILE'
            ]);
        }
        foreach($students_with_activity as $student_id){
            AttachmentInteraction::create([
                'course_id' => $course->getCourseId(),
                'user_id' => CourseBuilder::idFromCanvasId($student_id),
                'item_id' => CourseBuilder::idFromCanvasId(1200),
                'item_canvas_id' => 1200,
                'viewed' => $now,
                'url' => 'whatever',
                'device' => 'DESKTOP',
                'downloaded' => 0
            ]);
        }
        foreach($students_with_activity as $student_id){
            QuizInteraction::create([
                'course_id' => $course->getCourseId(),
                'user_id' => CourseBuilder::idFromCanvasId($student_id),
                'item_id' => CourseBuilder::idFromCanvasId(2000),
                'item_canvas_id' => 2000,
                'url' => 'whatever',
                'viewed' => $now,
                'device' => 'DESKTOP'
            ]);
        }
        foreach($students_with_activity as $student_id){
            ContextExternalToolInteraction::create([
                'course_id' => $course->getCourseId(),
                'user_id' => CourseBuilder::idFromCanvasId($student_id),
                'item_id' => CourseBuilder::idFromCanvasId(9000),
                'item_canvas_id' => 9000,
                'viewed' => $now,
                'url' => 'whatever',
                'device' => 'DESKTOP'
            ]);
        }
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $controller = new ReportsController();
        $report = $controller->moduleVisualizations($course->getCourseCanvasId(), $first_section);
        $report = json_decode($report->getContent());
        // Assertions
        $first_module = $report[0];
        $second_module = $report[1];
        $fake_module = $report[2];
        $this->assertEquals(0, $first_module->visualizations_percentage);
        $this->assertEquals(75, $second_module->visualizations_percentage);
        $this->assertEquals(0, $fake_module->visualizations_percentage);
        foreach($second_module->members as $member) {
            if(in_array($member->canvas_id, $students_with_activity)){
                $this->assertEquals(true, $member->all_resources_visualized);
                foreach($member->module_resources as $resource){
                    $this->assertEquals(true, $resource->viewed);
                    $this->assertEquals($now, $resource->first_view);
                }
            }else{
                $this->assertEquals(false, $member->all_resources_visualized);
                foreach($member->module_resources as $resource){
                    $this->assertEquals(false, $resource->viewed);
                    $this->assertEquals(null, $resource->first_view);
                }
            }
        }
    }

    public function test_module_viewed_by_all_the_students () {
        // Prepare
        $course = $this->buildAlgebraCourse();
        $first_section = 1000;
        $students_with_activity = [2, 3, 4, 5]; // Todos excepto: Oscar Alvarez
        $now = Carbon::now()->format('Y-m-d H:m:s');
        foreach($students_with_activity as $student_id){
            WikiPageInteraction::create([
                'course_id' => $course->getCourseId(),
                'user_id' => CourseBuilder::idFromCanvasId($student_id),
                'item_id' => CourseBuilder::idFromCanvasId(200),
                'item_canvas_id' => 200,
                'viewed' => $now,
                'url' => 'whatever',
                'device' => 'MOBILE'
            ]);
        }
        foreach($students_with_activity as $student_id){
            AttachmentInteraction::create([
                'course_id' => $course->getCourseId(),
                'user_id' => CourseBuilder::idFromCanvasId($student_id),
                'item_id' => CourseBuilder::idFromCanvasId(1200),
                'item_canvas_id' => 1200,
                'viewed' => $now,
                'url' => 'whatever',
                'device' => 'DESKTOP',
                'downloaded' => 0
            ]);
        }
        foreach($students_with_activity as $student_id){
            QuizInteraction::create([
                'course_id' => $course->getCourseId(),
                'user_id' => CourseBuilder::idFromCanvasId($student_id),
                'item_id' => CourseBuilder::idFromCanvasId(2000),
                'item_canvas_id' => 2000,
                'url' => 'whatever',
                'viewed' => $now,
                'device' => 'DESKTOP'
            ]);
        }
        foreach($students_with_activity as $student_id){
            ContextExternalToolInteraction::create([
                'course_id' => $course->getCourseId(),
                'user_id' => CourseBuilder::idFromCanvasId($student_id),
                'item_id' => CourseBuilder::idFromCanvasId(9000),
                'item_canvas_id' => 9000,
                'viewed' => $now,
                'url' => 'whatever',
                'device' => 'DESKTOP'
            ]);
        }
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $controller = new ReportsController();
        $report = $controller->moduleVisualizations($course->getCourseCanvasId(), $first_section);
        $report = json_decode($report->getContent());
        // Assertions
        $first_module = $report[0];
        $second_module = $report[1];
        $fake_module = $report[2];
        $this->assertEquals(0, $first_module->visualizations_percentage);
        $this->assertEquals(100, $second_module->visualizations_percentage);
        $this->assertEquals(0, $fake_module->visualizations_percentage);
        foreach($second_module->members as $member) {
            $this->assertEquals(true, $member->all_resources_visualized);
            foreach($member->module_resources as $resource){
                $this->assertEquals(true, $resource->viewed);
                $this->assertEquals($now, $resource->first_view);
            }
        }
    }

    public function test_modules_has_canvas_id () {
        // Prepare
        $course = $this->buildAlgebraCourse();
        $first_section = 1000;
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $controller = new ReportsController();
        $report = $controller->moduleVisualizations($course->getCourseCanvasId(), $first_section);
        $report = json_decode($report->getContent());
        // Assertions
        $first_module = $report[0];
        $second_module = $report[1];
        $fake_module = $report[2];
        $this->assertEquals(1, $first_module->canvas_id);
        $this->assertEquals(2, $second_module->canvas_id);
        $this->assertEquals(Course::FAKE_MODULE_ID, $fake_module->canvas_id);
    }

    public function test_resources_has_canvas_id () {
        // Prepare
        $course = $this->buildAlgebraCourse();
        $first_section = 1000;
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $controller = new ReportsController();
        $report = $controller->moduleVisualizations($course->getCourseCanvasId(), $first_section);
        $report = json_decode($report->getContent());
        // Assertions
        $first_module = $report[0];
        $second_module = $report[1];
        $fake_module = $report[2];
        foreach($first_module->members as $member){
            $resources = $member->module_resources;
            $this->assertEquals(8000, $resources[0]->resource_canvas_id);
            $this->assertEquals(100, $resources[1]->resource_canvas_id);
            $this->assertEquals(10000, $resources[2]->resource_canvas_id);
            $this->assertEquals(1100, $resources[3]->resource_canvas_id);
            $this->assertEquals(110, $resources[4]->resource_canvas_id);
        }

        foreach($second_module->members as $member){
            $resources = $member->module_resources;
            $this->assertEquals(9000, $resources[0]->resource_canvas_id);
            $this->assertEquals(200, $resources[1]->resource_canvas_id);
            $this->assertEquals(2000, $resources[2]->resource_canvas_id);
            $this->assertEquals(1200, $resources[3]->resource_canvas_id);
        }
        foreach($fake_module->members as $member){
            $resources = $member->module_resources;
            $this->assertEquals(300, $resources[0]->resource_canvas_id);
            $this->assertEquals(1000, $resources[1]->resource_canvas_id);
            $this->assertEquals(11000, $resources[2]->resource_canvas_id);
            $this->assertEquals(120, $resources[3]->resource_canvas_id);
        }
    }

    private function processReports(int $course_canvas_id) {
        (new devController())->rebuildReports($course_canvas_id);
    }
}
