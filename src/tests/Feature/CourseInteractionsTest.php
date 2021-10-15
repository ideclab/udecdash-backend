<?php

namespace Tests\Feature;

use App\Http\Controllers\devController;
use App\Http\Controllers\ReportsController;
use App\Models\WikiPageInteraction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\CourseBuilder;
use Tests\ExampleCourses\algebraCourse;
use Tests\TestCase;

class CourseInteractionsTest extends TestCase
{
    use algebraCourse;

    public function test_month_without_interactions(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $now = Carbon::now()->format('Y-m-d H:m:s');
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $year = 2020;
        $month = 5;
        $report = $controller->courseInteractions($course->getCourseCanvasId(), 1000, $year, $month);
        $result = json_decode($report->getContent());
        $this->assertIsArray($result);
        $this->assertCount(0, $result);
    }

    public function test_only_one_interaction_of_one_user(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $year = 2020;
        $month = 10;
        $first_date = "{$year}-{$month}-01";
        DB::table('course_interactions')->insert([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'year_month' => 202010,
            'interaction_date' => "{$first_date} 00:00:00",
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseInteractions($course->getCourseCanvasId(), 1000, $year, $month);
        $result = json_decode($report->getContent());
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(2, $result[0]->canvas_id);
        $this->assertCount(1, $result[0]->interactions);
        $this->assertEquals( $first_date, $result[0]->interactions[0]);
    }

    public function test_multiple_interactions_of_one_user(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $year = 2020;
        $month = 10;
        $first_date = "{$year}-{$month}-01";
        DB::table('course_interactions')->insert([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'year_month' => 202010,
            'interaction_date' => "{$first_date} 00:00:00",
        ]);
        $second_date = "{$year}-{$month}-31";
        DB::table('course_interactions')->insert([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'year_month' => 202010,
            'interaction_date' => "{$second_date} 23:59:59",
        ]);
        // course_interactions
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseInteractions($course->getCourseCanvasId(), 1000, $year, $month);
        $result = json_decode($report->getContent());
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(2, $result[0]->canvas_id);
        $this->assertCount(2, $result[0]->interactions);
        $this->assertEquals($first_date, $result[0]->interactions[0]);
        $this->assertEquals($second_date, $result[0]->interactions[1]);
    }

    public function test_assign_correct_interactions_to_each_students(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        $year = 2020;
        $month = 10;
        $first_date = "{$year}-{$month}-01";
        DB::table('course_interactions')->insert([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(5),
            'year_month' => 202010,
            'interaction_date' => "{$first_date} 00:00:00",
        ]);
        $second_date = "{$year}-{$month}-31";
        DB::table('course_interactions')->insert([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'year_month' => 202010,
            'interaction_date' => "{$second_date} 23:59:59",
        ]);
        $third_date = "{$year}-{$month}-20";
        DB::table('course_interactions')->insert([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(4),
            'year_month' => 202010,
            'interaction_date' => "{$third_date} 23:59:59",
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseInteractions($course->getCourseCanvasId(), 1000, $year, $month);
        $result = json_decode($report->getContent());
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals(5, $result[0]->canvas_id);
        $this->assertEquals(4, $result[1]->canvas_id);
        $this->assertCount(1, $result[0]->interactions);
        $this->assertCount(2, $result[1]->interactions);
        $this->assertEquals($first_date, $result[0]->interactions[0]);
        $this->assertEquals($second_date, $result[1]->interactions[0]);
        $this->assertEquals($third_date, $result[1]->interactions[1]);
    }

    public function test_dont_merge_data_of_old_years_for_the_month(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        DB::table('course_interactions')->insert([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'year_month' => 201910,
            'interaction_date' => "2019-10-05 00:00:00",
        ]);
        DB::table('course_interactions')->insert([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'year_month' => 202010,
            'interaction_date' => "2020-10-05 00:00:00",
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseInteractions($course->getCourseCanvasId(), 1000, 2020, 10);
        $result = json_decode($report->getContent());
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(2, $result[0]->canvas_id);
        $this->assertCount(1, $result[0]->interactions);
        $this->assertEquals("2020-10-05", $result[0]->interactions[0]);
    }

    public function test_dont_include_limit_dates_for_the_previous_and_next_month(){
        $course = $this->buildAlgebraCourse();
        $controller = new ReportsController();
        DB::table('course_interactions')->insert([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'year_month' => 202004,
            'interaction_date' => "2020-04-30 23:59:59",
        ]);
        DB::table('course_interactions')->insert([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'year_month' => 202005,
            'interaction_date' => "2020-05-05 00:00:00",
        ]);
        DB::table('course_interactions')->insert([
            'course_id' => $course->getCourseId(),
            'user_id' => CourseBuilder::idFromCanvasId(2),
            'year_month' => 202006,
            'interaction_date' => "2020-06-01 00:00:00",
        ]);
        // Execute
        $this->processReports($course->getCourseCanvasId());
        $report = $controller->courseInteractions($course->getCourseCanvasId(), 1000, 2020, 5);
        $result = json_decode($report->getContent());
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals(2, $result[0]->canvas_id);
        $this->assertCount(1, $result[0]->interactions);
        $this->assertEquals("2020-05-05", $result[0]->interactions[0]);
    }

    private function processReports(int $course_canvas_id) {
        (new devController())->rebuildReports($course_canvas_id);
    }
}
