<?php

namespace Tests\Feature;

use App\Http\Controllers\devController;
use App\Http\Controllers\ReportsController;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\CourseBuilder;
use Tests\ExampleCourses\algebraCourse;
use Tests\TestCase;

class CourseCommunicationTest extends TestCase
{
    use algebraCourse;

    public function test_course_without_communication(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $now = Carbon::now()->format('Y-m-d H:m:s');
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseCommunication($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        $this->assertEquals(0, $result->mail_messages_count);
        $this->assertEquals(0, $result->mail_messages_percentage);
        $this->assertEquals(0, $result->discussion_entry_count);
        $this->assertEquals(0, $result->discussion_entry_percentage);
        $this->assertCount(4, $result->mail_messages_list);
        $this->assertCount(4, $result->discussion_entry_list);
        foreach($result->mail_messages_list as $member) {
            $this->assertEquals(0, $member->creation_count);
        }
        foreach($result->discussion_entry_list as $member) {
            $this->assertEquals(0, $member->creation_count);
        }
    }

    public function test_course_with_only_mails(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $yesterday = Carbon::now()->subDays(1)->format('Y-m-d H:m:s');
        $now = Carbon::now()->format('Y-m-d H:m:s');
        $author_id = CourseBuilder::idFromCanvasId(2);
        $first_recipient = CourseBuilder::idFromCanvasId(3);
        $second_recipient = CourseBuilder::idFromCanvasId(4);
        $course->addMailMessages([
            ['canvas_id' => 100, 'from' => $author_id, 'to' => [$first_recipient, $second_recipient],
            'subject' => 'whatever', 'replies' => [
                    ['canvas_id' => 20, 'author_id'=> $author_id, 'body' => 'xd', 'created_at' => $now],
                    ['canvas_id' => 30, 'author_id'=> $first_recipient, 'body' => 'alv',
                    'created_at' => $now],
                    ['canvas_id' => 40, 'author_id'=> $first_recipient, 'body' => 'chau',
                    'created_at' => $now]
                ]
            ]
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseCommunication($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        $this->assertEquals(3, $result->mail_messages_count);
        $this->assertEquals(100, $result->mail_messages_percentage);
        $this->assertEquals(0, $result->discussion_entry_count);
        $this->assertEquals(0, $result->discussion_entry_percentage);
        $this->assertCount(4, $result->mail_messages_list);
        $this->assertCount(4, $result->discussion_entry_list);
        $this->assertEquals(2, $result->mail_messages_list[0]->member_canvas_id);
        $this->assertEquals(1, $result->mail_messages_list[0]->creation_count);
        $this->assertEquals(3, $result->mail_messages_list[1]->member_canvas_id);
        $this->assertEquals(2, $result->mail_messages_list[1]->creation_count);
        $this->assertEquals(4, $result->mail_messages_list[2]->member_canvas_id);
        $this->assertEquals(0, $result->mail_messages_list[2]->creation_count);
        $this->assertEquals(5, $result->mail_messages_list[3]->member_canvas_id);
        $this->assertEquals(0, $result->mail_messages_list[3]->creation_count);
        foreach($result->discussion_entry_list as $member) {
            $this->assertEquals(0, $member->creation_count);
        }
    }

    public function test_cumulative_mails(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $yesterday = Carbon::now()->subDays(1)->format('Y-m-d H:m:s');
        $now = Carbon::now()->format('Y-m-d H:m:s');
        $author_id = CourseBuilder::idFromCanvasId(2);
        $first_recipient = CourseBuilder::idFromCanvasId(3);
        $second_recipient = CourseBuilder::idFromCanvasId(4);
        $course->addMailMessages([
            ['canvas_id' => 100, 'from' => $author_id, 'to' => [$first_recipient, $second_recipient],
            'subject' => 'whatever', 'replies' => [
                    ['canvas_id' => 20, 'author_id'=> $author_id, 'body' => 'xd', 'created_at' => $now],
                    ['canvas_id' => 30, 'author_id'=> $first_recipient, 'body' => 'alv',
                    'created_at' => $now],
                    ['canvas_id' => 40, 'author_id'=> $first_recipient, 'body' => 'chau',
                    'created_at' => $now]
                ]
            ]
        ]);
        $author_id = CourseBuilder::idFromCanvasId(5);
        $first_recipient = CourseBuilder::idFromCanvasId(2);
        $second_recipient = CourseBuilder::idFromCanvasId(3);
        $course->addMailMessages([
            ['canvas_id' => 200, 'from' => $author_id, 'to' => [$first_recipient, $second_recipient],
            'subject' => 'whatever', 'replies' => [
                    ['canvas_id' => 50, 'author_id'=> $author_id, 'body' => 'xd', 'created_at' => $now],
                    ['canvas_id' => 60, 'author_id'=> $first_recipient, 'body' => 'alv',
                    'created_at' => $now],
                    ['canvas_id' => 70, 'author_id'=> $second_recipient, 'body' => 'chau',
                    'created_at' => $now]
                ]
            ]
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseCommunication($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        $this->assertEquals(6, $result->mail_messages_count);
        $this->assertEquals(100, $result->mail_messages_percentage);
        $this->assertEquals(0, $result->discussion_entry_count);
        $this->assertEquals(0, $result->discussion_entry_percentage);
        $this->assertCount(4, $result->mail_messages_list);
        $this->assertCount(4, $result->discussion_entry_list);
        $this->assertEquals(2, $result->mail_messages_list[0]->member_canvas_id);
        $this->assertEquals(2, $result->mail_messages_list[0]->creation_count);
        $this->assertEquals(3, $result->mail_messages_list[1]->member_canvas_id);
        $this->assertEquals(3, $result->mail_messages_list[1]->creation_count);
        $this->assertEquals(4, $result->mail_messages_list[2]->member_canvas_id);
        $this->assertEquals(0, $result->mail_messages_list[2]->creation_count);
        $this->assertEquals(5, $result->mail_messages_list[3]->member_canvas_id);
        $this->assertEquals(1, $result->mail_messages_list[3]->creation_count);
        foreach($result->discussion_entry_list as $member) {
            $this->assertEquals(0, $member->creation_count);
        }
    }

    public function test_not_count_mail_from_teacher(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $yesterday = Carbon::now()->subDays(1)->format('Y-m-d H:m:s');
        $now = Carbon::now()->format('Y-m-d H:m:s');
        $teacher_id = CourseBuilder::idFromCanvasId(1);
        $student_id = CourseBuilder::idFromCanvasId(2);
        $course->addMailMessages([
            ['canvas_id' => 100, 'from' => $teacher_id, 'to' => [2, 3, 4, 5],
            'subject' => 'whatever', 'replies' => [
                    ['canvas_id' => 20, 'author_id'=> $teacher_id, 'body' => 'xd', 'created_at' => $now],
                    ['canvas_id' => 40, 'author_id'=> $student_id, 'body' => 'chau', 'created_at' => $now]
                ]
            ]
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseCommunication($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        $this->assertEquals(1, $result->mail_messages_count);
        $this->assertEquals(100, $result->mail_messages_percentage);
        $this->assertEquals(0, $result->discussion_entry_count);
        $this->assertEquals(0, $result->discussion_entry_percentage);
        $this->assertCount(4, $result->mail_messages_list);
        $this->assertCount(4, $result->discussion_entry_list);
        $this->assertEquals(2, $result->mail_messages_list[0]->member_canvas_id);
        $this->assertEquals(1, $result->mail_messages_list[0]->creation_count);
        $this->assertEquals(3, $result->mail_messages_list[1]->member_canvas_id);
        $this->assertEquals(0, $result->mail_messages_list[1]->creation_count);
        $this->assertEquals(4, $result->mail_messages_list[2]->member_canvas_id);
        $this->assertEquals(0, $result->mail_messages_list[2]->creation_count);
        $this->assertEquals(5, $result->mail_messages_list[3]->member_canvas_id);
        $this->assertEquals(0, $result->mail_messages_list[3]->creation_count);
        foreach($result->discussion_entry_list as $member) {
            $this->assertEquals(0, $member->creation_count);
        }
    }

    public function test_course_with_only_discussion(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $first_user_id = CourseBuilder::idFromCanvasId(2);
        $second_user_id = CourseBuilder::idFromCanvasId(3);
        $course->addDiscussionComments([
            ['discussion_entry_id' => 100, 'user_id' => $first_user_id,'topic_id' => 1000],
            ['discussion_entry_id' => 101, 'user_id' => $first_user_id,'topic_id' => 1000],
            ['discussion_entry_id' => 200, 'user_id' => $second_user_id,'topic_id' => 1000]
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseCommunication($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(0, $result->mail_messages_count);
        $this->assertEquals(0, $result->mail_messages_percentage);
        $this->assertEquals(3, $result->discussion_entry_count);
        $this->assertEquals(100, $result->discussion_entry_percentage);
        $this->assertCount(4, $result->mail_messages_list);
        $this->assertCount(4, $result->discussion_entry_list);
        $this->assertEquals(2, $result->discussion_entry_list[0]->member_canvas_id);
        $this->assertEquals(2, $result->discussion_entry_list[0]->creation_count);
        $this->assertEquals(3, $result->discussion_entry_list[1]->member_canvas_id);
        $this->assertEquals(1, $result->discussion_entry_list[1]->creation_count);
        $this->assertEquals(4, $result->discussion_entry_list[2]->member_canvas_id);
        $this->assertEquals(0, $result->discussion_entry_list[2]->creation_count);
        $this->assertEquals(5, $result->discussion_entry_list[3]->member_canvas_id);
        $this->assertEquals(0, $result->discussion_entry_list[3]->creation_count);
        foreach($result->mail_messages_list as $member) {
            $this->assertEquals(0, $member->creation_count);
        }
    }

    public function test_cumulative_discussions(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $first_user_id = CourseBuilder::idFromCanvasId(2);
        $second_user_id = CourseBuilder::idFromCanvasId(3);
        $course->addDiscussionComments([
            ['discussion_entry_id' => 100, 'user_id' => $first_user_id,'topic_id' => 1000],
            ['discussion_entry_id' => 101, 'user_id' => $first_user_id,'topic_id' => 1000],
            ['discussion_entry_id' => 200, 'user_id' => $second_user_id,'topic_id' => 1000]
        ]);
        $first_user_id = CourseBuilder::idFromCanvasId(4);
        $second_user_id = CourseBuilder::idFromCanvasId(2);
        $course->addDiscussionComments([
            ['discussion_entry_id' => 301, 'user_id' => $first_user_id,'topic_id' => 1000],
            ['discussion_entry_id' => 302, 'user_id' => $first_user_id,'topic_id' => 1000],
            ['discussion_entry_id' => 303, 'user_id' => $first_user_id,'topic_id' => 1000],
            ['discussion_entry_id' => 304, 'user_id' => $first_user_id,'topic_id' => 1000],
            ['discussion_entry_id' => 305, 'user_id' => $first_user_id,'topic_id' => 1000],
            ['discussion_entry_id' => 306, 'user_id' => $first_user_id,'topic_id' => 1000],
            ['discussion_entry_id' => 307, 'user_id' => $first_user_id,'topic_id' => 1000],
            ['discussion_entry_id' => 400, 'user_id' => $second_user_id,'topic_id' => 1000]
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseCommunication($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(0, $result->mail_messages_count);
        $this->assertEquals(0, $result->mail_messages_percentage);
        $this->assertEquals(11, $result->discussion_entry_count);
        $this->assertEquals(100, $result->discussion_entry_percentage);
        $this->assertCount(4, $result->mail_messages_list);
        $this->assertCount(4, $result->discussion_entry_list);
        $this->assertEquals(2, $result->discussion_entry_list[0]->member_canvas_id);
        $this->assertEquals(3, $result->discussion_entry_list[0]->creation_count);
        $this->assertEquals(3, $result->discussion_entry_list[1]->member_canvas_id);
        $this->assertEquals(1, $result->discussion_entry_list[1]->creation_count);
        $this->assertEquals(4, $result->discussion_entry_list[2]->member_canvas_id);
        $this->assertEquals(7, $result->discussion_entry_list[2]->creation_count);
        $this->assertEquals(5, $result->discussion_entry_list[3]->member_canvas_id);
        $this->assertEquals(0, $result->discussion_entry_list[3]->creation_count);
        foreach($result->mail_messages_list as $member) {
            $this->assertEquals(0, $member->creation_count);
        }
    }

    public function test_not_count_discussion_from_teacher(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $first_user_id = CourseBuilder::idFromCanvasId(1);
        $second_user_id = CourseBuilder::idFromCanvasId(2);
        $course->addDiscussionComments([
            ['discussion_entry_id' => 100, 'user_id' => $first_user_id,'topic_id' => 1000],
            ['discussion_entry_id' => 101, 'user_id' => $second_user_id,'topic_id' => 1000],
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseCommunication($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(0, $result->mail_messages_count);
        $this->assertEquals(0, $result->mail_messages_percentage);
        $this->assertEquals(1, $result->discussion_entry_count);
        $this->assertEquals(100, $result->discussion_entry_percentage);
        $this->assertCount(4, $result->mail_messages_list);
        $this->assertCount(4, $result->discussion_entry_list);
        $this->assertEquals(2, $result->discussion_entry_list[0]->member_canvas_id);
        $this->assertEquals(1, $result->discussion_entry_list[0]->creation_count);
        $this->assertEquals(3, $result->discussion_entry_list[1]->member_canvas_id);
        $this->assertEquals(0, $result->discussion_entry_list[1]->creation_count);
        $this->assertEquals(4, $result->discussion_entry_list[2]->member_canvas_id);
        $this->assertEquals(0, $result->discussion_entry_list[2]->creation_count);
        $this->assertEquals(5, $result->discussion_entry_list[3]->member_canvas_id);
        $this->assertEquals(0, $result->discussion_entry_list[3]->creation_count);
        foreach($result->mail_messages_list as $member) {
            $this->assertEquals(0, $member->creation_count);
        }
    }

    public function test_usage_percentage(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $first_user_id = CourseBuilder::idFromCanvasId(2);
        $second_user_id = CourseBuilder::idFromCanvasId(3);
        $course->addDiscussionComments([
            ['discussion_entry_id' => 100, 'user_id' => $first_user_id,'topic_id' => 1000],
            ['discussion_entry_id' => 101, 'user_id' => $first_user_id,'topic_id' => 1000],
            ['discussion_entry_id' => 102, 'user_id' => $first_user_id,'topic_id' => 1000],
            ['discussion_entry_id' => 103, 'user_id' => $second_user_id,'topic_id' => 1000],
            ['discussion_entry_id' => 104, 'user_id' => $second_user_id,'topic_id' => 1000],
            ['discussion_entry_id' => 105, 'user_id' => $second_user_id,'topic_id' => 1000],
            ['discussion_entry_id' => 106, 'user_id' => $second_user_id,'topic_id' => 1000],
        ]);
        $now = Carbon::now()->format('Y-m-d H:m:s');
        $author_id = CourseBuilder::idFromCanvasId(2);
        $first_recipient = CourseBuilder::idFromCanvasId(3);
        $second_recipient = CourseBuilder::idFromCanvasId(4);
        $course->addMailMessages([
            ['canvas_id' => 100, 'from' => $author_id, 'to' => [$first_recipient, $second_recipient],
            'subject' => 'whatever', 'replies' => [
                    ['canvas_id' => 20, 'author_id'=> $author_id, 'body' => 'xd', 'created_at' => $now],
                    ['canvas_id' => 30, 'author_id'=> $first_recipient, 'body' => 'alv',
                    'created_at' => $now],
                    ['canvas_id' => 40, 'author_id'=> $first_recipient, 'body' => 'chau',
                    'created_at' => $now],
                    ['canvas_id' => 50, 'author_id'=> $first_recipient, 'body' => 'chau',
                    'created_at' => $now]
                ]
            ]
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseCommunication($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(4, $result->mail_messages_count);
        $this->assertEquals(7, $result->discussion_entry_count);
        $this->assertEquals(36.36, $result->mail_messages_percentage);
        $this->assertEquals(63.64, $result->discussion_entry_percentage);
        $this->assertCount(4, $result->mail_messages_list);
        $this->assertCount(4, $result->discussion_entry_list);
    }

    public function test_not_count_discussions_in_evaluated_forum(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $first_user_id = CourseBuilder::idFromCanvasId(2);
        $second_user_id = CourseBuilder::idFromCanvasId(3);
        $course->addDiscussionComments([
            ['discussion_entry_id' => 100, 'user_id' => $first_user_id,'topic_id' => 1000,
            'assignment_id' => 2],
            ['discussion_entry_id' => 101, 'user_id' => $first_user_id,'topic_id' => 1000,
            'assignment_id' => 12],
            ['discussion_entry_id' => 200, 'user_id' => $second_user_id,'topic_id' => 1000,
            'assignment_id' => 222]
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseCommunication($course->getCourseCanvasId(), 1000);
        $result = json_decode($report->getContent());
        // Assertions
        $this->assertEquals(0, $result->mail_messages_count);
        $this->assertEquals(0, $result->mail_messages_percentage);
        $this->assertEquals(0, $result->discussion_entry_count);
        $this->assertEquals(0, $result->discussion_entry_percentage);
        $this->assertCount(4, $result->mail_messages_list);
        $this->assertCount(4, $result->discussion_entry_list);
        $this->assertEquals(2, $result->discussion_entry_list[0]->member_canvas_id);
        $this->assertEquals(0, $result->discussion_entry_list[0]->creation_count);
        $this->assertEquals(3, $result->discussion_entry_list[1]->member_canvas_id);
        $this->assertEquals(0, $result->discussion_entry_list[1]->creation_count);
        $this->assertEquals(4, $result->discussion_entry_list[2]->member_canvas_id);
        $this->assertEquals(0, $result->discussion_entry_list[2]->creation_count);
        $this->assertEquals(5, $result->discussion_entry_list[3]->member_canvas_id);
        $this->assertEquals(0, $result->discussion_entry_list[3]->creation_count);
        foreach($result->mail_messages_list as $member) {
            $this->assertEquals(0, $member->creation_count);
        }
    }


    private function processReports(int $course_canvas_id) {
        (new devController())->rebuildReports($course_canvas_id);
    }
}
