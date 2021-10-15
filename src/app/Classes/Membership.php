<?php
namespace App\Classes;

use App\Classes\DataStructure\Identifier;
use DeepCopy\DeepCopy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Membership {
    const STUDENT_ROLES = ['StudentEnrollment'];
    const VALID_ENROLLMENTS = ['active', 'completed'];
    private Identifier $courseIdentifier;
    private Collection $sections;
    private Collection $members;
    private Collection $sections_with_members;

    public function __construct(Identifier $courseIdentifier){
        $this->courseIdentifier = $courseIdentifier;
        $this->setAllSections();
        $this->setMembers();
        $this->sortMembersInSections();
        $this->removeSectionsWithoutStudents();
    }

    public function getMembers() : Collection {
        $copier = new DeepCopy();
        return $copier->copy($this->members)->values();
    }

    public function getSections() : Collection {
        $copier = new DeepCopy();
        $sections = $copier->copy($this->sections)->values();
        return $sections;
    }

    public static function getStudentIds(int $course_id, int $section_canvas_id) : array {
        $section_id = self::getSectionIdFromCanvasId($section_canvas_id);
        $students_id = DB::table('enrollment_dim')
        ->select('user_id')
        ->where('course_id', $course_id)
        ->where('course_section_id', $section_id)
        ->whereIn('workflow_state', static::VALID_ENROLLMENTS)
        ->whereIn('type', static::STUDENT_ROLES)
        ->pluck('user_id')->toArray();
        return $students_id;
    }

    public static function getSectionIdFromCanvasId(int $section_canvas_id) : int {
        $row = DB::table('course_section_dim')->select('id')
        ->where('canvas_id', $section_canvas_id)->first();
        return $row?->id;
    }

    public function getSectionsWithMembers(array $roles = null) : Collection {
        $copier = new DeepCopy();
        $sections = $copier->copy($this->sections_with_members);
        if(!is_null($roles)){
            foreach($sections as $section){
                $section->members = $section->members->filter(function ($member) use ($roles){
                    return in_array($member->role_type, $roles);
                })->values();
            }
        }
        return $sections->values();
    }

    private function setAllSections() : void {
        $this->sections = DB::table('course_section_dim')
        ->select('id','canvas_id','name','default_section', 'workflow_state')
        ->where([['course_id', $this->courseIdentifier->id], ['workflow_state','active']])
        ->get();
    }

    public function setMembers() : void {
        $this->members = DB::table('enrollment_dim')
        ->select('user_dim.id','user_dim.canvas_id',
        'user_dim.name',
        'enrollment_dim.course_section_id',
        'enrollment_dim.type as role_type')
        ->where('enrollment_dim.course_id', $this->courseIdentifier->id)
        ->whereIn('enrollment_dim.workflow_state', static::VALID_ENROLLMENTS)
        ->join('user_dim', 'enrollment_dim.user_id', 'user_dim.id')
        ->get();
    }

    private function sortMembersInSections() : void {
        $sections = $this->getSections();
        $members = $this->getMembers();
        foreach($sections as $key => $section){
            $section->members = $members->filter(function($member) use ($section) {
                return $member->course_section_id == $section->id;
            })->values();
            if($section->members->count() == 0){
                $sections->forget($key);
            }
        }
        $this->sections_with_members = $sections;
    }

    private function removeSectionsWithoutStudents() : void {
        $sections_with_members = $this->getSectionsWithMembers();
        $sections = array();
        $sections_without_students = array();
        foreach($sections_with_members as $section){
            $members = $section->members;
            $roles = $section->members->groupBy('role_type');
            if(isset($roles['StudentEnrollment']) && count($roles['StudentEnrollment']) >= 1){
                unset($section->members);
                array_push($sections, $section);
            }else{
                array_push($sections_without_students, $section->id);
            }
        }
        $this->sections = collect($sections);
        foreach($this->sections_with_members as $index => $section){
            if(in_array($section->id, $sections_without_students)){
                $this->sections_with_members->forget($index);
            }
        }
    }

    public static function countAll(int $course_id) : int {
        return DB::table('enrollment_dim')->where('course_id', $course_id)
        ->whereIn('workflow_state', static::VALID_ENROLLMENTS)->count();
    }
}
