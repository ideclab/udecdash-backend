<?php

namespace Tests\Feature;

use App\Http\Controllers\CourseController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\CourseBuilder;
use Tests\TestCase;

class MembershipTest extends TestCase
{
    public function test_get_only_active_sections(){
         // Prepare
         $course_canvas_id = 1;
         $course = new CourseBuilder($course_canvas_id, 'Course One');
         $course->addSections([
             ['canvas_id' => 1000, 'name' => 'First Section', 'default_section' => 'true'],
             ['canvas_id' => 2000, 'name' => 'Second Section', 'workflow_state' => 'deleted'],
         ]);
         $course->addUsers([
             ['canvas_id' => 1, "name" => 'User One'],
             ['canvas_id' => 2, "name" => 'User Two'],
             ['canvas_id' => 3, "name" => 'User Three'],
             ['canvas_id' => 4, "name" => 'User Four'],
         ]);
         $course->addEnrollment([
             ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
             'section_id' => CourseBuilder::idFromCanvasId(1000)],
             ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
             'section_id' => CourseBuilder::idFromCanvasId(1000)],
             ['user_id' => CourseBuilder::idFromCanvasId(3), 'role' => 'TeacherEnrollment',
             'section_id' => CourseBuilder::idFromCanvasId(2000)],
             ['user_id' => CourseBuilder::idFromCanvasId(4), 'role' => 'StudentEnrollment',
             'section_id' => CourseBuilder::idFromCanvasId(2000)],
         ]);
         // Execute
         $response = (new CourseController)->getInformation($course->getCourseCanvasId());
         $response = json_decode($response->getContent());
         // Assertions
         $this->assertIsArray($response->sections);
         $this->assertCount(1, $response->sections);
         $this->assertEquals($response->sections[0]->canvas_id, 1000);
    }

    public function test_not_return_sections_without_students (){
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addSections([
            ['canvas_id' => 1000, 'name' => 'First Section', 'default_section' => 'true'],
            ['canvas_id' => 2000, 'name' => 'Second Section'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'User One'],
            ['canvas_id' => 2, "name" => 'User Two'],
            ['canvas_id' => 3, "name" => 'User Three'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(2000)],
            ['user_id' => CourseBuilder::idFromCanvasId(3), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(2000)],
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        // Assertions
        $this->assertIsArray($response->sections);
        $this->assertCount(1, $response->sections);
        $this->assertEquals($response->sections[0]->canvas_id, 2000);
    }

    public function test_return_array_for_course_without_valid_sections (){
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addSections([
            ['canvas_id' => 1000, 'name' => 'First Section', 'default_section' => 'true'],
            ['canvas_id' => 2000, 'name' => 'Second Section'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'User One'],
            ['canvas_id' => 2, "name" => 'User Two'],
            ['canvas_id' => 3, "name" => 'User Three'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)]
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        // Assertions
        $this->assertIsArray($response->sections);
        $this->assertCount(0, $response->sections);
    }

    public function test_return_array_for_course_without_sections (){
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        // Assertions
        $this->assertIsArray($response->sections);
        $this->assertCount(0, $response->sections);
    }

    public function test_get_enrolled_teachers(){
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addSections([
            ['canvas_id' => 1000, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'User One'],
            ['canvas_id' => 2, "name" => 'User Two'],
            ['canvas_id' => 3, "name" => 'User Three'],
            ['canvas_id' => 4, "name" => 'User Four'],
            ['canvas_id' => 5, "name" => 'User Five'],
            ['canvas_id' => 6, "name" => 'User Six'],
            ['canvas_id' => 7, "name" => 'User Seven'],
        ]);

        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentViewEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(3), 'role' => 'TaEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(4), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000), 'workflow_state' => 'deleted'],
            ['user_id' => CourseBuilder::idFromCanvasId(5), 'role' => 'DesignerEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(6), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(7), 'role' => 'ObserverEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        $members = collect($response->sections[0]->members);
        $enrollments = $members->groupBy('role_type');
        // Assertions
        $this->assertIsArray($response->sections);
        $this->assertIsArray($response->sections[0]->members);
        $this->assertCount(1, $enrollments['TeacherEnrollment']);
        $teacher = $enrollments['TeacherEnrollment'][0];
        $this->assertEquals($teacher->id, CourseBuilder::idFromCanvasId(1));
    }

    public function test_get_teachers_if_his_account_is_delete_but_keeping_the_enrollment(){
        // Prepare
        $course_canvas_id = 2;
        $course = new CourseBuilder($course_canvas_id, 'Course Two');
        $course->addSections([
            ['canvas_id' => 2000, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 200, "name" => 'User One', 'workflow_state' => 'deleted'],
            ['canvas_id' => 300, "name" => 'User Two'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(200), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(2000)],
            ['user_id' => CourseBuilder::idFromCanvasId(300), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(2000)],
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        $members = collect($response->sections[0]->members);
        $enrollments = $members->groupBy('role_type');
        // Assertions
        $this->assertIsArray($response->sections);
        $this->assertIsArray($response->sections[0]->members);
        $this->assertCount(1, $enrollments['TeacherEnrollment']);
        $teacher = $enrollments['TeacherEnrollment'][0];
        $this->assertEquals($teacher->id, CourseBuilder::idFromCanvasId(200));
    }

    public function test_get_enrolled_students()
    {
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addSections([
            ['canvas_id' => 1000, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'User One'],
            ['canvas_id' => 2, "name" => 'User Two'],
            ['canvas_id' => 3, "name" => 'User Three'],
            ['canvas_id' => 4, "name" => 'User Four'],
            ['canvas_id' => 5, "name" => 'User Five'],
            ['canvas_id' => 6, "name" => 'User Six'],
            ['canvas_id' => 7, "name" => 'User Seven'],
            ['canvas_id' => 8, "name" => 'User Eight'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentViewEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(3), 'role' => 'TaEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(4), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000), 'workflow_state' => 'deleted'],
            ['user_id' => CourseBuilder::idFromCanvasId(5), 'role' => 'DesignerEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(6), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(7), 'role' => 'ObserverEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(8), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        $members = collect($response->sections[0]->members);
        $enrollments = $members->groupBy('role_type');
        $students = $enrollments['StudentEnrollment'];
        // Assertions
        $this->assertIsArray($response->sections);
        $this->assertIsArray($response->sections[0]->members);
        $this->assertCount(2, $enrollments['StudentEnrollment']);
        $this->assertEquals($students[0]->id, CourseBuilder::idFromCanvasId(6));
        $this->assertEquals($students[1]->id, CourseBuilder::idFromCanvasId(8));
    }

    public function test_correct_separation_of_students_by_sections(){
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addSections([
            ['canvas_id' => 1000, 'name' => 'First Section', 'default_section' => 'true'],
            ['canvas_id' => 2000, 'name' => 'Second Section'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'User One'],
            ['canvas_id' => 2, "name" => 'User Two'],
            ['canvas_id' => 3, "name" => 'User Three'],
            ['canvas_id' => 4, "name" => 'User Four'],
            ['canvas_id' => 5, "name" => 'User Five'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(3), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(2000)],
            ['user_id' => CourseBuilder::idFromCanvasId(4), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(2000)],
            ['user_id' => CourseBuilder::idFromCanvasId(5), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(2000)],
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        $first_section = collect($response->sections[0]->members)->groupBy('role_type');
        $second_section = collect($response->sections[1]->members)->groupBy('role_type');
        // Assertions
        $this->assertIsArray($response->sections);
        $this->assertCount(1, $first_section['StudentEnrollment']);
        $this->assertCount(2, $second_section['StudentEnrollment']);
        $this->assertEquals($first_section['StudentEnrollment'][0]->id,
        CourseBuilder::idFromCanvasId(2));
        $this->assertEquals($second_section['StudentEnrollment'][0]->id,
        CourseBuilder::idFromCanvasId(4));
        $this->assertEquals($second_section['StudentEnrollment'][1]->id,
        CourseBuilder::idFromCanvasId(5));
    }

    public function test_dont_merge_data_between_different_courses(){
         // Prepare
         $first_course = new CourseBuilder(1, 'Course One');
         $second_course = new CourseBuilder(2, 'Course One');
         $first_course->addSections([
             ['canvas_id' => 1000, 'name' => 'First Section first course', 'default_section' => 'true'],
         ]);
         $second_course->addSections([
            ['canvas_id' => 2000, 'name' => 'First Section second course', 'default_section' => 'true'],
        ]);
         $first_course->addUsers([
             ['canvas_id' => 1, "name" => 'User One'],
             ['canvas_id' => 2, "name" => 'User Two'],
         ]);
         $first_course->addEnrollment([
             ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
             'section_id' => CourseBuilder::idFromCanvasId(1000)],
             ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
             'section_id' => CourseBuilder::idFromCanvasId(1000)],
         ]);
         $second_course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(2000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(2000)],
        ]);
         // Execute
         $response = (new CourseController)->getInformation($second_course->getCourseCanvasId());
         $response = json_decode($response->getContent());
         $members = collect($response->sections[0]->members)->groupBy('role_type');
         // Assertions
         $this->assertIsArray($response->sections);
         $this->assertCount(1, $members['StudentEnrollment']);
         $this->assertCount(1, $members['TeacherEnrollment']);
         $this->assertEquals(CourseBuilder::idFromCanvasId(1),
         $members['StudentEnrollment'][0]->id);
         $this->assertEquals(CourseBuilder::idFromCanvasId(2),
         $members['TeacherEnrollment'][0]->id);
    }

    public function test_show_user_in_all_section_when_are_enrolled_in_more_of_one(){
         // Prepare
         $course_canvas_id = 1;
         $course = new CourseBuilder($course_canvas_id, 'Course One');
         $course->addSections([
             ['canvas_id' => 1000, 'name' => 'First Section', 'default_section' => 'true'],
             ['canvas_id' => 2000, 'name' => 'Second Section'],
         ]);
         $course->addUsers([
             ['canvas_id' => 1, "name" => 'User One'],
             ['canvas_id' => 2, "name" => 'User Two']
         ]);
         $course->addEnrollment([
             ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
             'section_id' => CourseBuilder::idFromCanvasId(1000)],
             ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
             'section_id' => CourseBuilder::idFromCanvasId(1000)],
             ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
             'section_id' => CourseBuilder::idFromCanvasId(2000)],
         ]);
         // Execute
         $response = (new CourseController)->getInformation($course->getCourseCanvasId());
         $response = json_decode($response->getContent());
         $first_section = collect($response->sections[0]->members)->groupBy('role_type');
         $second_section = collect($response->sections[1]->members)->groupBy('role_type');
         // Assertions
         $this->assertIsArray($response->sections);
         $this->assertCount(1, $first_section['TeacherEnrollment']);
         $this->assertCount(1, $first_section['StudentEnrollment']);
         $this->assertCount(1, $second_section['StudentEnrollment']);
         $this->assertEquals($first_section['StudentEnrollment'][0]->id,
         CourseBuilder::idFromCanvasId(2));
         $this->assertEquals($second_section['StudentEnrollment'][0]->id,
         CourseBuilder::idFromCanvasId(2));
    }

    public function test_return_section_with_students_even_dont_has_teacher(){
        // Prepare
        $course_canvas_id = 1;
        $course = new CourseBuilder($course_canvas_id, 'Course One');
        $course->addSections([
            ['canvas_id' => 1000, 'name' => 'First Section', 'default_section' => 'true'],
        ]);
        $course->addUsers([
            ['canvas_id' => 1, "name" => 'User One'],
        ]);
        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
        ]);
        // Execute
        $response = (new CourseController)->getInformation($course->getCourseCanvasId());
        $response = json_decode($response->getContent());
        $section = collect($response->sections[0]->members)->groupBy('role_type');
        // Assertions
        $this->assertIsArray($response->sections);
        $this->assertCount(1, $section['StudentEnrollment']);
        $this->assertEquals($section['StudentEnrollment'][0]->id,
        CourseBuilder::idFromCanvasId(1));
   }
}
