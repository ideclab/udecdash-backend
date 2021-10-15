<?php

namespace Tests\Feature;

use App\Classes\Course;
use App\Classes\MimeType;
use App\Http\Controllers\devController;
use App\Http\Controllers\ReportsController;
use App\Models\AttachmentInteraction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\CourseBuilder;
use Tests\TestCase;

class FileTypeUsageTest extends TestCase
{
    public function test_course_without_files(){
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
        $report = $controller->fileTypeUsage($course->getCourseCanvasId(), $section_id);
        $result = json_decode($report->getContent());
        // Assertions
        $existing_types = ['Documento', 'Imagen', 'Audio','Video','Texto','Archivo comprimido', 'Otro'];
        foreach($result as $file_type => $type){
            $this->assertContains($file_type, $existing_types);
            $this->assertEquals(0, $type->file_count);
            $this->assertEquals(0, $type->file_percentage);
            $this->assertCount(0, $type->resources);
            $this->assertCount(0, $type->resources_interactions);
            $this->assertEquals(0, $type->downloads_count);
            $this->assertEquals(0, $type->resource_type_usage_percentage);
        }
    }

    public function test_document_course_percentage(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
            ['canvas_id' => 3, "name" => 'Alexa Lara'],
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
        $mime_types = MimeType::get_document_mimetypes();
        foreach($mime_types as $index => $mime){
            $course->addFiles([
                ['canvas_id' => ($index + 1), 'display_name' => "file_{$index}.pdf",
                'content_type' => $mime,
                'module_item' => ['canvas_id' => ($index + 1), 'module_id' => $module_id,'position' => 20]]
            ]);
        }
        $course->addFiles([
                ['canvas_id' => 500, 'display_name' => "audio.aac",
                'content_type' => 'audio/aac',
                'module_item' => ['canvas_id' => 800, 'module_id' => $module_id,'position' => 20]]
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->fileTypeUsage($course->getCourseCanvasId(), $section_id);
        $result = json_decode($report->getContent());
        // Assertions
        $this->assertCount(22, $result->Documento->resources);
        $this->assertEquals(22, $result->Documento->file_count);
        $this->assertEquals(95.65, $result->Documento->file_percentage);
        $this->assertCount(22, $result->Documento->resources_interactions);
        $this->assertCount(1, $result->Audio->resources);
        $this->assertEquals(1, $result->Audio->file_count);
        $this->assertEquals(4.35, $result->Audio->file_percentage);
        $this->assertCount(1, $result->Audio->resources_interactions);
        foreach($result->Documento->resources as $index => $resource){
            $this->assertEquals(($index + 1), $resource->module_item_canvas_id);
            $this->assertEquals("active", $resource->module_item_workflow_state);
        }
    }

    public function test_image_course_percentage(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
            ['canvas_id' => 3, "name" => 'Alexa Lara'],
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
        $mime_types = MimeType::get_image_mimetypes();
        foreach($mime_types as $index => $mime){
            $course->addFiles([
                ['canvas_id' => ($index + 1), 'display_name' => "file_{$index}.mp3",
                'content_type' => $mime,
                'module_item' => ['canvas_id' => ($index + 1), 'module_id' => $module_id,'position' => 20]]
            ]);
        }
        $course->addFiles([
                ['canvas_id' => 500, 'display_name' => "hola.pdf",
                'content_type' => 'application/pdf',
                'module_item' => ['canvas_id' => 800, 'module_id' => $module_id,'position' => 20]]
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->fileTypeUsage($course->getCourseCanvasId(), $section_id);
        $result = json_decode($report->getContent());
        // Assertions
        $this->assertCount(11, $result->Imagen->resources);
        $this->assertEquals(11, $result->Imagen->file_count);
        $this->assertEquals(91.67, $result->Imagen->file_percentage);
        $this->assertCount(11, $result->Imagen->resources_interactions);
        $this->assertCount(1, $result->Documento->resources);
        $this->assertEquals(1, $result->Documento->file_count);
        $this->assertEquals(8.33, $result->Documento->file_percentage);
        $this->assertCount(1, $result->Documento->resources_interactions);
        foreach($result->Imagen->resources as $index => $resource){
            $this->assertEquals(($index + 1), $resource->module_item_canvas_id);
            $this->assertEquals("active", $resource->module_item_workflow_state);
        }
    }

    public function test_audio_course_percentage(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
            ['canvas_id' => 3, "name" => 'Alexa Lara'],
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
        $mime_types = MimeType::get_audio_mimetypes();
        foreach($mime_types as $index => $mime){
            $course->addFiles([
                ['canvas_id' => ($index + 1), 'display_name' => "file_{$index}.mp3",
                'content_type' => $mime,
                'module_item' => ['canvas_id' => ($index + 1), 'module_id' => $module_id,'position' => 20]]
            ]);
        }
        $course->addFiles([
                ['canvas_id' => 500, 'display_name' => "hola.pdf",
                'content_type' => 'application/pdf',
                'module_item' => ['canvas_id' => 800, 'module_id' => $module_id,'position' => 20]]
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->fileTypeUsage($course->getCourseCanvasId(), $section_id);
        $result = json_decode($report->getContent());
        // Assertions
        $this->assertCount(16, $result->Audio->resources);
        $this->assertEquals(16, $result->Audio->file_count);
        $this->assertEquals(94.12, $result->Audio->file_percentage);
        $this->assertCount(16, $result->Audio->resources_interactions);
        $this->assertCount(1, $result->Documento->resources);
        $this->assertEquals(1, $result->Documento->file_count);
        $this->assertEquals(5.88, $result->Documento->file_percentage);
        $this->assertCount(1, $result->Documento->resources_interactions);
        foreach($result->Audio->resources as $index => $resource){
            $this->assertEquals(($index + 1), $resource->module_item_canvas_id);
            $this->assertEquals("active", $resource->module_item_workflow_state);
        }
    }

    public function test_video_course_percentage(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
            ['canvas_id' => 3, "name" => 'Alexa Lara'],
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
        $mime_types = MimeType::get_video_mimetypes();
        foreach($mime_types as $index => $mime){
            $course->addFiles([
                ['canvas_id' => ($index + 1), 'display_name' => "file_{$index}.mp4",
                'content_type' => $mime,
                'module_item' => ['canvas_id' => ($index + 1), 'module_id' => $module_id,'position' => 20]]
            ]);
        }
        $course->addFiles([
                ['canvas_id' => 500, 'display_name' => "hola.pdf",
                'content_type' => 'application/pdf',
                'module_item' => ['canvas_id' => 800, 'module_id' => $module_id,'position' => 20]]
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->fileTypeUsage($course->getCourseCanvasId(), $section_id);
        $result = json_decode($report->getContent());
        // Assertions
        $this->assertCount(13, $result->Video->resources);
        $this->assertEquals(13, $result->Video->file_count);
        $this->assertEquals(92.86, $result->Video->file_percentage);
        $this->assertCount(13, $result->Video->resources_interactions);
        $this->assertCount(1, $result->Documento->resources);
        $this->assertEquals(1, $result->Documento->file_count);
        $this->assertEquals(7.14, $result->Documento->file_percentage);
        $this->assertCount(1, $result->Documento->resources_interactions);
        foreach($result->Video->resources as $index => $resource){
            $this->assertEquals(($index + 1), $resource->module_item_canvas_id);
            $this->assertEquals("active", $resource->module_item_workflow_state);
        }
    }

    public function test_text_course_percentage(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
            ['canvas_id' => 3, "name" => 'Alexa Lara'],
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
        $mime_types = MimeType::get_text_mimetypes();
        foreach($mime_types as $index => $mime){
            $course->addFiles([
                ['canvas_id' => ($index + 1), 'display_name' => "file_{$index}.txt",
                'content_type' => $mime,
                'module_item' => ['canvas_id' => ($index + 1), 'module_id' => $module_id,'position' => 20]]
            ]);
        }
        $course->addFiles([
                ['canvas_id' => 500, 'display_name' => "hola.pdf",
                'content_type' => 'application/pdf',
                'module_item' => ['canvas_id' => 800, 'module_id' => $module_id,'position' => 20]]
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->fileTypeUsage($course->getCourseCanvasId(), $section_id);
        $result = json_decode($report->getContent());
        // Assertions
        $this->assertCount(11, $result->Texto->resources);
        $this->assertEquals(11, $result->Texto->file_count);
        $this->assertEquals(91.67, $result->Texto->file_percentage);
        $this->assertCount(11, $result->Texto->resources_interactions);
        $this->assertCount(1, $result->Documento->resources);
        $this->assertEquals(1, $result->Documento->file_count);
        $this->assertEquals(8.33, $result->Documento->file_percentage);
        $this->assertCount(1, $result->Documento->resources_interactions);
        foreach($result->Texto->resources as $index => $resource){
            $this->assertEquals(($index + 1), $resource->module_item_canvas_id);
            $this->assertEquals("active", $resource->module_item_workflow_state);
        }
    }

    public function test_compress_file_course_percentage(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
            ['canvas_id' => 3, "name" => 'Alexa Lara'],
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
        $mime_types = MimeType::get_file_mimetypes();
        foreach($mime_types as $index => $mime){
            $course->addFiles([
                ['canvas_id' => ($index + 1), 'display_name' => "file_{$index}.txt",
                'content_type' => $mime,
                'module_item' => ['canvas_id' => ($index + 1), 'module_id' => $module_id,'position' => 20]]
            ]);
        }
        $course->addFiles([
                ['canvas_id' => 500, 'display_name' => "hola.pdf",
                'content_type' => 'application/pdf',
                'module_item' => ['canvas_id' => 800, 'module_id' => $module_id,'position' => 20]]
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->fileTypeUsage($course->getCourseCanvasId(), $section_id);
        $result = json_decode($report->getContent());
        // Assertions
        $this->assertCount(7, $result->{'Archivo comprimido'}->resources);
        $this->assertEquals(7, $result->{'Archivo comprimido'}->file_count);
        $this->assertEquals(87.5, $result->{'Archivo comprimido'}->file_percentage);
        $this->assertCount(7, $result->{'Archivo comprimido'}->resources_interactions);
        $this->assertCount(1, $result->Documento->resources);
        $this->assertEquals(1, $result->Documento->file_count);
        $this->assertEquals(12.5, $result->Documento->file_percentage);
        $this->assertCount(1, $result->Documento->resources_interactions);
        foreach($result->{'Archivo comprimido'}->resources as $index => $resource){
            $this->assertEquals(($index + 1), $resource->module_item_canvas_id);
            $this->assertEquals("active", $resource->module_item_workflow_state);
        }
    }

    public function test_other_types_course_percentage(){
        // Prepare
        $course = new CourseBuilder(1, 'Empty course');
        $section_id = 1000;
        $course->addSections([
            ['canvas_id' => $section_id, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
            ['canvas_id' => 3, "name" => 'Alexa Lara'],
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
        $mime_types = ["other/mime", "other/whatever"];
        foreach($mime_types as $index => $mime){
            $course->addFiles([
                ['canvas_id' => ($index + 1), 'display_name' => "file_{$index}.txt",
                'content_type' => $mime,
                'module_item' => ['canvas_id' => ($index + 1), 'module_id' => $module_id,'position' => 20]]
            ]);
        }
        $course->addFiles([
                ['canvas_id' => 500, 'display_name' => "hola.pdf",
                'content_type' => 'application/pdf',
                'module_item' => ['canvas_id' => 800, 'module_id' => $module_id,'position' => 20]]
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->fileTypeUsage($course->getCourseCanvasId(), $section_id);
        $result = json_decode($report->getContent());
        // Assertions
        $this->assertCount(2, $result->Otro->resources);
        $this->assertEquals(2, $result->Otro->file_count);
        $this->assertEquals(66.67, $result->Otro->file_percentage);
        $this->assertCount(2, $result->Otro->resources_interactions);
        $this->assertCount(1, $result->Documento->resources);
        $this->assertEquals(1, $result->Documento->file_count);
        $this->assertEquals(33.33, $result->Documento->file_percentage);
        $this->assertCount(1, $result->Documento->resources_interactions);
        foreach($result->Otro->resources as $index => $resource){
            $this->assertEquals(($index + 1), $resource->module_item_canvas_id);
            $this->assertEquals("active", $resource->module_item_workflow_state);
        }
    }

    public function test_document_interactions(){
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
        $first_view = Carbon::now()->subDays(3)->format('Y-m-d H:m:s');
        $student_canvas_id = 2; // Juan Valdez
        AttachmentInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId($student_canvas_id),
            'item_id' => CourseBuilder::idFromCanvasId(100),
            'item_canvas_id' => 100,
            'viewed' => $first_view,
            'url' => '/whatever/download?download_frd=1',
            'device' => 'MOBILE'
        ]);
        AttachmentInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId($student_canvas_id),
            'item_id' => CourseBuilder::idFromCanvasId(100),
            'item_canvas_id' => 100,
            'viewed' => Carbon::now()->format('Y-m-d H:m:s'),
            'url' => '/whatever/download?download_frd=1',
            'device' => 'DESKTOP'
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->fileTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(2, $report->Documento->downloads_count);
        $this->assertEquals(2, $report->Documento->resources_interactions[0]->members_downloads_count);
        $this->assertEquals(0, $report->Documento->resources_interactions[1]->members_downloads_count);
        $this->assertEquals(25, $report->Documento->resource_type_usage_percentage);
        $first_resource = $report->Documento->resources_interactions[0];
        $second_resource = $report->Documento->resources_interactions[1];
        $this->assertCount(2, $first_resource->members_visualizations);
        $this->assertEquals(100, $first_resource->resource_canvas_id);
        $this->assertEquals(50, $first_resource->members_visualizations_percentage);
        $this->assertCount(2, $second_resource->members_visualizations);
        $this->assertEquals(300, $second_resource->resource_canvas_id);
        $this->assertEquals(0, $second_resource->members_visualizations_percentage);
        $this->assertEquals(true, $first_resource->members_visualizations[0]->viewed);
        $this->assertEquals($first_view, $first_resource->members_visualizations[0]->first_view);
        $this->assertEquals($student_canvas_id, $first_resource->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(100, $first_resource->members_visualizations[0]->resource_canvas_id);
        $this->assertEquals(false, $first_resource->members_visualizations[1]->viewed);
        $this->assertEquals(null, $first_resource->members_visualizations[1]->first_view);
        $this->assertEquals(3, $first_resource->members_visualizations[1]->member_canvas_id);
        $this->assertEquals(100, $first_resource->members_visualizations[1]->resource_canvas_id);
    }

    public function test_image_interactions(){
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
            ['canvas_id' => 100, 'display_name' => 'some_file_1.png',
            'content_type' => 'image/png',
            'module_item' => ['canvas_id' => 4, 'module_id' => $module_id,'position' => 20]],
            ['canvas_id' => 300, 'display_name' => 'some_file_2.png',
            'content_type' => 'image/png',
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
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->fileTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(0, $report->Imagen->downloads_count);
        $this->assertEquals(0, $report->Imagen->resources_interactions[0]->members_downloads_count);
        $this->assertEquals(0, $report->Imagen->resources_interactions[1]->members_downloads_count);
        $this->assertEquals(25, $report->Imagen->resource_type_usage_percentage);
        $first_resource = $report->Imagen->resources_interactions[0];
        $second_resource = $report->Imagen->resources_interactions[1];
        $this->assertCount(2, $first_resource->members_visualizations);
        $this->assertEquals(100, $first_resource->resource_canvas_id);
        $this->assertEquals(50, $first_resource->members_visualizations_percentage);
        $this->assertCount(2, $second_resource->members_visualizations);
        $this->assertEquals(300, $second_resource->resource_canvas_id);
        $this->assertEquals(0, $second_resource->members_visualizations_percentage);
        $this->assertEquals(true, $first_resource->members_visualizations[0]->viewed);
        $this->assertEquals($now, $first_resource->members_visualizations[0]->first_view);
        $this->assertEquals($student_canvas_id, $first_resource->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(100, $first_resource->members_visualizations[0]->resource_canvas_id);
        $this->assertEquals(false, $first_resource->members_visualizations[1]->viewed);
        $this->assertEquals(null, $first_resource->members_visualizations[1]->first_view);
        $this->assertEquals(3, $first_resource->members_visualizations[1]->member_canvas_id);
        $this->assertEquals(100, $first_resource->members_visualizations[1]->resource_canvas_id);
    }

    public function test_audio_interactions(){
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
            ['canvas_id' => 100, 'display_name' => 'some_file_1.mp3',
            'content_type' => 'audio/mpeg',
            'module_item' => ['canvas_id' => 4, 'module_id' => $module_id,'position' => 20]],
            ['canvas_id' => 300, 'display_name' => 'some_file_2.mp3',
            'content_type' => 'audio/mpeg',
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
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->fileTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(0, $report->Audio->downloads_count);
        $this->assertEquals(0, $report->Audio->resources_interactions[0]->members_downloads_count);
        $this->assertEquals(0, $report->Audio->resources_interactions[1]->members_downloads_count);
        $this->assertEquals(25, $report->Audio->resource_type_usage_percentage);
        $first_resource = $report->Audio->resources_interactions[0];
        $second_resource = $report->Audio->resources_interactions[1];
        $this->assertCount(2, $first_resource->members_visualizations);
        $this->assertEquals(100, $first_resource->resource_canvas_id);
        $this->assertEquals(50, $first_resource->members_visualizations_percentage);
        $this->assertCount(2, $second_resource->members_visualizations);
        $this->assertEquals(300, $second_resource->resource_canvas_id);
        $this->assertEquals(0, $second_resource->members_visualizations_percentage);
        $this->assertEquals(true, $first_resource->members_visualizations[0]->viewed);
        $this->assertEquals($now, $first_resource->members_visualizations[0]->first_view);
        $this->assertEquals($student_canvas_id, $first_resource->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(100, $first_resource->members_visualizations[0]->resource_canvas_id);
        $this->assertEquals(false, $first_resource->members_visualizations[1]->viewed);
        $this->assertEquals(null, $first_resource->members_visualizations[1]->first_view);
        $this->assertEquals(3, $first_resource->members_visualizations[1]->member_canvas_id);
        $this->assertEquals(100, $first_resource->members_visualizations[1]->resource_canvas_id);
    }

    public function test_video_interactions(){
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
            ['canvas_id' => 100, 'display_name' => 'some_file_1.mp4',
            'content_type' => 'video/mp4',
            'module_item' => ['canvas_id' => 4, 'module_id' => $module_id,'position' => 20]],
            ['canvas_id' => 300, 'display_name' => 'some_file_2.mp4',
            'content_type' => 'video/mp4',
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
            'url' => '/whatever/download/download?download_frd=1',
            'device' => 'DESKTOP'
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->fileTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(1, $report->Video->downloads_count);
        $this->assertEquals(1, $report->Video->resources_interactions[0]->members_downloads_count);
        $this->assertEquals(0, $report->Video->resources_interactions[1]->members_downloads_count);
        $this->assertEquals(25, $report->Video->resource_type_usage_percentage);
        $first_resource = $report->Video->resources_interactions[0];
        $second_resource = $report->Video->resources_interactions[1];
        $this->assertCount(2, $first_resource->members_visualizations);
        $this->assertEquals(100, $first_resource->resource_canvas_id);
        $this->assertEquals(50, $first_resource->members_visualizations_percentage);
        $this->assertCount(2, $second_resource->members_visualizations);
        $this->assertEquals(300, $second_resource->resource_canvas_id);
        $this->assertEquals(0, $second_resource->members_visualizations_percentage);
        $this->assertEquals(true, $first_resource->members_visualizations[0]->viewed);
        $this->assertEquals($now, $first_resource->members_visualizations[0]->first_view);
        $this->assertEquals($student_canvas_id, $first_resource->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(100, $first_resource->members_visualizations[0]->resource_canvas_id);
        $this->assertEquals(false, $first_resource->members_visualizations[1]->viewed);
        $this->assertEquals(null, $first_resource->members_visualizations[1]->first_view);
        $this->assertEquals(3, $first_resource->members_visualizations[1]->member_canvas_id);
        $this->assertEquals(100, $first_resource->members_visualizations[1]->resource_canvas_id);
    }

    public function test_text_interactions(){
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
            ['canvas_id' => 100, 'display_name' => 'some_file_1.csv',
            'content_type' => 'text/csv',
            'module_item' => ['canvas_id' => 4, 'module_id' => $module_id,'position' => 20]],
            ['canvas_id' => 300, 'display_name' => 'some_file_2.csv',
            'content_type' => 'text/csv',
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
            'url' => '/whatever/download/download?download_frd=1',
            'device' => 'DESKTOP'
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->fileTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(1, $report->Texto->downloads_count);
        $this->assertEquals(1, $report->Texto->resources_interactions[0]->members_downloads_count);
        $this->assertEquals(0, $report->Texto->resources_interactions[1]->members_downloads_count);
        $this->assertEquals(25, $report->Texto->resource_type_usage_percentage);
        $first_resource = $report->Texto->resources_interactions[0];
        $second_resource = $report->Texto->resources_interactions[1];
        $this->assertCount(2, $first_resource->members_visualizations);
        $this->assertEquals(100, $first_resource->resource_canvas_id);
        $this->assertEquals(50, $first_resource->members_visualizations_percentage);
        $this->assertCount(2, $second_resource->members_visualizations);
        $this->assertEquals(300, $second_resource->resource_canvas_id);
        $this->assertEquals(0, $second_resource->members_visualizations_percentage);
        $this->assertEquals(true, $first_resource->members_visualizations[0]->viewed);
        $this->assertEquals($now, $first_resource->members_visualizations[0]->first_view);
        $this->assertEquals($student_canvas_id, $first_resource->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(100, $first_resource->members_visualizations[0]->resource_canvas_id);
        $this->assertEquals(false, $first_resource->members_visualizations[1]->viewed);
        $this->assertEquals(null, $first_resource->members_visualizations[1]->first_view);
        $this->assertEquals(3, $first_resource->members_visualizations[1]->member_canvas_id);
        $this->assertEquals(100, $first_resource->members_visualizations[1]->resource_canvas_id);
    }

    public function test_compress_file_interactions(){
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
            ['canvas_id' => 100, 'display_name' => 'some_file_1.rar',
            'content_type' => 'application/x-rar-compressed',
            'module_item' => ['canvas_id' => 4, 'module_id' => $module_id,'position' => 20]],
            ['canvas_id' => 300, 'display_name' => 'some_file_2.rar',
            'content_type' => 'application/x-rar-compressed',
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
            'url' => '/whatever/download/download?download_frd=1',
            'device' => 'DESKTOP'
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->fileTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(1, $report->{'Archivo comprimido'}->downloads_count);
        $this->assertEquals(1, $report->{'Archivo comprimido'}->resources_interactions[0]->members_downloads_count);
        $this->assertEquals(0, $report->{'Archivo comprimido'}->resources_interactions[1]->members_downloads_count);
        $this->assertEquals(25, $report->{'Archivo comprimido'}->resource_type_usage_percentage);
        $first_resource = $report->{'Archivo comprimido'}->resources_interactions[0];
        $second_resource = $report->{'Archivo comprimido'}->resources_interactions[1];
        $this->assertCount(2, $first_resource->members_visualizations);
        $this->assertEquals(100, $first_resource->resource_canvas_id);
        $this->assertEquals(50, $first_resource->members_visualizations_percentage);
        $this->assertCount(2, $second_resource->members_visualizations);
        $this->assertEquals(300, $second_resource->resource_canvas_id);
        $this->assertEquals(0, $second_resource->members_visualizations_percentage);
        $this->assertEquals(true, $first_resource->members_visualizations[0]->viewed);
        $this->assertEquals($now, $first_resource->members_visualizations[0]->first_view);
        $this->assertEquals($student_canvas_id, $first_resource->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(100, $first_resource->members_visualizations[0]->resource_canvas_id);
        $this->assertEquals(false, $first_resource->members_visualizations[1]->viewed);
        $this->assertEquals(null, $first_resource->members_visualizations[1]->first_view);
        $this->assertEquals(3, $first_resource->members_visualizations[1]->member_canvas_id);
        $this->assertEquals(100, $first_resource->members_visualizations[1]->resource_canvas_id);
    }

    public function test_other_interactions(){
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
            ['canvas_id' => 100, 'display_name' => 'some_file_1.wtf',
            'content_type' => 'application/wtf',
            'module_item' => ['canvas_id' => 4, 'module_id' => $module_id,'position' => 20]],
            ['canvas_id' => 300, 'display_name' => 'some_file_2.wtf',
            'content_type' => 'application/wtf',
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
            'url' => '/whatever/download/download?=1',
            'device' => 'DESKTOP'
        ]);
        $controller = new ReportsController();
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->fileTypeUsage($course->getCourseCanvasId(), $section_id);
        $report = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(1, $report->Otro->downloads_count);
        $this->assertEquals(1, $report->Otro->resources_interactions[0]->members_downloads_count);
        $this->assertEquals(0, $report->Otro->resources_interactions[1]->members_downloads_count);
        $this->assertEquals(25, $report->Otro->resource_type_usage_percentage);
        $first_resource = $report->Otro->resources_interactions[0];
        $second_resource = $report->Otro->resources_interactions[1];
        $this->assertCount(2, $first_resource->members_visualizations);
        $this->assertEquals(100, $first_resource->resource_canvas_id);
        $this->assertEquals(50, $first_resource->members_visualizations_percentage);
        $this->assertCount(2, $second_resource->members_visualizations);
        $this->assertEquals(300, $second_resource->resource_canvas_id);
        $this->assertEquals(0, $second_resource->members_visualizations_percentage);
        $this->assertEquals(true, $first_resource->members_visualizations[0]->viewed);
        $this->assertEquals($now, $first_resource->members_visualizations[0]->first_view);
        $this->assertEquals($student_canvas_id, $first_resource->members_visualizations[0]->member_canvas_id);
        $this->assertEquals(100, $first_resource->members_visualizations[0]->resource_canvas_id);
        $this->assertEquals(false, $first_resource->members_visualizations[1]->viewed);
        $this->assertEquals(null, $first_resource->members_visualizations[1]->first_view);
        $this->assertEquals(3, $first_resource->members_visualizations[1]->member_canvas_id);
        $this->assertEquals(100, $first_resource->members_visualizations[1]->resource_canvas_id);
    }

    private function processReports(int $course_canvas_id) {
        (new devController())->rebuildReports($course_canvas_id);
    }
}
