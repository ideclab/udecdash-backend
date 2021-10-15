<?php
namespace App\Classes\InteractionsDiscovery;

use App\Classes\InteractionsDiscovery\Interaction;
use App\Classes\DataStructure\Identifier;
use Illuminate\Support\Facades\DB;

class ModuleItemDiscovery {
    private $columns = [
        Interaction::WIKI_PAGE => 'wiki_page_id',
        Interaction::QUIZ => 'quiz_id',
        Interaction::ATTACHMENT => 'file_id',
        Interaction::DISCUSSION_TOPIC => 'discussion_topic_id',
        Interaction::ASSIGNMENT => 'assignment_id'
    ];

    private $tables = [
        Interaction::WIKI_PAGE => 'wiki_page_dim',
        Interaction::QUIZ => 'quiz_dim',
        Interaction::ATTACHMENT => 'file_dim',
        Interaction::DISCUSSION_TOPIC => 'discussion_topic_dim',
        Interaction::ASSIGNMENT => 'assignment_dim'
    ];

    public function __construct(Identifier $courseIdentifier, object $log, string $interaction_type){
        $this->courseIdentifier = $courseIdentifier;
        $this->log = $log;
        $this->module_item_canvas_id = null;
        $this->interaction_type = $interaction_type;
        $this->discovery();
    }

    private function discovery() : void {
        $regex_match = array();
        $pattern = "/(\/courses\/{$this->courseIdentifier->canvas_id}\/modules\/items\/)\K[^\/\?]+(\/|\?)??/";
        $isModuleItem = preg_match($pattern, $this->log->url, $regex_match, PREG_OFFSET_CAPTURE);
        if($isModuleItem && isset($regex_match[0][0]) && $this->isValidId($regex_match[0][0])){
            $this->module_item_canvas_id = $regex_match[0][0];
        }
    }

    public function isModuleItemUrl() : bool {
        return !empty($this->module_item_canvas_id);
    }

    public function getIdentifier() : ? Identifier {
        $identifier = null;
        $id = $this->getResourceId();
        $exists_table = in_array($this->interaction_type, array_keys($this->tables));
        if(!empty($id) && $exists_table){
            $table_name =  $this->tables[$this->interaction_type];
            $record = DB::table($table_name)->where('id', $id)->first();
            if(!empty($record)){
                $identifier = new Identifier();
                $identifier->id = $record->id;
                $identifier->canvas_id = $record->canvas_id;
            }
        }
        return $identifier;
    }

    private function getResourceId() : ? int {
        $id = null;
        $module_item = DB::table('module_item_dim')->where('canvas_id', $this->module_item_canvas_id)->first();
        $exists_column  = in_array($this->interaction_type, array_keys($this->columns));
        if(!empty($module_item) && $exists_column){
            $column = $this->columns[$this->interaction_type];
            $id = $module_item->$column;
        }
        return $id;
    }

    private function isValidId(mixed $id): bool {
        return is_numeric($id) && !str_contains($id, 'e') && !str_contains($id, 'E');
    }
}
