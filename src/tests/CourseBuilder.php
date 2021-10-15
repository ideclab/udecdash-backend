<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseBuilder {
    const BASE_ID = 143540000000000000;

    function __construct(int $canvas_id, string $name){
        $id = $this->createCourseDim($canvas_id, $name);
        $this->course = new \StdClass();
        $this->course->id = $id;
        $this->course->canvas_id = $canvas_id;
    }

    public function getCourseCanvasId(){
        return $this->course->canvas_id;
    }

    public function getCourseId(){
        return $this->course->id;
    }

    public function createCourseDim(int $canvas_id, string $name) : int {
        DB::table('course_dim')->insert([
                'id' => $this->idFromCanvasId($canvas_id),
                'canvas_id' => $canvas_id,
                'root_account_id' => null,
                'account_id' => null,
                'enrollment_term_id' => null,
                'name' => $name,
                'code' => str_replace(' ','-', $name),
                'type' => null,
                'created_at' => Carbon::now(),
                'start_at' => null,
                'conclude_at' => null,
                'publicly_visible' => null,
                'sis_source_id' => null,
                'workflow_state' => 'available',
                'wiki_id' => null,
                'syllabus_body' => null
            ]);
        return $this->idFromCanvasId($canvas_id);
    }


    public function addSections(array $sections) : void {
        foreach ($sections as $section){
            DB::table('course_section_dim')->insert([
                'id' => $this->idFromCanvasId($section['canvas_id']),
                'canvas_id' => $section['canvas_id'],
                'name' => $section['name'],
                'course_id' => $this->course->id,
                'workflow_state' => $this->getValue('workflow_state', $section, 'active'),
                'default_section' => $this->getValue('default_section', $section, 'false')
            ]);
        }
    }

    public function addUsers(array $users) : void {
        foreach($users as $user){
            DB::table('user_dim')->insert([
                "id" => $this->idFromCanvasId($user['canvas_id']),
                "canvas_id" => $user['canvas_id'],
                "name" => $user['name'],
                "workflow_state" => "registered",
                "sortable_name" => $user['name']
            ]);
        }
    }

    public function addEnrollment(array $enrollments) : void {
            foreach ($enrollments as $enrollment){
                $enrollment_id = $this->createId();
                $workflow_state = $this->getValue('workflow_state', $enrollment, 'active');
                DB::table('enrollment_dim')->insert([
                    "id" => $enrollment_id,
                    "canvas_id" => $enrollment_id,
                    "course_section_id" => $enrollment['section_id'],
                    "role_id" => $this->getRoleId($enrollment['role']),
                    "type" => $enrollment['role'],
                    "workflow_state" => $workflow_state,
                    "course_id" => $this->course->id,
                    "user_id" => $enrollment['user_id']
                ]);
            }
    }

    private function getRoleId (string $role_name) : int {
        $roles = [
            "StudentEnrollment" => 143540000000000003,
            "StudentViewEnrollment" => 143540000000000003,
            "TeacherEnrollment" => 143540000000000004,
            "TaEnrollment" => 143540000000000005,
            "DesignerEnrollment" => 143540000000000006,
            "ObserverEnrollment" => 143540000000000007,
            "StudentEnrollment" => 143540000000000041
        ];
        return $roles[$role_name];
    }

    public function addModules(array $modules) : void {
        foreach($modules as $index => $module){
            $position = $this->getValue('position', $module, $index);
            $workflow_state = $this->getValue('workflow_state', $module, 'active');
            $id = $this->idFromCanvasId($module['canvas_id']);
            $modules[$index]['position'] = $position;
            $modules[$index]['workflow_state'] = $workflow_state;
            $modules[$index]['id'] = $id;
            DB::table('module_dim')->insert([
                'id' => $id,
                'canvas_id' => $module['canvas_id'],
                'course_id' => $this->course->id,
                'workflow_state' => $workflow_state,
                'position' => $position,
                'name' => $module['name']
            ]);
        }
    }

    public function addPages(array $pages) : void {
        foreach($pages as $index => $page){
            $id = $this->idFromCanvasId($page['canvas_id']);
            $workflow_state = $this->getValue('workflow_state', $page, 'active');
            $body = $this->getValue('body', $page, '<h1>Default page</h1>');
            $page['id'] = $id;
            $page['workflow_state'] = $workflow_state;
            $page['body'] = $body;
            $pages[$index] = $page;
            DB::table('wiki_page_dim')->insert([
                'id' => $id,
                'canvas_id' => $page['canvas_id'],
                'title' => $page['title'],
                'body' => $page['body'],
                'workflow_state' => $workflow_state,
                'url' => Str::slug($page['title'], '-'),
            ]);
            DB::table('wiki_page_fact')->insert([
                'wiki_page_id' => $id,
                'parent_course_id' => $this->course->id
            ]);
            if($this->hasModuleItemConfig($page)){
                $page['module_item']['title'] = $page['title'];
                $page['module_item']['item_id'] = $page['id'];
                $this->addAsModuleItem($page['module_item'], 'WikiPage');
            }
        }
    }

    private function hasModuleItemConfig(array $item) : bool {
        return isset($item['module_item']) &&
            isset($item['module_item']['module_id']) &&
            isset($item['module_item']['position']);
    }

    public function addQuizzes(array $quizzes) : void {
        foreach($quizzes as $quiz){
            $assignment_id = null;
            if(isset($quiz['assignment'])){
                $assignment_id = $this->idFromCanvasId($quiz['assignment']['canvas_id']);
                $position = null;
                if(isset($quiz['module_item']['position'])){
                    $position = $quiz['module_item']['position'];
                }
                DB::table('assignment_dim')->insert([
                    'id' => $assignment_id,
                    'canvas_id' => $quiz['assignment']['canvas_id'],
                    'course_id' => $this->course->id,
                    'title' => $quiz['name'],
                    'description' => $this->getValue('description', $quiz),
                    'due_at' => $this->getValue('due_at', $quiz),
                    'unlock_at' => $this->getValue('unlock_at', $quiz),
                    'lock_at' => $this->getValue('lock_at', $quiz),
                    'position' => $position,
                    'points_possible' => $this->getValue('points_possible', $quiz),
                    'grading_type'  => $this->getValue('points_possible', $quiz, 'points'),
                    'submission_types' => $this->getValue('submission_types', $quiz, 'online_quiz'),
                    'workflow_state' => $this->getValue('workflow_state', $quiz, 'published')
                ]);
            }
            DB::table('quiz_dim')->insert([
                'id' => $this->idFromCanvasId($quiz['canvas_id']),
                'canvas_id' => $quiz['canvas_id'],
                'name' => $quiz['name'],
                'points_possible' => $this->getValue('points_possible', $quiz),
                'description' => $this->getValue('description', $quiz),
                'quiz_type' => $quiz['quiz_type'],
                'course_id' => $this->course->id,
                'assignment_id' => $assignment_id,
                'workflow_state' => $this->getValue('workflow_state', $quiz, 'published'),
                'unlock_at' => $this->getValue('unlock_at', $quiz),
                'lock_at' => $this->getValue('lock_at', $quiz),
                'due_at' => $this->getValue('due_at', $quiz),
                'deleted_at' => $this->getValue('deleted_at', $quiz)
            ]);
            if($this->hasModuleItemConfig($quiz)){
                $quiz['module_item']['title'] = $quiz['name'];
                $quiz['module_item']['item_id'] = $this->idFromCanvasId($quiz['canvas_id']);
                $this->addAsModuleItem($quiz['module_item'], 'Quiz');
            }
        }
    }

    public function addFiles(array $files) : void {
        foreach($files as $file){
            DB::table('file_dim')->insert([
                'id' => $this->idFromCanvasId($file['canvas_id']),
                'canvas_id' => $file['canvas_id'],
                'display_name' => $file['display_name'],
                'course_id' => $this->course->id,
                'owner_entity_type' => $this->getValue('owner_entity_type', $file, 'course'),
                'content_type' => $file['content_type'],
                'file_state' => $this->getValue('file_state', $file, 'available')
            ]);
            if($this->hasModuleItemConfig($file)){
                $file['module_item']['title'] = $file['display_name'];
                $file['module_item']['item_id'] = $this->idFromCanvasId($file['canvas_id']);
                $this->addAsModuleItem($file['module_item'], 'Attachment');
            }
        }
    }

    public function addAssignments(array $assignments) : void {
        foreach($assignments as $assignment){
            DB::table('assignment_dim')->insert([
                'id' => $this->idFromCanvasId($assignment['canvas_id']),
                'canvas_id' => $assignment['canvas_id'],
                'course_id' => $this->course->id,
                'position' => $assignment['position'],
                'title' => $assignment['title'],
                'due_at' => $this->getValue('due_at', $assignment),
                'unlock_at' => $this->getValue('unlock_at', $assignment),
                'lock_at' => $this->getValue('lock_at', $assignment),
                'points_possible' => $this->getValue('points_possible', $assignment),
                'grading_type' => $this->getValue('lock_at', $assignment, 'points'),
                'submission_types' => $assignment['submission_types'],
                'workflow_state' => $this->getValue('workflow_state', $assignment, 'published'),
                'position' => $assignment['position']
            ]);
            if($this->hasModuleItemConfig($assignment)){
                $assignment['module_item']['title'] = $assignment['title'];
                $assignment['module_item']['position'] = $assignment['position'];
                $assignment['module_item']['item_id'] = $this->idFromCanvasId($assignment['canvas_id']);
                $this->addAsModuleItem($assignment['module_item'], 'Assignment');
            }
        }
    }

    public function addUrls(array $urls) : void {
        foreach($urls as $url){
            DB::table('module_item_dim')->insert([
                'id' => $this->idFromCanvasId($url['canvas_id']),
                'canvas_id' => $url['canvas_id'],
                'course_id' => $this->course->id,
                'module_id' => $url['module_id'],
                'content_type' => 'ExternalUrl',
                'workflow_state' => $this->getValue('workflow_state', $url,'active'),
                'position' => $url['position'],
                'title' => $url['title'],
                'url' => $url['url']
            ]);
        }
    }

    public function addExternalTools(array $external_tools) : void {
        foreach($external_tools as $tool){
            DB::table('module_item_dim')->insert([
                'id' => $this->idFromCanvasId($tool['canvas_id']),
                'canvas_id' => $tool['canvas_id'],
                'course_id' => $this->course->id,
                'module_id' => $tool['module_id'],
                'content_type' => 'ContextExternalTool',
                'workflow_state' => $this->getValue('workflow_state', $tool,'active'),
                'position' => $tool['position'],
                'title' => $tool['title'],
                'url' => $tool['url']
            ]);
        }
    }

    public function addDiscussionTopics(array $topics){
        foreach($topics as $topic){
            DB::table('discussion_topic_dim')->insert([
                'id' => $this->idFromCanvasId($topic['canvas_id']),
                'canvas_id' => $topic['canvas_id'],
                'title' => $topic['title'],
                'message' => $this->getValue('message', $topic),
                'type' => $this->getValue('type', $topic),
                'workflow_state' => $this->getValue('workflow_state', $topic, 'active'),
                'discussion_type' => $this->getValue('discussion_type', $topic, 'threaded'),
                'pinned' => $this->getValue('pinned', $topic, 'True'),
                'locked' => $this->getValue('locked', $topic,'False'),
                'course_id' => $this->course->id
            ]);
            if($this->hasModuleItemConfig($topic)){
                $topic['module_item']['title'] = $topic['title'];
                $topic['module_item']['item_id'] = $this->idFromCanvasId($topic['canvas_id']);
                $this->addAsModuleItem($topic['module_item'], 'DiscussionTopic');
            }
        }
    }

    public function addMailMessages(array $mails){
        foreach($mails as $mail){
            DB::table('conversation_dim')->insert([
                'id' => $this->idFromCanvasId($mail['canvas_id']),
                'canvas_id' => $mail['canvas_id'],
                'has_attachments' => 'False',
                'has_media_objects' => 'False',
                'subject' => $mail['subject'],
                'course_id' => $this->getCourseId()
            ]);
            foreach($mail['replies'] as $reply){
                DB::table('conversation_message_dim')->insert([
                    'id' => $this->idFromCanvasId($reply['canvas_id']),
                    'canvas_id' => $reply['canvas_id'],
                    'conversation_id' => $this->idFromCanvasId($mail['canvas_id']),
                    'author_id' => $reply['author_id'],
                    'created_at' => $reply['created_at'],
                    'generated' => 'False',
                    'has_attachments' => 'False',
                    'has_media_objects' => 'False',
                    'body' => $reply['body']
                ]);
            }
        }
    }

    public function addDiscussionComments(array $comments){
        foreach($comments as $comment){
            DB::table('discussion_entry_fact')->insert([
                'discussion_entry_id' => $comment['discussion_entry_id'],
                'user_id' => $comment['user_id'],
                'topic_id' => $comment['topic_id'],
                'course_id' => $this->getCourseId(),
                'topic_assignment_id' => $this->getValue('assignment_id', $comment)
            ]);
        }
    }

    private function getValue(string $key, array $array, mixed $default = null){
        return isset($array[$key]) ? $array[$key] : $default;
    }

    public static function idFromCanvasId(int $canvas_id) : int {
        return $canvas_id + self::BASE_ID;
    }

    private function createId() : int {
        $unique = (int)(time().mt_rand(1000, 9999));
        return self::BASE_ID + $unique;
    }

    private function addAsModuleItem(array $module_item, $type){
        $item = [
            'id' => $this->idFromCanvasId($module_item['canvas_id']),
            'canvas_id' => $module_item['canvas_id'],
            'course_id' => $this->course->id,
            'module_id' => $module_item['module_id'],
            'content_type' => $type,
            'workflow_state' => $this->getValue('workflow_state', $module_item, 'active'),
            'position' => $module_item['position'],
            'title' => $module_item['title'],
            'url' => $this->getValue('url', $module_item, null)
        ];
        if($type == 'WikiPage') $item['wiki_page_id'] = $module_item['item_id'];
        if($type == 'Quiz') $item['quiz_id'] = $module_item['item_id'];
        if($type == 'Attachment') $item['file_id'] = $module_item['item_id'];
        if($type == 'DiscussionTopic') $item['discussion_topic_id'] = $module_item['item_id'];
        if($type == 'Assignment') $item['assignment_id'] = $module_item['item_id'];
        DB::table('module_item_dim')->insert($item);
    }

}
