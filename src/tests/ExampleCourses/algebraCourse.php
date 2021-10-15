<?php
namespace Tests\ExampleCourses;

use Tests\CourseBuilder;

trait algebraCourse {

    public function buildAlgebraCourse(){
        $course = new CourseBuilder(1, 'Algebra');

        $course->addSections([
            ['canvas_id' => 1000, 'name' => 'First Section', 'default_section' => 'true'],
        ]);

        $course->addUsers([
            ['canvas_id' => 1, "name" => 'César Mora'],
            ['canvas_id' => 2, "name" => 'Juan Valdez'],
            ['canvas_id' => 3, "name" => 'Doroti Strungerbarsen'],
            ['canvas_id' => 4, "name" => 'Oscar Alvarez'],
            ['canvas_id' => 5, "name" => 'Fernanda Aravena']
        ]);

        $course->addEnrollment([
            ['user_id' => CourseBuilder::idFromCanvasId(1), 'role' => 'TeacherEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(2), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(3), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(4), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
            ['user_id' => CourseBuilder::idFromCanvasId(5), 'role' => 'StudentEnrollment',
            'section_id' => CourseBuilder::idFromCanvasId(1000)],
        ]);

        $course->addModules([
            ['canvas_id' => 1, 'name' => 'First Module'],
            ['canvas_id' => 2, 'name' => 'Second Module'],
        ]);

        $module1 = CourseBuilder::idFromCanvasId(1);
        $module2 = CourseBuilder::idFromCanvasId(2);

        $course->addPages([
            ['canvas_id' => 100, 'title' => 'Page in first module',
            'module_item' => ['canvas_id'=> 1, 'module_id' => $module1, 'position' => 1]],
            ['canvas_id' => 200, 'title' => 'Page in second module',
            'module_item' => ['canvas_id'=> 2, 'module_id' => $module2, 'position' => 2]],
            ['canvas_id' => 300, 'title' => 'Page without module', 'workflow_state' =>'unpublished'],
        ]);

        $course->addQuizzes([
            ['canvas_id' => 1000, 'name' => 'Quiz without module', 'quiz_type' => 'assignment'],
            ['canvas_id' => 2000, 'name' => 'Quiz in second module', 'quiz_type' => 'assignment',
            'assignment' => ['canvas_id' => 1],
            'module_item' => ['canvas_id'=> 3, 'module_id' => $module2, 'position' => 2]]
        ]);

        $course->addFiles([
            ['canvas_id' => 1100, 'display_name' => 'file_in_first_module.pdf',
            'content_type' => 'application/pdf',
            'module_item' => ['canvas_id' => 4, 'module_id' => $module1,'position' => 20]],
            ['canvas_id' => 1200, 'display_name' => 'file_in_second_module.pdf',
            'content_type' => 'application/pdf',
            'module_item' => ['canvas_id' => 5, 'module_id' => $module2, 'position' => 20]],
        ]);

        $course->addAssignments([
            ['canvas_id' => 110, 'title' => 'Assignment in first module',
            'submission_types' => 'online_upload', 'position' => 200,
            'module_item' => ['canvas_id' => 6, 'module_id' => $module1, 'position' => 200]],
            ['canvas_id' => 120, 'title' => 'Assignment without module',
            'submission_types' => 'online_upload', 'position' => 200],
        ]);

        $course->addUrls([
            ['canvas_id' => 8000, 'title' => 'Url in first module', 'module_id' => $module1,
            'position' => 60, 'url' => 'http://www.google.cl'],
        ]);

        $course->addExternalTools([
            ['canvas_id' => 9000, 'title' => 'External Tool in second module', 'module_id' => $module2,
            'position' => 61, 'url' => 'http://www.launchLTI.cl'],
        ]);

        $course->addDiscussionTopics([
            ['canvas_id' => 10000, 'title' => 'Discussion topic in first module',
            'module_item' => ['canvas_id'=> 7, 'module_id' => $module1, 'position' => 5]],
            ['canvas_id' => 11000, 'title' => 'Discussion topic without module']
        ]);

        return $course;
    }
}
