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

class ResourceVisualizationsTest extends TestCase
{
    use algebraCourse;

    public function test_resources_without_interactions(){
        // Prepare
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $now = Carbon::now()->format('Y-m-d H:m:s');
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceVisualizations($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        // Assertions
        foreach ($result as $module){
            foreach($module->resources_visualizations as $resource){
                $this->assertEquals(0, $resource->visualizations_count);
                $this->assertCount(4, $resource->members_visualizations);
                foreach($resource->members_visualizations as $member){
                    $this->assertEquals(0, $member->views_count);
                }
            }
        }
    }

    public function test_count_wiki_pages_interactions(){
        // Prepare
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => Carbon::now()->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(3),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => Carbon::now()->subDays(1)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => Carbon::now()->subDays(2)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => Carbon::now()->subHours(1)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceVisualizations($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        $module = $result[2];
        $resource = $module->resources_visualizations[0];
        $this->assertCount(4, $module->resources_visualizations);
        $this->assertEquals(4, $resource->visualizations_count);
        $this->assertEquals(300, $resource->resource_canvas_id);
        $this->assertEquals(2, $resource->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(2, $resource->members_visualizations[0]->views_count);
        $this->assertEquals(1, $resource->members_visualizations[1]->views_count);
        $this->assertEquals(1, $resource->members_visualizations[2]->views_count);
        $this->assertEquals(0, $resource->members_visualizations[3]->views_count);
    }

    public function test_count_quizzes_interactions(){
        // Prepare
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        QuizInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(2000),
            'item_canvas_id' => 2000,
            'viewed' => Carbon::now()->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        QuizInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(3),
            'item_id' => CourseBuilder::idFromCanvasId(2000),
            'item_canvas_id' => 2000,
            'viewed' => Carbon::now()->subHours(1)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        QuizInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(2000),
            'item_canvas_id' => 2000,
            'viewed' => Carbon::now()->subHours(2)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        QuizInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(2000),
            'item_canvas_id' => 2000,
            'viewed' => Carbon::now()->subHours(3)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceVisualizations($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        $module = $result[1];
        $resource = $module->resources_visualizations[2];
        $this->assertCount(4, $module->resources_visualizations);
        $this->assertEquals(4, $resource->visualizations_count);
        $this->assertEquals(2000, $resource->resource_canvas_id);
        $this->assertEquals(2, $resource->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(2, $resource->members_visualizations[0]->views_count);
        $this->assertEquals(1, $resource->members_visualizations[1]->views_count);
        $this->assertEquals(1, $resource->members_visualizations[2]->views_count);
        $this->assertEquals(0, $resource->members_visualizations[3]->views_count);
    }

    public function test_count_attachments_interactions(){
        // Prepare
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        AttachmentInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(1200),
            'item_canvas_id' => 1200,
            'viewed' => Carbon::now()->subHours(1)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        AttachmentInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(3),
            'item_id' => CourseBuilder::idFromCanvasId(1200),
            'item_canvas_id' => 1200,
            'viewed' => Carbon::now()->subHours(2)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        AttachmentInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(1200),
            'item_canvas_id' => 1200,
            'viewed' => Carbon::now()->subHours(3)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        AttachmentInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(1200),
            'item_canvas_id' => 1200,
            'viewed' => Carbon::now()->subHours(4)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceVisualizations($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        $module = $result[1];
        $resource = $module->resources_visualizations[3];
        $this->assertCount(4, $module->resources_visualizations);
        $this->assertEquals(4, $resource->visualizations_count);
        $this->assertEquals(1200, $resource->resource_canvas_id);
        $this->assertEquals(2, $resource->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(2, $resource->members_visualizations[0]->views_count);
        $this->assertEquals(1, $resource->members_visualizations[1]->views_count);
        $this->assertEquals(1, $resource->members_visualizations[2]->views_count);
        $this->assertEquals(0, $resource->members_visualizations[3]->views_count);
    }

    public function test_count_discussion_topics_interactions(){
        // Prepare
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        DiscussionTopicInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(10000),
            'item_canvas_id' => 10000,
            'viewed' => Carbon::now()->subDays(1)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        DiscussionTopicInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(3),
            'item_id' => CourseBuilder::idFromCanvasId(10000),
            'item_canvas_id' => 10000,
            'viewed' => Carbon::now()->subDays(2)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        DiscussionTopicInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(10000),
            'item_canvas_id' => 10000,
            'viewed' => Carbon::now()->subDays(3)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        DiscussionTopicInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(10000),
            'item_canvas_id' => 10000,
            'viewed' => Carbon::now()->subDays(4)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceVisualizations($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        $module = $result[0];
        $resource = $module->resources_visualizations[2];
        $this->assertCount(5, $module->resources_visualizations);
        $this->assertEquals(4, $resource->visualizations_count);
        $this->assertEquals(10000, $resource->resource_canvas_id);
        $this->assertEquals(2, $resource->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(2, $resource->members_visualizations[0]->views_count);
        $this->assertEquals(1, $resource->members_visualizations[1]->views_count);
        $this->assertEquals(1, $resource->members_visualizations[2]->views_count);
        $this->assertEquals(0, $resource->members_visualizations[3]->views_count);
    }

    public function test_count_assignments_interactions(){
        // Prepare
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        AssignmentInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(110),
            'item_canvas_id' => 110,
            'viewed' => Carbon::now()->subHours(1)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        AssignmentInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(110),
            'item_canvas_id' => 110,
            'viewed' => Carbon::now()->subHours(2)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        AssignmentInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(3),
            'item_id' => CourseBuilder::idFromCanvasId(110),
            'item_canvas_id' => 110,
            'viewed' => Carbon::now()->subHours(3)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        AssignmentInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(110),
            'item_canvas_id' => 110,
            'viewed' => Carbon::now()->subHours(4)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        AssignmentInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(110),
            'item_canvas_id' => 110,
            'viewed' => Carbon::now()->subHours(5)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        AssignmentInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(5),
            'item_id' => CourseBuilder::idFromCanvasId(110),
            'item_canvas_id' => 110,
            'viewed' => Carbon::now()->subHours(6)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceVisualizations($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        $module = $result[0];
        $resource = $module->resources_visualizations[4];
        $this->assertCount(5, $module->resources_visualizations);
        $this->assertEquals(6, $resource->visualizations_count);
        $this->assertEquals(110, $resource->resource_canvas_id);
        $this->assertEquals(2, $resource->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(3, $resource->members_visualizations[0]->views_count);
        $this->assertEquals(1, $resource->members_visualizations[1]->views_count);
        $this->assertEquals(1, $resource->members_visualizations[2]->views_count);
        $this->assertEquals(1, $resource->members_visualizations[3]->views_count);
    }

    public function test_count_url_interactions(){
        // Prepare
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        ExternalUrlInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(8000),
            'item_canvas_id' => 8000,
            'viewed' => Carbon::now()->subHours(1)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        ExternalUrlInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(3),
            'item_id' => CourseBuilder::idFromCanvasId(8000),
            'item_canvas_id' => 8000,
            'viewed' => Carbon::now()->subHours(2)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        ExternalUrlInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(8000),
            'item_canvas_id' => 8000,
            'viewed' => Carbon::now()->subHours(3)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        ExternalUrlInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(8000),
            'item_canvas_id' => 8000,
            'viewed' => Carbon::now()->subHours(4)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceVisualizations($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        $module = $result[0];
        $resource = $module->resources_visualizations[0];
        $this->assertCount(5, $module->resources_visualizations);
        $this->assertEquals(4, $resource->visualizations_count);
        $this->assertEquals(8000, $resource->resource_canvas_id);
        $this->assertEquals(2, $resource->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(2, $resource->members_visualizations[0]->views_count);
        $this->assertEquals(1, $resource->members_visualizations[1]->views_count);
        $this->assertEquals(1, $resource->members_visualizations[2]->views_count);
        $this->assertEquals(0, $resource->members_visualizations[3]->views_count);
    }

    public function test_count_external_tools_interactions(){
        // Prepare
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        ContextExternalToolInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(9000),
            'item_canvas_id' => 9000,
            'viewed' => Carbon::now()->subHours(1)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        ContextExternalToolInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(3),
            'item_id' => CourseBuilder::idFromCanvasId(9000),
            'item_canvas_id' => 9000,
            'viewed' => Carbon::now()->subHours(2)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        ContextExternalToolInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'item_id' => CourseBuilder::idFromCanvasId(9000),
            'item_canvas_id' => 9000,
            'viewed' => Carbon::now()->subHours(3)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        ContextExternalToolInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(9000),
            'item_canvas_id' => 9000,
            'viewed' => Carbon::now()->subHours(4)->format('Y-m-d H:m:s'),
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceVisualizations($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        $module = $result[1];
        $resource = $module->resources_visualizations[0];
        $this->assertCount(4, $module->resources_visualizations);
        $this->assertEquals(4, $resource->visualizations_count);
        $this->assertEquals(9000, $resource->resource_canvas_id);
        $this->assertEquals(2, $resource->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(2, $resource->members_visualizations[0]->views_count);
        $this->assertEquals(1, $resource->members_visualizations[1]->views_count);
        $this->assertEquals(1, $resource->members_visualizations[2]->views_count);
        $this->assertEquals(0, $resource->members_visualizations[3]->views_count);
    }

    private function processReports(int $course_canvas_id) {
        (new devController())->rebuildReports($course_canvas_id);
    }
}
