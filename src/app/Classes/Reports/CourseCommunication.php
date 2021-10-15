<?php

namespace App\Classes\Reports;

use App\Classes\Course;
use App\Classes\DataStructure\LoadFilter;
use App\Classes\DataStructure\Reports\CourseCommunication\CommunicationSummary;
use App\Classes\DataStructure\Reports\CourseCommunication\UserCreation;
use App\Classes\Membership;
use App\Classes\Reports\Report;
use App\Interfaces\Cacheable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class CourseCommunication extends Report implements Cacheable {

    private $course_mail_messages;
    private $course_discussions_entry;

    function __construct(Course $course){
        parent::__construct($course);
    }

    public static function defaultCacheCode() : int {
        return 1;
    }

    protected function build() : void {
        $this->createReportOutputStructure();
        $this->setCourseMessages();
        $this->setDiscussionMessages();
        $this->addSectionCommunication();
        $this->calculateCommunicationSectionsPercentages();
    }

    protected function setLoadFilter() : LoadFilter {
        $filter = new LoadFilter();
        return $filter;
    }

    protected function createReportOutputStructure() : void {
        $this->results = $this->course->getMembership()
            ->getSectionsWithMembers(Membership::STUDENT_ROLES);
        foreach($this->results as $section){
            $section->communication = new CommunicationSummary();
        }
    }

    private function setCourseMessages(){
        $messages = DB::table('conversation_dim')
        ->select('conversation_message_dim.author_id', DB::raw('count(*)'),'user_dim.canvas_id')
        ->join('conversation_message_dim', 'conversation_dim.id',
        'conversation_message_dim.conversation_id')
        ->join('user_dim','conversation_message_dim.author_id','user_dim.id')
        ->where([['conversation_dim.course_id', $this->course->getId()],
        ['conversation_message_dim.created_at', '>=', $this->load_filter->from],
        ['conversation_message_dim.created_at', '<=', $this->load_filter->until]])
        ->groupBy('conversation_message_dim.author_id','user_dim.canvas_id')
        ->get();
        $messages = $messages->groupBy('canvas_id');
        $this->course_mail_messages = $messages;
    }

    private function setDiscussionMessages(){
        $discussions = DB::table('discussion_entry_fact')
        ->join('user_dim','discussion_entry_fact.user_id','user_dim.id')
        ->select('user_id', DB::raw('count(*)'), 'user_dim.canvas_id')
        ->where([['course_id', $this->course->getId()]])
        ->wherenull('topic_assignment_id')
        ->groupBy('discussion_entry_fact.user_id', 'user_dim.canvas_id')
        ->get();
        $discussions = $discussions->groupBy(['user_id']);
        $this->course_discussions_entry = $discussions;
    }

    private function addSectionCommunication(){
        foreach($this->results as $section){
            foreach($section->members as $member){
                $user_creation = $this->getUserDiscussionEntriesCreation($member);
                $section->communication->discussion_entry_count += $user_creation->creation_count;
                $section->communication->discussion_entry_list->push($user_creation);
                $user_creation = $this->getUserMailMessagesCreation($member);
                $section->communication->mail_messages_count += $user_creation->creation_count;
                $section->communication->mail_messages_list->push($user_creation);
            }
        }
    }

    private function getUserDiscussionEntriesCreation(object $member) : UserCreation {
        $creation = new UserCreation();
        $creation->member_canvas_id = $member->canvas_id;
        if(isset($this->course_discussions_entry[$member->id])){
            foreach($this->course_discussions_entry[$member->id] as $interactions){
                $creation->creation_count += $interactions->count;
            }
        }
        return $creation;
    }

    private function getUserMailMessagesCreation(object $member) : UserCreation {
        $creation = new UserCreation();
        $creation->member_canvas_id = $member->canvas_id;
        if(isset($this->course_mail_messages[$member->canvas_id])){
            foreach($this->course_mail_messages[$member->canvas_id] as $interactions){
                $creation->creation_count += $interactions->count;
            }
        }
        return $creation;
    }

    private function calculateCommunicationSectionsPercentages(){
        foreach($this->results as $section){
            $all_count = $section->communication->mail_messages_count +
            $section->communication->discussion_entry_count;
            $section->communication->mail_messages_percentage = $this->percentage(
                $section->communication->mail_messages_count, $all_count);
            $section->communication->discussion_entry_percentage = $this->percentage(
                $section->communication->discussion_entry_count, $all_count);
        }
    }

    // Override methods for not load interactions
    protected function loadInteractions() : void {
        $this->interactions = [];
    }

    protected function cleanResults(Collection|array $sections) : Collection|array {
        foreach($sections as $section){
            unset($section->id);
            unset($section->name);
            unset($section->default_section);
            unset($section->workflow_state);
            unset($section->members);
        }
        return $sections;
    }
}
