<?php

namespace App\Http\Controllers;

use App\Classes\Course;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class devController extends Controller
{
    public function rebuildReports($course_canvas_id){
        $course = new Course($course_canvas_id);
        (new ReportsManagementController())->rebuildReports($course_canvas_id);
    }

    public function processAllCourses(){
        $semestre_2_2020 = 143540000000000037;
        $semestre_1_2020 = 143540000000000040;
        $courses_id = DB::table('course_dim')->where('enrollment_term_id', $semestre_1_2020)->select('canvas_id')->pluck('canvas_id');
        $total = $courses_id->count();
        $printed_progress = array();
        echo 'Procesando...';
        foreach($courses_id as $index => $course_canvas_id){
            (new CourseProcessingRequestController())
            ->addCourseToQueue($course_canvas_id);
        }
        echo '¡LISTO!';
    }

    public function test(){
        $token = Auth::user()->getCanvasToken();
        $options = ['headers' => ['Authorization' => "Bearer $token"]];
        $client = new Client($options);
        $params = [
            // 'recipients[]' => 436, // Alejandra ilabaca
            'recipients[]' => 121, // César Mora
            'subject' => 'Prueba API',
            'body' => 'Hola, esto es solo una prueba tecnica, ignorame :)',
            'force_new' => true,
            'scope' => 'unread',
        ];
        $url = 'https://udec.test.instructure.com/api/v1/conversations';
        $res = $client->post($url, ['form_params' => $params]);
        dd($res->getBody()->getContents());
    }

}
