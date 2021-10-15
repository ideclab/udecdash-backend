<?php

namespace Tests\Feature;

use App\Classes\Course;
use App\Http\Controllers\CourseController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\CourseBuilder;
use Tests\TestCase;

class CourseStructureTest extends TestCase {

    public function test_get_only_active_and_unpublished_modules() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addModules([
           ['canvas_id' => 1, 'name' => 'Module One'],
           ['canvas_id' => 2, 'name' => 'Module Two', 'workflow_state' => 'unpublished'],
           ['canvas_id' => 3, 'name' => 'Module Three', 'workflow_state' => 'deleted']
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(2, $response->structure);
        $this->assertEquals($response->structure[0]->canvas_id, 1);
        $this->assertEquals($response->structure[1]->canvas_id, 2);
    }

    public function test_return_array_for_empty_modules() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(0, $response->structure);
    }

    public function test_return_array_for_one_module() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addModules([
           ['canvas_id' => 1, 'name' => 'Module One'],
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
    }

    public function test_add_fake_module_for_pages_without_modules() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addPages([
            ['canvas_id' => 22, 'title' => 'Page without module'],
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->canvas_id, Course::FAKE_MODULE_ID);
        $this->assertEquals($response->structure[0]->resources[0]->canvas_id, 22);
    }

    public function test_add_fake_module_for_quizzes_without_modules() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addQuizzes([
            ['canvas_id' => 555, 'name' => 'Quiz without module', 'quiz_type' => 'assignment'],
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->canvas_id, Course::FAKE_MODULE_ID);
        $this->assertEquals($response->structure[0]->resources[0]->canvas_id, 555);
    }

    public function test_dont_add_fake_module_for_files_without_modules() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addFiles([
            ['canvas_id' => 35, 'display_name' => 'file_state_delete_modulo_!.pdf',
            'content_type' => 'application/pdf']
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(0, $response->structure);
    }

    public function test_add_fake_module_for_assignments_without_modules() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addAssignments([
            ['canvas_id' => 102, 'title' => 'Assignment without module',
            'submission_types' => 'online_upload', 'position' => 200],
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->canvas_id, Course::FAKE_MODULE_ID);
        $this->assertEquals($response->structure[0]->resources[0]->canvas_id, 102);
    }

    public function test_dont_add_fake_module_for_discussion_topics_without_modules() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addDiscussionTopics([
            ['canvas_id' => 11, 'title' => 'Discussion topic 1']
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->canvas_id, Course::FAKE_MODULE_ID);
        $this->assertEquals($response->structure[0]->resources[0]->canvas_id, 11);
    }

    public function test_get_page_in_the_module() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addModules([
            ['canvas_id' => 1, 'name' => 'Modulo uno'],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(1);
        $course->addPages([
            ['canvas_id' => 1, 'title' => 'Pagina en módulo 1',
            'module_item' => ['canvas_id'=> 1, 'module_id' => $module_id, 'position' => 1]],
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->id, $module_id);
        $resource = $response->structure[0]->resources[0];
        $this->assertEquals($resource->canvas_id, 1);
        $this->assertEquals($resource->workflow_state, 'active');
        $this->assertEquals($resource->module_item_canvas_id, 1);
        $this->assertEquals($resource->module_item_workflow_state, 'active');
    }

    public function test_dont_get_pages_deleted_in_the_module() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addModules([
            ['canvas_id' => 1, 'name' => 'Modulo uno'],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(1);
        $course->addPages([
            ['canvas_id' => 1, 'title' => 'Pagina en módulo 1', 'workflow_state' => 'deleted',
            'module_item' => ['canvas_id'=> 1, 'module_id' => $module_id, 'position' => 1]],
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->id, $module_id);
        $this->assertCount(0, $response->structure[0]->resources);
    }

    public function test_get_quiz_in_the_module() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addModules([
            ['canvas_id' => 1, 'name' => 'Modulo uno'],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(1);
        $course->addQuizzes([
            ['canvas_id' => 3, 'name' => 'Quiz módulo 3', 'quiz_type' => 'assignment',
            'assignment' => ['canvas_id' => 1],
            'module_item' => ['canvas_id'=> 5, 'module_id' => $module_id, 'position' => 2]]
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->id, $module_id);
        $resource = $response->structure[0]->resources[0];
        $this->assertEquals($resource->canvas_id, 3);
        $this->assertEquals($resource->workflow_state, 'published');
        $this->assertEquals($resource->module_item_canvas_id, 5);
        $this->assertEquals($resource->module_item_workflow_state, 'active');
    }

    public function test_dont_get_quiz_deleted_in_the_module() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addModules([
            ['canvas_id' => 1, 'name' => 'Modulo uno'],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(1);
        $course->addQuizzes([
            ['canvas_id' => 3, 'name' => 'Quiz módulo 3', 'quiz_type' => 'assignment',
            'workflow_state' => 'deleted', 'assignment' => ['canvas_id' => 1],
            'module_item' => ['canvas_id'=> 5, 'module_id' => $module_id, 'position' => 2]]
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->id, $module_id);
        $this->assertCount(0, $response->structure[0]->resources);
    }

    public function test_get_files_in_the_module() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addModules([
            ['canvas_id' => 1, 'name' => 'Modulo uno'],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(1);
        $course->addFiles([
            ['canvas_id' => 1, 'display_name' => 'file_state_delete_modulo_!.pdf',
            'content_type' => 'application/pdf', 'file_state' => 'available',
            'module_item' => ['canvas_id' => 10, 'module_id' => $module_id,'position' => 20]],
        ]);
        // // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        // //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->id, $module_id);
        $resource = $response->structure[0]->resources[0];
        $this->assertEquals($resource->canvas_id, 1);
        $this->assertEquals($resource->workflow_state, 'available');
        $this->assertEquals($resource->module_item_canvas_id, 10);
        $this->assertEquals($resource->module_item_workflow_state, 'active');
    }

    public function test_dont_get_files_deleted_in_the_module() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addModules([
            ['canvas_id' => 1, 'name' => 'Modulo uno'],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(1);
        $course->addFiles([
            ['canvas_id' => 1, 'display_name' => 'file_state_delete_modulo_!.pdf',
            'content_type' => 'application/pdf', 'file_state' => 'deleted',
            'module_item' => ['canvas_id' => 10, 'module_id' => $module_id,'position' => 20]],
        ]);
        // // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        // //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->id, $module_id);
        $this->assertCount(0, $response->structure[0]->resources);
    }

    public function test_get_assignment_in_the_module() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addModules([
            ['canvas_id' => 1, 'name' => 'Modulo uno'],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(1);
        $course->addAssignments([
            ['canvas_id' => 100, 'title' => 'Tarea en módulo 1', 'submission_types' => 'online_upload',
            'position' => 200, 'module_item' => ['canvas_id' => 40, 'module_id' => $module_id,
            'position' => 200]],
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->id, $module_id);
        $resource = $response->structure[0]->resources[0];
        $this->assertEquals($resource->canvas_id, 100);
        $this->assertEquals($resource->workflow_state, 'published');
        $this->assertEquals($resource->module_item_canvas_id, 40);
        $this->assertEquals($resource->module_item_workflow_state, 'active');
    }

    public function test_dont_get_assignment_deleted_in_the_module() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addModules([
            ['canvas_id' => 1, 'name' => 'Modulo uno'],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(1);
        $course->addAssignments([
            ['canvas_id' => 100, 'title' => 'Tarea en módulo 1', 'submission_types' => 'online_upload',
            'position' => 200, 'workflow_state' =>  'deleted',
            'module_item' => ['canvas_id' => 40, 'module_id' => $module_id, 'position' => 200]],
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->id, $module_id);
        $this->assertCount(0, $response->structure[0]->resources);
    }

    public function test_get_discussion_topics_in_the_module() {
        // // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addModules([
            ['canvas_id' => 1, 'name' => 'Modulo uno'],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(1);
        $course->addDiscussionTopics([
            ['canvas_id' => 1, 'title' => 'Foro Módulo 1',
            'module_item' => ['canvas_id'=> 90, 'module_id' => $module_id, 'position' => 5]]
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->id, $module_id);
        $resource = $response->structure[0]->resources[0];
        $this->assertEquals($resource->canvas_id, 1);
        $this->assertEquals($resource->workflow_state, 'active');
        $this->assertEquals($resource->module_item_canvas_id, 90);
        $this->assertEquals($resource->module_item_workflow_state, 'active');
    }

    public function test_dont_get_discussion_topics_deleted_in_the_module() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addModules([
            ['canvas_id' => 1, 'name' => 'Modulo uno'],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(1);
        $course->addDiscussionTopics([
            ['canvas_id' => 1, 'title' => 'Foro Módulo 1', 'workflow_state' => 'deleted',
            'module_item' => ['canvas_id'=> 90, 'module_id' => $module_id, 'position' => 5]]
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->id, $module_id);
        $this->assertCount(0, $response->structure[0]->resources);
    }

    public function test_get_urls_in_the_module() {
        // // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addModules([
            ['canvas_id' => 1, 'name' => 'Modulo uno'],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(1);
        $course->addUrls([
            ['canvas_id' => 1000, 'title' => 'Enlace módulo 1', 'module_id' => $module_id,
            'position' => 60, 'url' => 'http://www.google.cl'],
        ]);
        // // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        // //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->id, $module_id);
        $resource = $response->structure[0]->resources[0];
        $this->assertEquals($resource->canvas_id, 1000);
        $this->assertEquals($resource->workflow_state, 'active');
        $this->assertEquals($resource->module_item_canvas_id, 1000);
        $this->assertEquals($resource->module_item_workflow_state, 'active');
    }

    public function test_dont_get_urls_deleted_in_the_module() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addModules([
            ['canvas_id' => 1, 'name' => 'Modulo uno'],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(1);
        $course->addUrls([
            ['canvas_id' => 1000, 'title' => 'Enlace módulo 1', 'module_id' => $module_id,
            'position' => 60, 'url' => 'http://www.google.cl', 'workflow_state' => 'deleted'],
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->id, $module_id);
        $this->assertCount(0, $response->structure[0]->resources);
    }

    public function test_get_external_tools_in_the_module() {
        // // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addModules([
            ['canvas_id' => 1, 'name' => 'Modulo uno'],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(1);
        $course->addExternalTools([
            ['canvas_id' => 5000, 'title' => 'External Tool 1', 'module_id' => $module_id,
            'position' => 61, 'url' => 'http://www.launchLTI.cl'],
        ]);
        // // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        // //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->id, $module_id);
        $resource = $response->structure[0]->resources[0];
        $this->assertEquals($resource->canvas_id, 5000);
        $this->assertEquals($resource->workflow_state, 'active');
        $this->assertEquals($resource->module_item_canvas_id, 5000);
        $this->assertEquals($resource->module_item_workflow_state, 'active');
    }

    public function test_dont_get_external_tools_deleted_in_the_module() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addModules([
            ['canvas_id' => 1, 'name' => 'Modulo uno'],
        ]);
        $module_id = CourseBuilder::idFromCanvasId(1);
        $course->addExternalTools([
            ['canvas_id' => 5000, 'title' => 'External Tool 1', 'module_id' => $module_id,
            'position' => 61, 'url' => 'http://www.launchLTI.cl', 'workflow_state' => 'deleted'],
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->id, $module_id);
        $this->assertCount(0, $response->structure[0]->resources);
    }

    public function test_move_active_resources_in_deleted_module_to_fake_module() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addModules([
            ['canvas_id' => 1, 'name' => 'First Module', 'workflow_state' => 'deleted']
        ]);
        $module_id = CourseBuilder::idFromCanvasId(1);
        $course->addPages([
            ['canvas_id' => 1, 'title' => 'First page',
            'module_item' => ['canvas_id'=> 1, 'module_id' => $module_id, 'position' => 1]],
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->id, Course::FAKE_MODULE_ID);
        $this->assertCount(1, $response->structure[0]->resources);
        $resource = $response->structure[0]->resources[0];
        $this->assertEquals($resource->canvas_id, 1);
        $this->assertEquals($resource->workflow_state, 'active');
        $this->assertEquals($resource->module_item_canvas_id, null);
        $this->assertEquals($resource->module_item_workflow_state, null);
    }

    public function test_get_active_module_and_move_resources_in_deleted_module_to_fake_module() {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addModules([
            ['canvas_id' => 1, 'name' => 'First Module'],
            ['canvas_id' => 2, 'name' => 'Second Module', 'workflow_state' => 'deleted']
        ]);
        $module_id = CourseBuilder::idFromCanvasId(2);
        $course->addPages([
            ['canvas_id' => 1, 'title' => 'First page',
            'module_item' => ['canvas_id'=> 1, 'module_id' => $module_id, 'position' => 1]],
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(2, $response->structure);
        $this->assertEquals($response->structure[0]->canvas_id, 1);
        $this->assertEquals($response->structure[1]->id, Course::FAKE_MODULE_ID);
        $this->assertCount(0, $response->structure[0]->resources);
        $this->assertCount(1, $response->structure[1]->resources);
        $resource = $response->structure[1]->resources[0];
        $this->assertEquals($resource->canvas_id, 1);
        $this->assertEquals($resource->workflow_state, 'active');
        $this->assertEquals($resource->module_item_canvas_id, null);
    }

    public function test_delete_module_item_information_for_resources_moved_to_fake_modules(){
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addModules([
            ['canvas_id' => 2, 'name' => 'Second Module', 'workflow_state' => 'deleted']
        ]);
        $module_id = CourseBuilder::idFromCanvasId(2);
        $course->addPages([
            ['canvas_id' => 1, 'title' => 'First page',
            'module_item' => ['canvas_id'=> 1, 'module_id' => $module_id, 'position' => 1]],
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        //  Assertions
        $this->assertIsArray($response->structure);
        $this->assertCount(1, $response->structure);
        $this->assertEquals($response->structure[0]->id, Course::FAKE_MODULE_ID);
        $this->assertCount(1, $response->structure[0]->resources);
        $resource = $response->structure[0]->resources[0];
        $this->assertEquals($resource->module_item_canvas_id, null);
        $this->assertEquals($resource->module_item_workflow_state, null);
    }
}
