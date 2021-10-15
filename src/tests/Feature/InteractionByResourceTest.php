<?php

namespace Tests\Feature;

use App\Http\Controllers\devController;
use App\Http\Controllers\ReportsController;
use App\Models\DiscussionTopicInteraction;
use App\Models\WikiPageInteraction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\CourseBuilder;
use Tests\ExampleCourses\algebraCourse;
use Tests\TestCase;

class InteractionByResourceTest extends TestCase
{
    use algebraCourse;

    public function test_course_without_interactions(){
        // Prepare
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->interactionByResource($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        // Assertions
        $this->assertCount(3, $result);
        $this->assertCount(5, $result[0]->resources_interaction);
        $this->assertCount(4, $result[1]->resources_interaction);
        $this->assertCount(4, $result[2]->resources_interaction);
        $this->assertEquals(4, $result[0]->members_count);
        $this->assertEquals(4, $result[1]->members_count);
        $this->assertEquals(4, $result[2]->members_count);
        foreach($result as $module){
            $this->assertCount(4, $module->members);
            foreach($module->members as $member){
                foreach($member->module_resources as $interaction){
                    $this->assertEquals(false,$interaction->viewed);
                    $this->assertEquals(null, $interaction->first_view);
                }
            }
        }
    }

    public function test_resource_viewed_by_all_students(){
        // Prepare
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $now = Carbon::now()->format('Y-m-d H:m:s');
        DiscussionTopicInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(10000),
            'item_canvas_id' => 10000,
            'viewed' => $now,
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        DiscussionTopicInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(3),
            'item_id' => CourseBuilder::idFromCanvasId(10000),
            'item_canvas_id' => 10000,
            'viewed' => $now,
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        DiscussionTopicInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(10000),
            'item_canvas_id' => 10000,
            'viewed' => $now,
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        DiscussionTopicInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(5),
            'item_id' => CourseBuilder::idFromCanvasId(10000),
            'item_canvas_id' => 10000,
            'viewed' => $now,
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->interactionByResource($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        $first_module = $result[0];
        $resource_id = 10000;
        // Assertions
        $this->assertEquals($resource_id, $first_module->members[0]->module_resources[2]->resource_canvas_id);
        $this->assertEquals($resource_id, $first_module->members[1]->module_resources[2]->resource_canvas_id);
        $this->assertEquals($resource_id, $first_module->members[2]->module_resources[2]->resource_canvas_id);
        $this->assertEquals($resource_id, $first_module->members[3]->module_resources[2]->resource_canvas_id);
        $this->assertEquals(true, $first_module->members[0]->module_resources[2]->viewed);
        $this->assertEquals(true, $first_module->members[1]->module_resources[2]->viewed);
        $this->assertEquals(true, $first_module->members[2]->module_resources[2]->viewed);
        $this->assertEquals(true, $first_module->members[3]->module_resources[2]->viewed);
        $this->assertEquals($now, $first_module->members[0]->module_resources[2]->first_view);
        $this->assertEquals($now, $first_module->members[1]->module_resources[2]->first_view);
        $this->assertEquals($now, $first_module->members[2]->module_resources[2]->first_view);
        $this->assertEquals($now, $first_module->members[3]->module_resources[2]->first_view);
        foreach($first_module->resources_interaction as $summary){
            if($summary->resource_canvas_id == $resource_id){
                $this->assertEquals(100, $summary->visualization_percentage);
                $this->assertEquals(4, $summary->viewed_resources_count);
            }else{
                $this->assertEquals(0, $summary->visualization_percentage);
                $this->assertEquals(0, $summary->viewed_resources_count);
            }
        }
    }

    public function test_resource_viewed_by_some_students(){
        // Prepare
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $now = Carbon::now()->format('Y-m-d H:m:s');
        DiscussionTopicInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(10000),
            'item_canvas_id' => 10000,
            'viewed' => $now,
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        DiscussionTopicInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(3),
            'item_id' => CourseBuilder::idFromCanvasId(10000),
            'item_canvas_id' => 10000,
            'viewed' => $now,
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        DiscussionTopicInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(10000),
            'item_canvas_id' => 10000,
            'viewed' => $now,
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->interactionByResource($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        $first_module = $result[0];
        $resource_id = 10000;
        // Assertions
        $this->assertEquals($resource_id, $first_module->members[0]->module_resources[2]->resource_canvas_id);
        $this->assertEquals($resource_id, $first_module->members[1]->module_resources[2]->resource_canvas_id);
        $this->assertEquals($resource_id, $first_module->members[2]->module_resources[2]->resource_canvas_id);
        $this->assertEquals($resource_id, $first_module->members[3]->module_resources[2]->resource_canvas_id);
        $this->assertEquals(true, $first_module->members[0]->module_resources[2]->viewed);
        $this->assertEquals(true, $first_module->members[1]->module_resources[2]->viewed);
        $this->assertEquals(true, $first_module->members[2]->module_resources[2]->viewed);
        $this->assertEquals(false, $first_module->members[3]->module_resources[2]->viewed);
        $this->assertEquals($now, $first_module->members[0]->module_resources[2]->first_view);
        $this->assertEquals($now, $first_module->members[1]->module_resources[2]->first_view);
        $this->assertEquals($now, $first_module->members[2]->module_resources[2]->first_view);
        $this->assertEquals(null, $first_module->members[3]->module_resources[2]->first_view);
        foreach($first_module->resources_interaction as $summary){
            if($summary->resource_canvas_id == $resource_id){
                $this->assertEquals(75, $summary->visualization_percentage);
                $this->assertEquals(3, $summary->viewed_resources_count);
            }else{
                $this->assertEquals(0, $summary->visualization_percentage);
                $this->assertEquals(0, $summary->viewed_resources_count);
            }
        }
    }

    public function test_multiples_interactions_by_one_student(){
        // Prepare
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $now = Carbon::now()->format('Y-m-d H:m:s');
        DiscussionTopicInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(10000),
            'item_canvas_id' => 10000,
            'viewed' => $now,
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        DiscussionTopicInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(10000),
            'item_canvas_id' => 10000,
            'viewed' => Carbon::now()->addDays(2)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        DiscussionTopicInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(10000),
            'item_canvas_id' => 10000,
            'viewed' => Carbon::now()->addDays(3)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->interactionByResource($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        $first_module = $result[0];
        $resource_id = 10000;
        // Assertions
        $this->assertEquals($resource_id, $first_module->members[0]->module_resources[2]->resource_canvas_id);
        $this->assertEquals($resource_id, $first_module->members[1]->module_resources[2]->resource_canvas_id);
        $this->assertEquals($resource_id, $first_module->members[2]->module_resources[2]->resource_canvas_id);
        $this->assertEquals($resource_id, $first_module->members[3]->module_resources[2]->resource_canvas_id);
        $this->assertEquals(true, $first_module->members[0]->module_resources[2]->viewed);
        $this->assertEquals(false, $first_module->members[1]->module_resources[2]->viewed);
        $this->assertEquals(false, $first_module->members[2]->module_resources[2]->viewed);
        $this->assertEquals(false, $first_module->members[3]->module_resources[2]->viewed);
        $this->assertEquals($now, $first_module->members[0]->module_resources[2]->first_view);
        $this->assertEquals(null, $first_module->members[1]->module_resources[2]->first_view);
        $this->assertEquals(null, $first_module->members[2]->module_resources[2]->first_view);
        $this->assertEquals(null, $first_module->members[3]->module_resources[2]->first_view);
        foreach($first_module->resources_interaction as $summary){
            if($summary->resource_canvas_id == $resource_id){
                $this->assertEquals(25, $summary->visualization_percentage);
                $this->assertEquals(1, $summary->viewed_resources_count);
            }else{
                $this->assertEquals(0, $summary->visualization_percentage);
                $this->assertEquals(0, $summary->viewed_resources_count);
            }
        }
    }

    public function test_resource_viewed_by_some_students_on_fake_module(){
        // Prepare
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $now = Carbon::now()->format('Y-m-d H:m:s');
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => $now,
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(3),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => $now,
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => $now,
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->interactionByResource($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        $fake_module = $result[2];
        $resource_id = 300;
        // Assertions
        $this->assertEquals($resource_id, $fake_module->members[0]->module_resources[0]->resource_canvas_id);
        $this->assertEquals($resource_id, $fake_module->members[1]->module_resources[0]->resource_canvas_id);
        $this->assertEquals($resource_id, $fake_module->members[2]->module_resources[0]->resource_canvas_id);
        $this->assertEquals($resource_id, $fake_module->members[3]->module_resources[0]->resource_canvas_id);
        $this->assertEquals(true, $fake_module->members[0]->module_resources[0]->viewed);
        $this->assertEquals(true, $fake_module->members[1]->module_resources[0]->viewed);
        $this->assertEquals(true, $fake_module->members[2]->module_resources[0]->viewed);
        $this->assertEquals(false, $fake_module->members[3]->module_resources[0]->viewed);
        $this->assertEquals($now, $fake_module->members[0]->module_resources[0]->first_view);
        $this->assertEquals($now, $fake_module->members[1]->module_resources[0]->first_view);
        $this->assertEquals($now, $fake_module->members[2]->module_resources[0]->first_view);
        $this->assertEquals(null, $fake_module->members[3]->module_resources[0]->first_view);
        foreach($fake_module->resources_interaction as $summary){
            if($summary->resource_canvas_id == $resource_id){
                $this->assertEquals(75, $summary->visualization_percentage);
                $this->assertEquals(3, $summary->viewed_resources_count);
            }else{
                $this->assertEquals(0, $summary->visualization_percentage);
                $this->assertEquals(0, $summary->viewed_resources_count);
            }
        }
    }

    private function processReports(int $course_canvas_id) {
        (new devController())->rebuildReports($course_canvas_id);
    }
}
