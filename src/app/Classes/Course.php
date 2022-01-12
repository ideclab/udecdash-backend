<?php
namespace App\Classes;

use App\Classes\DataStructure\Identifier;
use App\Classes\Helpers\CourseNameFormatter;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Classes\Membership;
use Carbon\Carbon;
use DeepCopy\DeepCopy;

class Course {
    public const FAKE_MODULE_ID = -1;
    private object $settings;
    private Identifier $identifier;
    private Collection $structure;
    private Collection $modules;
    private Collection $resources;
    private Membership $membership;

    function __construct(int $canvas_id){
        $this->init($canvas_id);
        $this->setModules();
        $this->setResources();
        $this->setStructure();
        if(!$this->fakeModuleHasResources()){
            $this->removeFakeModule();
        }
        $this->membership = new Membership($this->identifier);
    }

    private function init(int $canvas_id) : void {
        $identifier = null;
        $course = DB::table('course_dim')->where('canvas_id', $canvas_id)->first();
        if(!empty($course)){
            $identifier = new Identifier();
            $identifier->id = $course->id;
            $identifier->canvas_id = $course->canvas_id;
            $this->identifier = $identifier;
            $this->settings = $course;
        }else{
            abort(400, "Course not found from the canvas_id $canvas_id");
        }
    }

    public static function getLastUpdateRequest(array $ids, array $columns = []) : Collection {
        if(empty($columns)){
            $columns = ['id','process_status','course_canvas_id', 'created_at', 'finished_at','cache_expires'];
        }
        $status = array();
        $last_records_id = DB::table('course_processing_requests')
        ->select(DB::raw('max(id) as id'))
        ->whereIn('course_canvas_id', $ids)
        ->groupBy('course_canvas_id')
        ->pluck('id')->toArray();
        $courses = DB::table('course_processing_requests')
        ->select($columns)
        ->whereIn('id', $last_records_id)
        ->get();
        return $courses;
    }

    public function getId(){
        return $this->identifier->id;
    }

    public function getCanvasId(){
        return $this->identifier->canvas_id;
    }

    public function getName() : string {
        return $this->settings->name;
    }

    public function getInformation() : object {
        $formatter = new CourseNameFormatter($this->settings->name);
        return $formatter->getSummary();
    }

    public function getCode() : string {
        return $this->settings->code;
    }

    public function getWorkflowState() : string {
        return $this->settings->workflow_state;
    }

    public function getModules() : Collection {
        $copier = new DeepCopy();
        return $copier->copy($this->modules)->values();
    }

    public function getResources() : Collection {
        $copier = new DeepCopy();
        return $copier->copy($this->resources);
    }

    public function getStructure() : Collection {
        $copier = new DeepCopy();
        return $copier->copy($this->structure)->values();
    }

    public function getMembership(){
        return $this->membership;
    }

    public function setModules() : void {
        $this->modules = DB::table('module_dim')
        ->select('id','canvas_id','name','workflow_state','position')
        ->where('course_id', $this->identifier->id)
        ->whereIn('workflow_state', ['active','unpublished'])
        ->orderBy('position')->get();
        $this->addFakeModule();
    }

    /* this module is added for group all resources that was created without modules */
    private function addFakeModule() : void {
        $module = new \StdClass();
        $module->id = static::FAKE_MODULE_ID;
        $module->canvas_id = static::FAKE_MODULE_ID;
        $module->name = "Recursos fuera de un módulo";
        $module->workflow_state = "active";
        $module->position = 999;
        $this->modules->add($module);
    }

    private function setResources() : void {
        $resources = new Collection();
        $resources = $resources->merge($this->getWikipages());
        $resources = $resources->merge($this->getQuizzes());
        $resources = $resources->merge($this->getAttachments());
        $resources = $resources->merge($this->getDiscussionTopics());
        $resources = $resources->merge($this->getAssignments());
        $resources = $resources->merge($this->getExternalUrls());
        $resources = $resources->merge($this->getExternalTools());
        $this->resources = $resources;
    }

    private function setStructure() : void {
        $modules = $this->getModules();
        $resources = $this->getResources();
        foreach ($modules as $module){
            $module->resources = $resources->filter(function ($resource) use ($module) {
                return $resource->module_id == $module->id;
            })->values();
            $module->resources = $module->resources->sortBy('position')->values();
        }
        $this->structure = $modules;
    }

