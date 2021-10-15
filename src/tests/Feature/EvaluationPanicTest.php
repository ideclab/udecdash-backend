<?php

namespace Tests\Feature;

use App\Classes\DataStructure\Reports\EvaluationPanic\Activity;
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

class EvaluationPanicTest extends TestCase
{
    use algebraCourse;

    public function test_get_only_quizzes_with_start_and_finish_date(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $course->addQuizzes([
            ['canvas_id' => 99651, 'name' => 'Some quiz 1', 'quiz_type' => 'assignment'],
            ['canvas_id' => 99652, 'name' => 'Some quiz 2', 'quiz_type' => 'assignment',
            'assignment' => ['canvas_id' => 998877], 'unlock_at' => '2020-12-12 22:00:00',
            'lock_at'=>"2020-12-12 22:30:00"],
            ['canvas_id' => 99653, 'name' => 'Some quiz 3', 'quiz_type' => 'assignment',
            'assignment' => ['canvas_id' => 9988778], 'unlock_at' => '2020-12-12 20:00:00'],
            ['canvas_id' => 99654, 'name' => 'Some quiz 4', 'quiz_type' => 'assignment',
            'lock_at'=>"2020-12-12 22:00:00"],
            ['canvas_id' => 99688, 'name' => 'Some quiz 5', 'quiz_type' => 'assignment',
            'unlock_at' => '2020-12-12 22:00:00', 'lock_at'=>"2020-12-12 22:30:00"],
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->evaluationPanic($course->getCourseCanvasId(),1000);
        $result = json_decode($report->getContent());
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $dates_count = (Activity::LIMIT_DAYS + 1);
        $this->assertCount($dates_count, (array) $result[0]->viewed_before);
        $this->assertCount($dates_count, (array) $result[0]->viewed_after);
        $this->assertCount($dates_count, (array) $result[1]->viewed_before);
        $this->assertCount($dates_count, (array) $result[1]->viewed_after);
        $this->assertEquals(99652, $result[0]->quiz->canvas_id);
        $this->assertEquals(99688, $result[1]->quiz->canvas_id);
    }

    public function test_identify_evaluated_quizzes(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $course->addQuizzes([
            ['canvas_id' => 99651, 'name' => 'Some quiz 1', 'quiz_type' => 'assignment'],
            ['canvas_id' => 99652, 'name' => 'Some quiz 2', 'quiz_type' => 'assignment',
            'assignment' => ['canvas_id' => 998877], 'unlock_at' => '2020-12-12 22:00:00',
            'lock_at'=>"2020-12-12 22:30:00"],
            ['canvas_id' => 99653, 'name' => 'Some quiz 3', 'quiz_type' => 'assignment',
            'assignment' => ['canvas_id' => 9988778], 'unlock_at' => '2020-12-12 20:00:00'],
            ['canvas_id' => 99654, 'name' => 'Some quiz 4', 'quiz_type' => 'assignment',
            'lock_at'=>"2020-12-12 22:00:00"],
            ['canvas_id' => 99688, 'name' => 'Some quiz 5', 'quiz_type' => 'assignment',
            'unlock_at' => '2020-12-12 22:00:00', 'lock_at'=>"2020-12-12 22:30:00"],
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->evaluationPanic($course->getCourseCanvasId(),1000);
        $result = json_decode($report->getContent());
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(true, $result[0]->quiz->is_evaluated);
        $this->assertEquals(false, $result[1]->quiz->is_evaluated);
    }

    public function test_before_and_after_days_for_put_activity(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $course->addQuizzes([
            // First quiz Verano UTC-3
            ['canvas_id' => 99688, 'name' => 'Some quiz 1', 'quiz_type' => 'assignment',
            'unlock_at' => '2020-12-12 00:00:00', 'lock_at'=>"2020-12-12 23:59:59"],
            // Second quiz VERANO UTC-3
            ['canvas_id' => 99689, 'name' => 'Some quiz 2', 'quiz_type' => 'assignment',
            'unlock_at' => '2020-12-31 00:00:00', 'lock_at'=>"2021-01-01 00:00:00"],
            // Third quiz INVIERNO UTC-4
            ['canvas_id' => 94689, 'name' => 'Some quiz 2', 'quiz_type' => 'assignment',
            'unlock_at' => '2021-05-31 20:00:00', 'lock_at'=>"2021-06-01 22:00:00"],
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->evaluationPanic($course->getCourseCanvasId(),1000);
        $result = json_decode($report->getContent());
        // First Quiz
        $this->assertCount(3, $result);
        $this->assertEquals("2020-12-09 21:00:00", $result[0]->before_start);
        $this->assertEquals("2020-12-11 21:00:00", $result[0]->before_end);
        $this->assertEquals("2020-12-12 20:59:59", $result[0]->after_start);
        $this->assertEquals("2020-12-14 20:59:59", $result[0]->after_end);
        $this->assertObjectHasAttribute("2020-12-09", $result[0]->viewed_before);
        $this->assertObjectHasAttribute("2020-12-10", $result[0]->viewed_before);
        $this->assertObjectHasAttribute("2020-12-11", $result[0]->viewed_before);
        $this->assertObjectHasAttribute("2020-12-12", $result[0]->viewed_after);
        $this->assertObjectHasAttribute("2020-12-13", $result[0]->viewed_after);
        $this->assertObjectHasAttribute("2020-12-14", $result[0]->viewed_after);
        // Second Quiz
        $this->assertEquals("2020-12-28 21:00:00", $result[1]->before_start);
        $this->assertEquals("2020-12-30 21:00:00", $result[1]->before_end);
        $this->assertEquals("2020-12-31 21:00:00", $result[1]->after_start);
        $this->assertEquals("2021-01-02 21:00:00", $result[1]->after_end);
        $this->assertObjectHasAttribute("2020-12-28", $result[1]->viewed_before);
        $this->assertObjectHasAttribute("2020-12-29", $result[1]->viewed_before);
        $this->assertObjectHasAttribute("2020-12-30", $result[1]->viewed_before);
        $this->assertObjectHasAttribute("2020-12-31", $result[1]->viewed_after);
        $this->assertObjectHasAttribute("2021-01-01", $result[1]->viewed_after);
        $this->assertObjectHasAttribute("2021-01-02", $result[1]->viewed_after);
        // Third Quiz
        $this->assertEquals("2021-05-29 16:00:00", $result[2]->before_start);
        $this->assertEquals("2021-05-31 16:00:00", $result[2]->before_end);
        $this->assertEquals("2021-06-01 18:00:00", $result[2]->after_start);
        $this->assertEquals("2021-06-03 18:00:00", $result[2]->after_end);
        $this->assertObjectHasAttribute("2021-05-29", $result[2]->viewed_before);
        $this->assertObjectHasAttribute("2021-05-30", $result[2]->viewed_before);
        $this->assertObjectHasAttribute("2021-05-31", $result[2]->viewed_before);
        $this->assertObjectHasAttribute("2021-06-01", $result[2]->viewed_after);
        $this->assertObjectHasAttribute("2021-06-02", $result[2]->viewed_after);
        $this->assertObjectHasAttribute("2021-06-03", $result[2]->viewed_after);
    }

    public function test_put_interactions_before(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $course->addQuizzes([
            ['canvas_id' => 99688, 'name' => 'Some quiz 5', 'quiz_type' => 'assignment',
            'unlock_at' => '2021-05-18 15:00:00', 'lock_at'=>'2021-05-18 16:00:00'], // 11 a 12 CL
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => "2021-05-16 10:59:59",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => "2021-05-16 11:00:00",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => "2021-05-17 11:00:00",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => "2021-05-18 11:00:00",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => "2021-05-18 11:00:01",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->evaluationPanic($course->getCourseCanvasId(),1000);
        $result = json_decode($report->getContent());
        $before_days = (array) $result[0]->viewed_before;
        foreach($before_days as $day){
            $first_resource = $day[0];
            $this->assertEquals(300, $first_resource->resource_canvas_id);
            $this->assertEquals(1, $first_resource->distinct_members_count);
            $this->assertEquals(25, $first_resource->members_visualization_percentage);
            $this->assertEquals(1, $first_resource->all_visualizations_count);
            foreach($first_resource->members_interactions as $interaction){
                if($interaction->member_canvas_id == 4){
                    $this->assertEquals(1, $interaction->count_views);
                }else{
                    $this->assertEquals(0, $interaction->count_views);
                }
                $this->assertEquals(300, $interaction->resource_canvas_id);
            }
        }
    }

    public function test_put_interactions_after(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $course->addQuizzes([
            ['canvas_id' => 99688, 'name' => 'Some quiz 5', 'quiz_type' => 'assignment',
            'unlock_at' => '2021-04-18 15:00:00', 'lock_at'=>'2021-04-18 16:00:00'], // 11 a 12 CL
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => "2021-04-18 11:59:59",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => "2021-04-18 12:00:00",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => "2021-04-19 11:00:00",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => "2021-04-20 12:00:00",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => "2021-04-20 12:00:01",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->evaluationPanic($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        $before_days = (array) $result[0]->viewed_after;
        foreach($before_days as $day){
            $first_resource = $day[0];
            $this->assertEquals(300, $first_resource->resource_canvas_id);
            $this->assertEquals(1, $first_resource->distinct_members_count);
            $this->assertEquals(25, $first_resource->members_visualization_percentage);
            $this->assertEquals(1, $first_resource->all_visualizations_count);
            foreach($first_resource->members_interactions as $interaction){
                if($interaction->member_canvas_id == 4){
                    $this->assertEquals(1, $interaction->count_views);
                }else{
                    $this->assertEquals(0, $interaction->count_views);
                }
                $this->assertEquals(300, $interaction->resource_canvas_id);
            }
        }
    }

    public function test_check_multiple_interactions_before_for_the_same_resource(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $course->addQuizzes([
            ['canvas_id' => 99688, 'name' => 'Some quiz 5', 'quiz_type' => 'assignment',
            'unlock_at' => '2021-05-18 15:00:00', 'lock_at'=>'2021-05-18 16:00:00'], // 11 a 12 CL
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => "2021-05-16 11:00:00",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => "2021-05-16 11:00:01",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(3),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => "2021-05-16 11:00:00",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->evaluationPanic($course->getCourseCanvasId(),1000);
        $result = json_decode($report->getContent());
        $resource = $result[0]->viewed_before->{'2021-05-16'}[0];
        $this->assertEquals(300, $resource->resource_canvas_id);
        $this->assertEquals(2, $resource->distinct_members_count);
        $this->assertEquals(3, $resource->all_visualizations_count);
        $this->assertCount(4, $resource->members_interactions);
        foreach($resource->members_interactions as $member){
            if($member->member_canvas_id == 3){
                $this->assertEquals(1, $member->count_views);
            }else if($member->member_canvas_id == 4){
                $this->assertEquals(2, $member->count_views);
            }else{
                $this->assertEquals(0, $member->count_views);
            }
            $this->assertEquals(300, $member->resource_canvas_id);
        }
    }

    public function test_check_multiple_interactions_after_for_the_same_resource(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $course->addQuizzes([
            ['canvas_id' => 99688, 'name' => 'Some quiz 5', 'quiz_type' => 'assignment',
            'unlock_at' => '2021-04-18 15:00:00', 'lock_at'=>'2021-04-18 16:00:00'], // 11 a 12 CL
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => "2021-04-18 19:00:00",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => "2021-04-18 18:00:00",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(3),
            'item_id' => CourseBuilder::idFromCanvasId(300),
            'item_canvas_id' => 300,
            'viewed' => "2021-04-18 18:00:00",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->evaluationPanic($course->getCourseCanvasId(),1000);
        $result = json_decode($report->getContent());
        $resource = $result[0]->viewed_after->{'2021-04-18'}[0];
        $this->assertEquals(300, $resource->resource_canvas_id);
        $this->assertEquals(50, $resource->members_visualization_percentage);
        $this->assertEquals(2, $resource->distinct_members_count);
        $this->assertEquals(3, $resource->all_visualizations_count);
        $this->assertCount(4, $resource->members_interactions);
        foreach($resource->members_interactions as $member){
            if($member->member_canvas_id == 3){
                $this->assertEquals(1, $member->count_views);
            }else if($member->member_canvas_id == 4){
                $this->assertEquals(2, $member->count_views);
            }else{
                $this->assertEquals(0, $member->count_views);
            }
            $this->assertEquals(300, $member->resource_canvas_id);
        }
    }

    public function test_get_wiki_pages_interactions(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $course->addQuizzes([
            ['canvas_id' => 99688, 'name' => 'Some quiz 5', 'quiz_type' => 'assignment',
            'unlock_at' => '2021-04-18 15:00:00', 'lock_at'=>'2021-04-18 16:00:00'], // 11 a 12 CL
        ]);
        $resource_canvas_id = 300;
        WikiPageInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId($resource_canvas_id),
            'item_canvas_id' => $resource_canvas_id,
            'viewed' => "2021-04-18 19:00:00",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->evaluationPanic($course->getCourseCanvasId(),1000);
        $result = json_decode($report->getContent());
        $resource = $result[0]->viewed_after->{'2021-04-18'}[0];
        // Assertions
        $this->assertEquals($resource_canvas_id, $resource->resource_canvas_id);
        $this->assertEquals(25, $resource->members_visualization_percentage);
        $this->assertEquals(1, $resource->distinct_members_count);
        $this->assertEquals(1, $resource->all_visualizations_count);
        $this->assertCount(4, $resource->members_interactions);
        foreach($resource->members_interactions as $member){
            if($member->member_canvas_id == 4){
                $this->assertEquals(1, $member->count_views);
            }else{
                $this->assertEquals(0, $member->count_views);
            }
            $this->assertEquals($resource_canvas_id, $member->resource_canvas_id);
        }
    }

    public function test_get_quiz_interactions(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $course->addQuizzes([
            ['canvas_id' => 99688, 'name' => 'Some quiz 5', 'quiz_type' => 'assignment',
            'unlock_at' => '2021-04-18 15:00:00', 'lock_at'=>'2021-04-18 16:00:00'], // 11 a 12 CL
        ]);
        $resource_canvas_id = 1000;
        QuizInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId($resource_canvas_id),
            'item_canvas_id' => $resource_canvas_id,
            'viewed' => "2021-04-18 19:00:00",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->evaluationPanic($course->getCourseCanvasId(),1000);
        $result = json_decode($report->getContent());
        $resource = $result[0]->viewed_after->{'2021-04-18'}[0];
        // Assertions
        $this->assertEquals($resource_canvas_id, $resource->resource_canvas_id);
        $this->assertEquals(25, $resource->members_visualization_percentage);
        $this->assertEquals(1, $resource->distinct_members_count);
        $this->assertEquals(1, $resource->all_visualizations_count);
        $this->assertCount(4, $resource->members_interactions);
        foreach($resource->members_interactions as $member){
            if($member->member_canvas_id == 4){
                $this->assertEquals(1, $member->count_views);
            }else{
                $this->assertEquals(0, $member->count_views);
            }
            $this->assertEquals($resource_canvas_id, $member->resource_canvas_id);
        }
    }

    public function test_get_files_interactions(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $course->addQuizzes([
            ['canvas_id' => 99688, 'name' => 'Some quiz 5', 'quiz_type' => 'assignment',
            'unlock_at' => '2021-04-18 15:00:00', 'lock_at'=>'2021-04-18 16:00:00'], // 11 a 12 CL
        ]);
        $resource_canvas_id = 1100;
        AttachmentInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId($resource_canvas_id),
            'item_canvas_id' => $resource_canvas_id,
            'viewed' => "2021-04-18 19:00:00",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->evaluationPanic($course->getCourseCanvasId(),1000);
        $result = json_decode($report->getContent());
        $resource = $result[0]->viewed_after->{'2021-04-18'}[0];
        // Assertions
        $this->assertEquals($resource_canvas_id, $resource->resource_canvas_id);
        $this->assertEquals(25, $resource->members_visualization_percentage);
        $this->assertEquals(1, $resource->distinct_members_count);
        $this->assertEquals(1, $resource->all_visualizations_count);
        $this->assertCount(4, $resource->members_interactions);
        foreach($resource->members_interactions as $member){
            if($member->member_canvas_id == 4){
                $this->assertEquals(1, $member->count_views);
            }else{
                $this->assertEquals(0, $member->count_views);
            }
            $this->assertEquals($resource_canvas_id, $member->resource_canvas_id);
        }
    }

    public function test_get_discussion_topics_interactions(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $course->addQuizzes([
            ['canvas_id' => 99688, 'name' => 'Some quiz 5', 'quiz_type' => 'assignment',
            'unlock_at' => '2021-04-18 15:00:00', 'lock_at'=>'2021-04-18 16:00:00'], // 11 a 12 CL
        ]);
        $resource_canvas_id = 10000;
        DiscussionTopicInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId($resource_canvas_id),
            'item_canvas_id' => $resource_canvas_id,
            'viewed' => "2021-04-18 19:00:00",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->evaluationPanic($course->getCourseCanvasId(),1000);
        $result = json_decode($report->getContent());
        $resource = $result[0]->viewed_after->{'2021-04-18'}[0];
        // Assertions
        $this->assertEquals($resource_canvas_id, $resource->resource_canvas_id);
        $this->assertEquals(25, $resource->members_visualization_percentage);
        $this->assertEquals(1, $resource->distinct_members_count);
        $this->assertEquals(1, $resource->all_visualizations_count);
        $this->assertCount(4, $resource->members_interactions);
        foreach($resource->members_interactions as $member){
            if($member->member_canvas_id == 4){
                $this->assertEquals(1, $member->count_views);
            }else{
                $this->assertEquals(0, $member->count_views);
            }
            $this->assertEquals($resource_canvas_id, $member->resource_canvas_id);
        }
    }

    public function test_get_assignment_interactions(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $course->addQuizzes([
            ['canvas_id' => 99688, 'name' => 'Some quiz 5', 'quiz_type' => 'assignment',
            'unlock_at' => '2021-04-18 15:00:00', 'lock_at'=>'2021-04-18 16:00:00'], // 11 a 12 CL
        ]);
        $resource_canvas_id = 110;
        AssignmentInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId($resource_canvas_id),
            'item_canvas_id' => $resource_canvas_id,
            'viewed' => "2021-04-18 19:00:00",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->evaluationPanic($course->getCourseCanvasId(),1000);
        $result = json_decode($report->getContent());
        $resource = $result[0]->viewed_after->{'2021-04-18'}[0];
        // Assertions
        $this->assertEquals($resource_canvas_id, $resource->resource_canvas_id);
        $this->assertEquals(25, $resource->members_visualization_percentage);
        $this->assertEquals(1, $resource->distinct_members_count);
        $this->assertEquals(1, $resource->all_visualizations_count);
        $this->assertCount(4, $resource->members_interactions);
        foreach($resource->members_interactions as $member){
            if($member->member_canvas_id == 4){
                $this->assertEquals(1, $member->count_views);
            }else{
                $this->assertEquals(0, $member->count_views);
            }
            $this->assertEquals($resource_canvas_id, $member->resource_canvas_id);
        }
    }

    public function test_get_external_url_interactions(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $course->addQuizzes([
            ['canvas_id' => 99688, 'name' => 'Some quiz 5', 'quiz_type' => 'assignment',
            'unlock_at' => '2021-04-18 15:00:00', 'lock_at'=>'2021-04-18 16:00:00'], // 11 a 12 CL
        ]);
        $resource_canvas_id = 8000;
        ExternalUrlInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId($resource_canvas_id),
            'item_canvas_id' => $resource_canvas_id,
            'viewed' => "2021-04-18 19:00:00",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->evaluationPanic($course->getCourseCanvasId(),1000);
        $result = json_decode($report->getContent());
        $resource = $result[0]->viewed_after->{'2021-04-18'}[0];
        // Assertions
        $this->assertEquals($resource_canvas_id, $resource->resource_canvas_id);
        $this->assertEquals(25, $resource->members_visualization_percentage);
        $this->assertEquals(1, $resource->distinct_members_count);
        $this->assertEquals(1, $resource->all_visualizations_count);
        $this->assertCount(4, $resource->members_interactions);
        foreach($resource->members_interactions as $member){
            if($member->member_canvas_id == 4){
                $this->assertEquals(1, $member->count_views);
            }else{
                $this->assertEquals(0, $member->count_views);
            }
            $this->assertEquals($resource_canvas_id, $member->resource_canvas_id);
        }
    }

    public function test_get_external_tool_interactions(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $course->addQuizzes([
            ['canvas_id' => 99688, 'name' => 'Some quiz 5', 'quiz_type' => 'assignment',
            'unlock_at' => '2021-04-18 15:00:00', 'lock_at'=>'2021-04-18 16:00:00'], // 11 a 12 CL
        ]);
        $resource_canvas_id = 9000;
        ContextExternalToolInteraction::create([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'item_id' => CourseBuilder::idFromCanvasId($resource_canvas_id),
            'item_canvas_id' => $resource_canvas_id,
            'viewed' => "2021-04-18 19:00:00",
            'url' => '/whatever/download',
            'device' => 'DESKTOP'
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->evaluationPanic($course->getCourseCanvasId(),1000);
        $result = json_decode($report->getContent());
        $resource = $result[0]->viewed_after->{'2021-04-18'}[0];
        // Assertions
        $this->assertEquals($resource_canvas_id, $resource->resource_canvas_id);
        $this->assertEquals(25, $resource->members_visualization_percentage);
        $this->assertEquals(1, $resource->distinct_members_count);
        $this->assertEquals(1, $resource->all_visualizations_count);
        $this->assertCount(4, $resource->members_interactions);
        foreach($resource->members_interactions as $member){
            if($member->member_canvas_id == 4){
                $this->assertEquals(1, $member->count_views);
            }else{
                $this->assertEquals(0, $member->count_views);
            }
            $this->assertEquals($resource_canvas_id, $member->resource_canvas_id);
        }
    }

    private function processReports(int $course_canvas_id) {
        (new devController())->rebuildReports($course_canvas_id);
    }
}
