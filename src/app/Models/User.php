<?php

namespace App\Models;

use App\Classes\Course;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'user_dim';
    protected $primaryKey = 'canvas_id';
    public $incrementing = false;

    protected $fillable = ['id', 'canvas_id', 'root_account_id','name','time_zone',
    'created_at','visibility','school_name','school_position','gender',
    'locale','public','birthdate','country_code','workflow_state','sortable_name',
    'global_canvas_id'];

    public function summary() : array {
        return ['canvas_id' => $this->canvas_id,'name' => $this->name,
        'sortable_name' => $this->sortable_name];
    }

    public static function emailFromId (int $user_id) : ?string {
        $row = DB::table('communication_channel_dim')->select('address')->where([
            ['user_id', $user_id], ['type', 'email'], ['workflow_state', 'active']
        ])->orderBy('position', 'DESC')->first();
        return $row?->address;
    }

    public function isTeacherInSomeCourse() : bool {
        $courses_count = DB::table('enrollment_dim')->where('user_id', $this->id)
        ->where('type', 'TeacherEnrollment')->whereIn('workflow_state', ['active','completed'])
        ->count();
        return $courses_count > 0;
    }

    public function coursesWhereIsTeacher() : Collection {
        $courses = DB::table('enrollment_dim')
        ->select('course_dim.canvas_id as course_canvas_id', 'course_dim.name as course_name',
        'enrollment_term_dim.id as enrollment_term_dim_id', 'enrollment_term_dim.name as term_name',
        'course_dim.code')
        ->where('enrollment_dim.user_id', $this->id)
        ->where('enrollment_dim.type', 'TeacherEnrollment')
        ->whereIn('enrollment_dim.workflow_state', ['active','completed'])
        ->join('course_dim', 'enrollment_dim.course_id', 'course_dim.id')
        ->join('enrollment_term_dim', 'course_dim.enrollment_term_id', 'enrollment_term_dim.id')
        ->groupBy(['course_dim.canvas_id', 'course_dim.name', 'enrollment_term_dim.id',
        'enrollment_term_dim.name','course_dim.code'])
        ->get();
        return $courses;
    }

    public function isEnrolledAs(string $role, int $course_canvas_id) : bool {
        $is_enrolled = DB::table('course_dim')
            ->join('enrollment_dim', 'course_dim.id','enrollment_dim.course_id')
            ->where('course_dim.canvas_id', $course_canvas_id)
            ->where('enrollment_dim.user_id', $this->id)
            ->where('enrollment_dim.type', $role)
            ->count();
        return $is_enrolled > 0;
    }

    public function getCanvasToken() : ?string {
        $token = CanvasToken::find($this->canvas_id)->first();
        if(!$token->isValid()){
            $token->refresh();
        }
        return $token->access_token;
    }
}
