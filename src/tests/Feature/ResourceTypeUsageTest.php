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

class ResourceTypeUsageTest extends TestCase
{
    use algebraCourse;

    public function test_course_without_resources(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $existing_types = ['WikiPage', 'Quiz','Attachment','DiscussionTopic','Assignment',
        'ExternalUrl','ContextExternalTool'];
        foreach($report as $resource_type => $type){
            $this->assertContains($resource_type, $existing_types);
            $this->assertCount(0, $type->resources);
            $this->assertEquals(0, $type->resources_count);
            $this->assertEquals(0, $type->resources_percentage);
            $this->assertEquals(0, $type->resource_type_use_percentage);
            $this->assertCount(0, $type->resources_interactions);
            $this->assertEquals(0, $type->resource_type_use_percentage);
        }
    }

    public function test_wiki_page_course_distribution_percentage(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(500);
        $course->addModules([['canvas_id' => 500, 'name' => 'First Module']]);
        $course->addPages([
            ['canvas_id' => 100, 'title' => 'Page in first module',
            'module_item' => ['canvas_id'=> 1, 'module_id' => $module_id, 'position' => 1]],
            ['canvas_id' => 300, 'title' => 'Page without module', 'workflow_state' =>'unpublished'],
        ]);
        $course->addAssignments([
            ['canvas_id' => 120, 'title' => 'Assignment without module',
            'submission_types' => 'online_upload', 'position' => 200],
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(2, $report->WikiPage->resources_count);
        $this->assertEquals(66.67, $report->WikiPage->resources_percentage);
        $this->assertEquals(0, $report->WikiPage->resource_type_use_percentage);
    }

    public function test_quiz_course_distribution_percentage(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(500);
        $course->addModules([['canvas_id' => 500, 'name' => 'First Module']]);
        $course->addQuizzes([
            ['canvas_id' => 1000, 'name' => 'Some quiz 1', 'quiz_type' => 'assignment'],
            ['canvas_id' => 2000, 'name' => 'Some quiz 2', 'quiz_type' => 'assignment',
            'assignment' => ['canvas_id' => 1],
            'module_item' => ['canvas_id'=> 3, 'module_id' => $module_id, 'position' => 2]]
        ]);
        $course->addAssignments([
            ['canvas_id' => 120, 'title' => 'Assignment without module',
            'submission_types' => 'online_upload', 'position' => 200],
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(2, $report->Quiz->resources_count);
        $this->assertEquals(66.67, $report->Quiz->resources_percentage);
        $this->assertEquals(0, $report->Quiz->resource_type_use_percentage);
    }

    public function test_attachment_course_distribution_percentage(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(500);
        $course->addModules([['canvas_id' => 500, 'name' => 'First Module']]);
        $course->addFiles([
            ['canvas_id' => 1100, 'display_name' => 'some_file_1.pdf',
            'content_type' => 'application/pdf',
            'module_item' => ['canvas_id' => 4, 'module_id' => $module_id,'position' => 20]],
            ['canvas_id' => 1200, 'display_name' => 'some_file_2.pdf',
            'content_type' => 'application/pdf',
            'module_item' => ['canvas_id' => 5, 'module_id' => $module_id, 'position' => 20]],
        ]);
        $course->addAssignments([
            ['canvas_id' => 120, 'title' => 'Assignment without module',
            'submission_types' => 'online_upload', 'position' => 200],
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(2, $report->Attachment->resources_count);
        $this->assertEquals(66.67, $report->Attachment->resources_percentage);
        $this->assertEquals(0, $report->Attachment->resource_type_use_percentage);
    }

    public function test_discussion_topic_course_distribution_percentage(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(500);
        $course->addModules([['canvas_id' => 500, 'name' => 'First Module']]);
        $course->addDiscussionTopics([
            ['canvas_id' => 10000, 'title' => 'some discussion 1',
            'module_item' => ['canvas_id'=> 7, 'module_id' => $module_id, 'position' => 5]],
            ['canvas_id' => 11000, 'title' => 'some discussion 2']
        ]);
        $course->addAssignments([
            ['canvas_id' => 120, 'title' => 'Assignment without module',
            'submission_types' => 'online_upload', 'position' => 200],
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(2, $report->DiscussionTopic->resources_count);
        $this->assertEquals(66.67, $report->DiscussionTopic->resources_percentage);
        $this->assertEquals(0, $report->DiscussionTopic->resource_type_use_percentage);
    }

    public function test_assignment_course_distribution_percentage(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(500);
        $course->addModules([['canvas_id' => 500, 'name' => 'First Module']]);
        $course->addAssignments([
            ['canvas_id' => 110, 'title' => 'Assignment in first module',
            'submission_types' => 'online_upload', 'position' => 200,
            'module_item' => ['canvas_id' => 6, 'module_id' => $module_id, 'position' => 200]],
            ['canvas_id' => 120, 'title' => 'Assignment without module',
            'submission_types' => 'online_upload', 'position' => 200],
        ]);
        $course->addPages([
            ['canvas_id' => 100, 'title' => 'Page in first module',
            'module_item' => ['canvas_id'=> 1, 'module_id' => $module_id, 'position' => 1]],
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(2, $report->Assignment->resources_count);
        $this->assertEquals(66.67, $report->Assignment->resources_percentage);
        $this->assertEquals(0, $report->Assignment->resource_type_use_percentage);
    }

    public function test_external_url_course_distribution_percentage(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(500);
        $course->addModules([['canvas_id' => 500, 'name' => 'First Module']]);
        $course->addUrls([
            ['canvas_id' => 8000, 'title' => 'Some url', 'module_id' => $module_id,
            'position' => 60, 'url' => 'http://www.google.cl'],
        ]);
        $course->addPages([
            ['canvas_id' => 100, 'title' => 'some_page_1',
            'module_item' => ['canvas_id'=> 1, 'module_id' => $module_id, 'position' => 1]],
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(1, $report->ExternalUrl->resources_count);
        $this->assertEquals(50, $report->ExternalUrl->resources_percentage);
        $this->assertEquals(0, $report->ExternalUrl->resource_type_use_percentage);
    }

    public function test_context_external_tool_course_distribution_percentage(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(500);
        $course->addModules([['canvas_id' => 500, 'name' => 'First Module']]);
        $course->addExternalTools([
            ['canvas_id' => 9000, 'title' => 'some tool 1', 'module_id' => $module_id,
            'position' => 61, 'url' => 'http://www.launchLTI.cl']
        ]);
        $course->addPages([
            ['canvas_id' => 100, 'title' => 'some_page_1',
            'module_item' => ['canvas_id'=> 1, 'module_id' => $module_id, 'position' => 1]],
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(1, $report->ContextExternalTool->resources_count);
        $this->assertEquals(50, $report->ContextExternalTool->resources_percentage);
        $this->assertEquals(0, $report->ContextExternalTool->resource_type_use_percentage);
    }

    public function test_wiki_page_interactions(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
            ['canvas_id' => 3, "name" => 'Oscar Alvarez'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(3), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
        ]);
        $course->addPages([
            ['canvas_id' => 100, 'title' => 'Some page'],
            ['canvas_id' => 300, 'title' => 'Some page 2']
        ]);
        $now = Carbon::now()->format('Y-m-d H:m:s');
        $student_canvas_id = 2; // Juan Valdez
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId($student_canvas_id),
            'item_id' => CourseBuilder::idFromCanvasId(100),
            'item_canvas_id' => 100,
            'viewed' => $now,
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(2, $report->WikiPage->resources_count);
        $this->assertCount(2, $report->WikiPage->resources_interactions);
        $this->assertEquals(100, $report->WikiPage->resources_percentage);
        $this->assertEquals(25, $report->WikiPage->resource_type_use_percentage);
        $first_page = $report->WikiPage->resources_interactions[0];
        $second_page = $report->WikiPage->resources_interactions[1];
        $this->assertCount(2, $first_page->members_visualizations);
        $this->assertEquals(50, $first_page->members_visualizations_percentage);
        $this->assertCount(2, $second_page->members_visualizations);
        $this->assertEquals(0, $second_page->members_visualizations_percentage);
        $this->assertEquals(true, $first_page->members_visualizations[0]->viewed);
        $this->assertEquals($now, $first_page->members_visualizations[0]->first_view);
        $this->assertEquals($student_canvas_id, $first_page->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(100, $first_page->members_visualizations[0]->resource_canvas_id);
        $this->assertEquals(false, $first_page->members_visualizations[1]->viewed);
        $this->assertEquals(null, $first_page->members_visualizations[1]->first_view);
        $this->assertEquals(3, $first_page->members_visualizations[1]->member_canvas_id);
        $this->assertEquals(100, $first_page->members_visualizations[1]->resource_canvas_id);
    }

    public function test_quiz_interactions(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
            ['canvas_id' => 3, "name" => 'Oscar Alvarez'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(3), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(500);
        $course->addModules([['canvas_id' => 500, 'name' => 'First Module']]);
        $course->addQuizzes([
            ['canvas_id' => 100, 'name' => 'Some quiz 1', 'quiz_type' => 'assignment'],
            ['canvas_id' => 300, 'name' => 'Some quiz 2', 'quiz_type' => 'assignment',
            'assignment' => ['canvas_id' => 1],
            'module_item' => ['canvas_id'=> 3, 'module_id' => $module_id, 'position' => 2]]
        ]);
        $now = Carbon::now()->format('Y-m-d H:m:s');
        $student_canvas_id = 2; // Juan Valdez
        QuizInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId($student_canvas_id),
            'item_id' => CourseBuilder::idFromCanvasId(100),
            'item_canvas_id' => 100,
            'viewed' => $now,
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(2, $report->Quiz->resources_count);
        $this->assertCount(2, $report->Quiz->resources_interactions);
        $this->assertEquals(100, $report->Quiz->resources_percentage);
        $this->assertEquals(25, $report->Quiz->resource_type_use_percentage);
        $first_page = $report->Quiz->resources_interactions[0];
        $second_page = $report->Quiz->resources_interactions[1];
        $this->assertCount(2, $first_page->members_visualizations);
        $this->assertEquals(50, $first_page->members_visualizations_percentage);
        $this->assertCount(2, $second_page->members_visualizations);
        $this->assertEquals(0, $second_page->members_visualizations_percentage);
        $this->assertEquals(true, $first_page->members_visualizations[0]->viewed);
        $this->assertEquals($now, $first_page->members_visualizations[0]->first_view);
        $this->assertEquals($student_canvas_id, $first_page->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(100, $first_page->members_visualizations[0]->resource_canvas_id);
        $this->assertEquals(false, $first_page->members_visualizations[1]->viewed);
        $this->assertEquals(null, $first_page->members_visualizations[1]->first_view);
        $this->assertEquals(3, $first_page->members_visualizations[1]->member_canvas_id);
        $this->assertEquals(100, $first_page->members_visualizations[1]->resource_canvas_id);
    }

    public function test_attachment_interactions(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
            ['canvas_id' => 3, "name" => 'Oscar Alvarez'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(3), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(500);
        $course->addModules([['canvas_id' => 500, 'name' => 'First Module']]);
        $course->addFiles([
            ['canvas_id' => 100, 'display_name' => 'some_file_1.pdf',
            'content_type' => 'application/pdf',
            'module_item' => ['canvas_id' => 4, 'module_id' => $module_id,'position' => 20]],
            ['canvas_id' => 300, 'display_name' => 'some_file_2.pdf',
            'content_type' => 'application/pdf',
            'module_item' => ['canvas_id' => 5, 'module_id' => $module_id, 'position' => 20]],
        ]);
        $now = Carbon::now()->format('Y-m-d H:m:s');
        $student_canvas_id = 2; // Juan Valdez
        AttachmentInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId($student_canvas_id),
            'item_id' => CourseBuilder::idFromCanvasId(100),
            'item_canvas_id' => 100,
            'viewed' => $now,
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(2, $report->Attachment->resources_count);
        $this->assertCount(2, $report->Attachment->resources_interactions);
        $this->assertEquals(100, $report->Attachment->resources_percentage);
        $this->assertEquals(25, $report->Attachment->resource_type_use_percentage);
        $first_page = $report->Attachment->resources_interactions[0];
        $second_page = $report->Attachment->resources_interactions[1];
        $this->assertCount(2, $first_page->members_visualizations);
        $this->assertEquals(50, $first_page->members_visualizations_percentage);
        $this->assertCount(2, $second_page->members_visualizations);
        $this->assertEquals(0, $second_page->members_visualizations_percentage);
        $this->assertEquals(true, $first_page->members_visualizations[0]->viewed);
        $this->assertEquals($now, $first_page->members_visualizations[0]->first_view);
        $this->assertEquals($student_canvas_id, $first_page->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(100, $first_page->members_visualizations[0]->resource_canvas_id);
        $this->assertEquals(false, $first_page->members_visualizations[1]->viewed);
        $this->assertEquals(null, $first_page->members_visualizations[1]->first_view);
        $this->assertEquals(3, $first_page->members_visualizations[1]->member_canvas_id);
        $this->assertEquals(100, $first_page->members_visualizations[1]->resource_canvas_id);
    }

    public function test_discussion_topics_interactions(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
            ['canvas_id' => 3, "name" => 'Oscar Alvarez'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(3), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(500);
        $course->addModules([['canvas_id' => 500, 'name' => 'First Module']]);
        $course->addDiscussionTopics([
            ['canvas_id' => 100, 'title' => 'some discussion 1',
            'module_item' => ['canvas_id'=> 7, 'module_id' => $module_id, 'position' => 5]],
            ['canvas_id' => 300, 'title' => 'some discussion 2']
        ]);
        $now = Carbon::now()->format('Y-m-d H:m:s');
        $student_canvas_id = 2; // Juan Valdez
        DiscussionTopicInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId($student_canvas_id),
            'item_id' => CourseBuilder::idFromCanvasId(100),
            'item_canvas_id' => 100,
            'viewed' => $now,
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(2, $report->DiscussionTopic->resources_count);
        $this->assertCount(2, $report->DiscussionTopic->resources_interactions);
        $this->assertEquals(100, $report->DiscussionTopic->resources_percentage);
        $this->assertEquals(25, $report->DiscussionTopic->resource_type_use_percentage);
        $first_page = $report->DiscussionTopic->resources_interactions[0];
        $second_page = $report->DiscussionTopic->resources_interactions[1];
        $this->assertCount(2, $first_page->members_visualizations);
        $this->assertEquals(50, $first_page->members_visualizations_percentage);
        $this->assertCount(2, $second_page->members_visualizations);
        $this->assertEquals(0, $second_page->members_visualizations_percentage);
        $this->assertEquals(true, $first_page->members_visualizations[0]->viewed);
        $this->assertEquals($now, $first_page->members_visualizations[0]->first_view);
        $this->assertEquals($student_canvas_id, $first_page->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(100, $first_page->members_visualizations[0]->resource_canvas_id);
        $this->assertEquals(false, $first_page->members_visualizations[1]->viewed);
        $this->assertEquals(null, $first_page->members_visualizations[1]->first_view);
        $this->assertEquals(3, $first_page->members_visualizations[1]->member_canvas_id);
        $this->assertEquals(100, $first_page->members_visualizations[1]->resource_canvas_id);
    }

    public function test_assignment_interactions(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
            ['canvas_id' => 3, "name" => 'Oscar Alvarez'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(3), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(500);
        $course->addModules([['canvas_id' => 500, 'name' => 'First Module']]);
        $course->addAssignments([
            ['canvas_id' => 100, 'title' => 'Assignment in first module',
            'submission_types' => 'online_upload', 'position' => 200,
            'module_item' => ['canvas_id' => 6, 'module_id' => $module_id, 'position' => 200]],
            ['canvas_id' => 300, 'title' => 'Assignment without module',
            'submission_types' => 'online_upload', 'position' => 200],
        ]);
        $now = Carbon::now()->format('Y-m-d H:m:s');
        $student_canvas_id = 2; // Juan Valdez
        AssignmentInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId($student_canvas_id),
            'item_id' => CourseBuilder::idFromCanvasId(100),
            'item_canvas_id' => 100,
            'viewed' => $now,
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(2, $report->Assignment->resources_count);
        $this->assertCount(2, $report->Assignment->resources_interactions);
        $this->assertEquals(100, $report->Assignment->resources_percentage);
        $this->assertEquals(25, $report->Assignment->resource_type_use_percentage);
        $first_page = $report->Assignment->resources_interactions[0];
        $second_page = $report->Assignment->resources_interactions[1];
        $this->assertCount(2, $first_page->members_visualizations);
        $this->assertEquals(50, $first_page->members_visualizations_percentage);
        $this->assertCount(2, $second_page->members_visualizations);
        $this->assertEquals(0, $second_page->members_visualizations_percentage);
        $this->assertEquals(true, $first_page->members_visualizations[0]->viewed);
        $this->assertEquals($now, $first_page->members_visualizations[0]->first_view);
        $this->assertEquals($student_canvas_id, $first_page->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(100, $first_page->members_visualizations[0]->resource_canvas_id);
        $this->assertEquals(false, $first_page->members_visualizations[1]->viewed);
        $this->assertEquals(null, $first_page->members_visualizations[1]->first_view);
        $this->assertEquals(3, $first_page->members_visualizations[1]->member_canvas_id);
        $this->assertEquals(100, $first_page->members_visualizations[1]->resource_canvas_id);
    }

    public function test_external_url_interactions(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
            ['canvas_id' => 3, "name" => 'Oscar Alvarez'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(3), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(500);
        $course->addModules([['canvas_id' => 500, 'name' => 'First Module']]);
        $course->addUrls([
            ['canvas_id' => 100, 'title' => 'Some url', 'module_id' => $module_id,
            'position' => 60, 'url' => 'http://www.google.cl'],
            ['canvas_id' => 300, 'title' => 'Some url', 'module_id' => $module_id,
            'position' => 60, 'url' => 'http://www.google.cl'],
        ]);
        $now = Carbon::now()->format('Y-m-d H:m:s');
        $student_canvas_id = 2; // Juan Valdez
        ExternalUrlInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId($student_canvas_id),
            'item_id' => CourseBuilder::idFromCanvasId(100),
            'item_canvas_id' => 100,
            'viewed' => $now,
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(2, $report->ExternalUrl->resources_count);
        $this->assertCount(2, $report->ExternalUrl->resources_interactions);
        $this->assertEquals(100, $report->ExternalUrl->resources_percentage);
        $this->assertEquals(25, $report->ExternalUrl->resource_type_use_percentage);
        $first_page = $report->ExternalUrl->resources_interactions[0];
        $second_page = $report->ExternalUrl->resources_interactions[1];
        $this->assertCount(2, $first_page->members_visualizations);
        $this->assertEquals(50, $first_page->members_visualizations_percentage);
        $this->assertCount(2, $second_page->members_visualizations);
        $this->assertEquals(0, $second_page->members_visualizations_percentage);
        $this->assertEquals(true, $first_page->members_visualizations[0]->viewed);
        $this->assertEquals($now, $first_page->members_visualizations[0]->first_view);
        $this->assertEquals($student_canvas_id, $first_page->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(100, $first_page->members_visualizations[0]->resource_canvas_id);
        $this->assertEquals(false, $first_page->members_visualizations[1]->viewed);
        $this->assertEquals(null, $first_page->members_visualizations[1]->first_view);
        $this->assertEquals(3, $first_page->members_visualizations[1]->member_canvas_id);
        $this->assertEquals(100, $first_page->members_visualizations[1]->resource_canvas_id);
    }

    public function test_context_external_tool_interactions(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
            ['canvas_id' => 3, "name" => 'Oscar Alvarez'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(3), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(500);
        $course->addModules([['canvas_id' => 500, 'name' => 'First Module']]);
        $course->addExternalTools([
            ['canvas_id' => 100, 'title' => 'some tool 1', 'module_id' => $module_id,
            'position' => 61, 'url' => 'http://www.launchLTI.cl'],
            ['canvas_id' => 300, 'title' => 'some tool 2', 'module_id' => $module_id,
            'position' => 61, 'url' => 'http://www.launch2LTI.cl']
        ]);
        $now = Carbon::now()->format('Y-m-d H:m:s');
        $student_canvas_id = 2; // Juan Valdez
        ContextExternalToolInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId($student_canvas_id),
            'item_id' => CourseBuilder::idFromCanvasId(100),
            'item_canvas_id' => 100,
            'viewed' => $now,
            'url' => 'whatever',
            'device' => 'MOBILE'
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->resourceTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(2, $report->ContextExternalTool->resources_count);
        $this->assertCount(2, $report->ContextExternalTool->resources_interactions);
        $this->assertEquals(100, $report->ContextExternalTool->resources_percentage);
        $this->assertEquals(25, $report->ContextExternalTool->resource_type_use_percentage);
        $first_page = $report->ContextExternalTool->resources_interactions[0];
        $second_page = $report->ContextExternalTool->resources_interactions[1];
        $this->assertCount(2, $first_page->members_visualizations);
        $this->assertEquals(50, $first_page->members_visualizations_percentage);
        $this->assertCount(2, $second_page->members_visualizations);
        $this->assertEquals(0, $second_page->members_visualizations_percentage);
        $this->assertEquals(true, $first_page->members_visualizations[0]->viewed);
        $this->assertEquals($now, $first_page->members_visualizations[0]->first_view);
        $this->assertEquals($student_canvas_id, $first_page->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(100, $first_page->members_visualizations[0]->resource_canvas_id);
        $this->assertEquals(false, $first_page->members_visualizations[1]->viewed);
        $this->assertEquals(null, $first_page->members_visualizations[1]->first_view);
        $this->assertEquals(3, $first_page->members_visualizations[1]->member_canvas_id);
        $this->assertEquals(100, $first_page->members_visualizations[1]->resource_canvas_id);
    }

    private function processReports(int $course_canvas_id) {
        (new devController())->rebuildReports($course_canvas_id);
    }
}