    public static function findIdentifierFromCanvasId(int $canvas_id) : ? Identifier {
        $identifier = null;
        $course = DB::table('course_dim')->where('canvas_id', $canvas_id)->first();
        if(!empty($course)){
            $identifier = new Identifier();
            $identifier->id = $course->id;
            $identifier->canvas_id = $course->canvas_id;
        }else{
            Log::notice("Course identifier can't generate for the canvas id {$canvas_id}");
        }
        return $identifier;
    }

    private function reorderResourcesInModules(Collection $resources,
    string $content_type, string $resource_name_column) : Collection {
        foreach($resources as $resource){
            if($this->notHasModuleAssigned($resource) || $this->notHasModule($resource)){
                $resource->resource_name = $resource->$resource_name_column;
                $resource->module_id = static::FAKE_MODULE_ID;
                $resource->position = (int) Carbon::parse($resource->created_at)->format('U');
                $resource->module_item_canvas_id = null;
                $resource->module_item_workflow_state = null;
            }
            $resource->content_type = $content_type;
        }
        return $resources;
    }

    private function notHasModuleAssigned(object $resource) : bool{
        return is_null($resource->module_id);
    }

    private function notHasModule(object $resource) : bool {
        $has_module = false;
        foreach($this->modules as $module){
            if($module->id === $resource->module_id){
                $has_module = true;
                break;
            }
        }
        return !$has_module;
    }

    public function getWikipages() : Collection {
        $wiki_pages = DB::table('wiki_page_fact')
        ->select('wiki_page_dim.id', 'wiki_page_dim.canvas_id','module_item_dim.title as resource_name',
        'wiki_page_dim.title', 'wiki_page_dim.created_at', 'wiki_page_dim.workflow_state',
        'module_item_dim.position', 'module_item_dim.module_id as module_id',
        'module_item_dim.canvas_id as module_item_canvas_id',
        'module_item_dim.workflow_state as module_item_workflow_state')
        ->where([['wiki_page_fact.parent_course_id', $this->getId()],
                ['wiki_page_dim.workflow_state','<>','deleted']])
        ->join('wiki_page_dim', 'wiki_page_fact.wiki_page_id','wiki_page_dim.id')
        ->leftJoin('module_item_dim', 'wiki_page_dim.id','module_item_dim.wiki_page_id')
        ->get();
        $this->reorderResourcesInModules($wiki_pages, ResourceType::WIKI_PAGE, 'title');
        return $wiki_pages;
    }

    public function getQuizzes() : Collection {
        $quizzes = DB::table('quiz_dim')
        ->select('quiz_dim.id','quiz_dim.canvas_id','quiz_dim.name', 'quiz_dim.workflow_state',
        'quiz_dim.created_at','module_item_dim.module_id as module_id','module_item_dim.position',
        'quiz_dim.quiz_type','module_item_dim.title as resource_name',
        'module_item_dim.canvas_id as module_item_canvas_id',
        'module_item_dim.workflow_state as module_item_workflow_state')
        ->where([['quiz_dim.course_id', $this->getId()],
                 ['quiz_dim.workflow_state','<>','deleted']])
        ->leftJoin('module_item_dim', 'quiz_dim.id', 'module_item_dim.quiz_id')
        ->get();
        $this->reorderResourcesInModules($quizzes, ResourceType::QUIZ,'name');
        return $quizzes;
    }

    public function getAttachments() : Collection {
        $attachments = DB::table('module_item_dim')
        ->select('file_dim.id','file_dim.canvas_id', 'file_dim.display_name',
        'file_dim.content_type as mime_type', 'file_dim.created_at',
        'file_dim.file_state as workflow_state', 'module_item_dim.module_id as module_id',
        'module_item_dim.title as resource_name', 'module_item_dim.position',
        'module_item_dim.canvas_id as module_item_canvas_id',
        'module_item_dim.workflow_state as module_item_workflow_state')
        ->where([['module_item_dim.course_id', $this->getId()],
                 ['module_item_dim.workflow_state','<>','deleted'],
                 ['file_dim.file_state','<>','deleted']])
        ->whereNotNull('module_item_dim.file_id')
        ->join('file_dim', 'module_item_dim.file_id','file_dim.id')
        ->get();
        $this->reorderResourcesInModules($attachments, ResourceType::ATTACHMENT ,'display_name');
        return $attachments;
    }

