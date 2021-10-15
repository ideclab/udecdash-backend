<?php
namespace App\Classes\InteractionsDiscovery;

use App\Models\CourseInteraction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Interaction {

    public const WIKI_PAGE = 'WikiPage';
    public const QUIZ = 'Quiz';
    public const ATTACHMENT = 'Attachment';
    public const DISCUSSION_TOPIC = 'DiscussionTopic';
    public const ASSIGNMENT = 'Assignment';
    public const EXTERNAL_URL = 'ExternalUrl';
    public const CONTEXT_EXTERNAL_TOOL = 'ContextExternalTool';
    public const UNPROCESSABLE = 'Unprocessable';
    public $log;
    public $course_id;
    public $user_id;
    public $item_id;
    public $item_canvas_id;
    public $type;
    public $viewed;
    public $url;
    public $device;
    public $params;
    public $error_label;
    public $error_message;
    public $component_name;

    public function __construct(){
        $this->type = self::UNPROCESSABLE;
    }

    public function moveToRespectiveInteractionTable() : void {
        $this->saveInInteractionTable();
        $this->createCourseInteraction();
        $this->deleteAssociateLog();
    }

    private function createCourseInteraction() : void {
        if($this->type == self::UNPROCESSABLE){
            return;
        }
        $viewed = strtotime($this->viewed);
        $year_month = date('Y', $viewed) . date('m', $viewed);
        $interaction = ["user_id" => $this->user_id, "course_id" => $this->course_id,
        'interaction_date' => $this->viewed, 'year_month' =>  $year_month];
        Log::debug('[Interaction::class] [createCourseInteraction] Course interaction', $interaction);
        try{
            DB::table('course_interactions')->insert($interaction);
        }catch(\Exception $e){
        }
    }

    private function saveInInteractionTable() : void {
        $class_name = "App\\Models\\{$this->type}Interaction";
        $interaction = new $class_name();
        $interaction->course_id = $this->course_id;
        $interaction->user_id = $this->user_id;
        $interaction->url = $this->url;
        if($this->type == self::UNPROCESSABLE){
            $interaction->log = json_encode($this->log);
            $interaction->error_label = $this->error_label;
            $interaction->error_message = $this->error_message;
        }else{
            $interaction->item_id = $this->item_id;
            $interaction->item_canvas_id = $this->item_canvas_id;
            $interaction->viewed = $this->viewed;
            $interaction->device = $this->device;
        }
        try{
            $interaction->save();
        }catch(\Exception $e){
        }
    }

    public static function isTrash(object $log) : bool {
        $isTrash = false;
        foreach(static::patternToIdentifyTrash() as $pattern_name => $pattern){
            $isMatch = preg_match($pattern, $log->url);
            if($isMatch){
                $isTrash = true;
                Log::debug("[Interaction::class] [isTrash] The url was identified like trash by the pattern ({$pattern_name}). =>", [$log->url, $log]);
                break;
            }
        }
        return $isTrash;
    }

    public static function patternToIdentifyTrash() : array {
        $patterns = [
            'ping' => "/\/courses\/\d+\/ping$/",
            'activity_stream' => "/\/courses\/\d+\/activity_stream\/summary$/",
            'quiz_submission_backup' => "/\/courses\/\d+\/quizzes\/\d+\/submissions\/backup\?user_id=\d+.*/"
        ];
        return $patterns;
    }

    public function deleteAssociateLog() : void {
        // Log::emergency("[Interaction::class] [delete] This log was deleted from database =>", [$this->log]);
        // DB::table('requests')->where('local_id', $this->log->local_id)->delete();
    }
}