    public function getDiscussionTopics() : Collection {
        $discussions_topic = DB::table('discussion_topic_dim')
        ->select('discussion_topic_dim.id','discussion_topic_dim.canvas_id','discussion_topic_dim.title',
        'discussion_topic_dim.workflow_state', 'discussion_topic_dim.created_at',
        'module_item_dim.title as resource_name', 'module_item_dim.position',
        'module_item_dim.module_id as module_id', 'module_item_dim.canvas_id as module_item_canvas_id',
        'module_item_dim.workflow_state as module_item_workflow_state')
        ->where([['discussion_topic_dim.course_id', $this->getId()],
                 ['discussion_topic_dim.workflow_state','<>','deleted']])
        ->leftJoin('module_item_dim', 'discussion_topic_dim.id', 'module_item_dim.discussion_topic_id')
        ->get();
        $this->reorderResourcesInModules($discussions_topic,ResourceType::DISCUSSION_TOPIC,'title');
        return $discussions_topic;
    }

    public function getAssignments() : Collection {
        $assignments = DB::table('assignment_dim')
        ->select('assignment_dim.id','assignment_dim.canvas_id', 'module_item_dim.title as resource_name',
        'assignment_dim.workflow_state', 'assignment_dim.created_at',
        'module_item_dim.module_id as module_id', 'module_item_dim.position', 'assignment_dim.title',
        'module_item_dim.canvas_id as module_item_canvas_id',
        'module_item_dim.workflow_state as module_item_workflow_state')
        ->where('assignment_dim.course_id', $this->getId())
        ->whereNotIn('assignment_dim.workflow_state', ['deleted', 'failed_to_duplicate'])
        ->whereNotIn('assignment_dim.submission_types',['not_graded','wiki_page', 'online_quiz',
        'none', 'discussion_topic','external_tool'])
        ->leftJoin('module_item_dim', 'assignment_dim.id', 'module_item_dim.assignment_id')
        ->get();
        $this->reorderResourcesInModules($assignments, ResourceType::ASSIGNMENT, 'title');
        return $assignments;
    }

    public function getExternalUrls() : Collection {
        $external_urls = DB::table('module_item_dim')
        ->select('id','canvas_id','workflow_state', 'title as resource_name',
        'url', 'module_id as module_id','module_item_dim.canvas_id as module_item_canvas_id',
        'module_item_dim.workflow_state as module_item_workflow_state', 'module_item_dim.created_at')
        ->where([['course_id', $this->getId()],
                 ['content_type','ExternalUrl'],
                 ['workflow_state','<>','deleted']])
        ->get();
        $this->reorderResourcesInModules($external_urls, ResourceType::EXTERNAL_URL, 'resource_name');
        return $external_urls;
    }

    public function getExternalTools() : Collection {
        $external_tools = DB::table('module_item_dim')
        ->select('id','canvas_id','workflow_state', 'title as resource_name',
        'url', 'module_id as module_id', 'module_item_dim.canvas_id as module_item_canvas_id',
        'module_item_dim.workflow_state as module_item_workflow_state', 'module_item_dim.created_at')
        ->where([['course_id', $this->getId()],
                 ['content_type','ContextExternalTool'],
                 ['workflow_state','<>','deleted']])
        ->get();
        $this->reorderResourcesInModules($external_tools, ResourceType::CONTEXT_EXTERNAL_TOOL, 'resource_name');
        return $external_tools;
    }

    public function getQuizzesWithAssignedDate() : Collection {
        $quizzes = DB::table('quiz_dim')
        ->select('id','canvas_id', 'workflow_state', 'created_at', 'quiz_type',
        'unlock_at','due_at', 'lock_at', 'name as resource_name' ,
        DB::raw('CASE WHEN assignment_id is null THEN false else true END as is_evaluated'))
        ->where([['course_id', $this->getId()], ['workflow_state','<>','deleted']])
        ->whereNotNull(['unlock_at', 'lock_at'])
        ->get();
        return $quizzes;
    }

    private function removeFakeModule() : void {
        $fake_module_position = $this->getFakeModulePosition();
        unset($this->modules[$fake_module_position]);
        unset($this->structure[$fake_module_position]);
    }

    private function fakeModuleHasResources() : bool {
        $module = $this->structure[$this->getFakeModulePosition()];
        return $module->resources->isNotEmpty();
    }

    private function getFakeModulePosition() : int {
        return (int) (count($this->modules) - 1);
    }
}
