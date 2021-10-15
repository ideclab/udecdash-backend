<?php
namespace App\Classes\InteractionsDiscovery;

use Illuminate\Support\Facades\Log;
use App\Classes\InteractionsDiscovery\Interaction;
use App\Classes\DataStructure\Identifier;
use Illuminate\Support\Facades\DB;

class InteractionResolver {

    public function __construct(\stdClass $log, Identifier $courseIdentifier){
        Log::debug('[InteractionResolver::class] [construct]');
        $this->log = $log;
        $this->courseIdentifier = $courseIdentifier;
    }

    protected function interactionResolvers() : array {
        $interactionResolvers = [
            Interaction::WIKI_PAGE => "wikiPageResolver",
            Interaction::ATTACHMENT => "attachmentResolver",
            Interaction::QUIZ => "quizResolver",
            Interaction::DISCUSSION_TOPIC => "discussionTopicResolver",
            Interaction::ASSIGNMENT => "assignmentResolver",
            Interaction::EXTERNAL_URL => "externalUrlResolver",
            Interaction::CONTEXT_EXTERNAL_TOOL => "contextExternalToolResolver"

        ];
        return $interactionResolvers;
    }

    protected function wikiPageResolver() : ?Identifier {
        $identifier = null;
        $regex_match = array();
        $pattern = "/(\/courses\/{$this->courseIdentifier->canvas_id}\/pages\/)\K[^\/\?]+(\/|\?)??/";
        $isPage = preg_match($pattern, $this->log->url, $regex_match, PREG_OFFSET_CAPTURE);
        if($isPage && isset($regex_match[0][0])){
            $slug = $regex_match[0][0];
            $identifier = $this->getPageIdentifierFromSlug($slug);
            Log::debug('[InteractionDiscovery::class] [wikiPageResolver] Page interaction created =>', [$slug, $this->log->url, $identifier]);
        }
        if(empty($identifier)){
            $identifier = $this->identifierFromModuleItemUrl(Interaction::WIKI_PAGE);
        }
        return $identifier;
    }

    private function getPageIdentifierFromSlug(string $slug) : ?Identifier {
        $identifier = null;
        Log::debug("[InteractionDiscovery::class] [getPageIdentifierFromSlug] Page Type found. Extracted slug =>", [$slug]);
        $wiki_page = DB::table('wiki_page_dim')
                ->whereRaw('id in (SELECT wiki_page_id FROM module_item_dim
                            WHERE course_id = ? AND wiki_page_id IS NOT NULL)',
                            [$this->courseIdentifier->id])
                ->where('url', $slug)->get();
        if($wiki_page->isEmpty()){
            $this->interaction->error_label = "wiki_page_not_found";
            $this->interaction->error_message = "wiki page not found inside wiki_page_dim for the slug [{$slug}]";
            Log::notice("[InteractionDiscovery::class] [getPageIdentifierFromSlug] Wiki page not found inside wiki_page_dim for the slug =>", [$slug, $this->courseIdentifier]);
        }else if ($wiki_page->count() > 1){
            $this->interaction->error_label = "multiple_wiki_pages";
            $this->interaction->error_message = "Multiple wiki pages finded inside wiki_page_dim for the slug [{$slug}]";
            Log::notice("[InteractionDiscovery::class] [getPageIdentifierFromSlug] Multiple wiki pages finded inside wiki_page_dim for the slug =>", [$slug, $this->courseIdentifier]);
        }else{
            Log::debug("[InteractionDiscovery::class] [getPageIdentifierFromSlug] Wiki page found for the slug =>", [$slug, $wiki_page]);
            $identifier = new Identifier();
            $identifier->id = $wiki_page[0]->id;
            $identifier->canvas_id = $wiki_page[0]->canvas_id;
        }
        return $identifier;
    }

    protected function contextExternalToolResolver() : ?Identifier {
        $identifier = null;
        $regex_match = array();
        $pattern = "/(\/courses\/{$this->courseIdentifier->canvas_id}\/external_tools\/)\K[^\/\?]+(\/|\?)??/";
        $isExternalTool = preg_match($pattern, $this->log->url, $regex_match, PREG_OFFSET_CAPTURE);
        if($isExternalTool && isset($regex_match[0][0]) && $this->isValidId($regex_match[0][0])){
            $canvas_id = $regex_match[0][0];
            $identifier = $this->getIdentifierFromCanvasId($canvas_id, 'external_tool_activation_dim');
            Log::debug('[InteractionDiscovery::class] [contextExternalToolResolver] Context external tool interaction created =>', [$identifier, $this->log->url, $identifier]);
        }
        return $identifier;
    }

    protected function externalUrlResolver() : ?Identifier {
        $identifier = null;
        $regex_match = array();
        $pattern = "/(\/courses\/{$this->courseIdentifier->canvas_id}\/modules\/items\/)\K[^\/\?]+(\/|\?)??/";
        $isModuleItem = preg_match($pattern, $this->log->url, $regex_match, PREG_OFFSET_CAPTURE);
        if($isModuleItem && isset($regex_match[0][0]) && $this->isValidId($regex_match[0][0])){
            $module_item_canvas_id = $regex_match[0][0];
            $type = $this->findResourceType($module_item_canvas_id);
            if($type == Interaction::EXTERNAL_URL){
                $identifier = $this->getModuletItemIdentifier($module_item_canvas_id);
                Log::debug('[InteractionDiscovery::class] [externalUrlResolver] External URL interaction created (module_item_id)=>', [$module_item_canvas_id, $this->log->url, $identifier]);
            }
        }
        return $identifier;
    }

    private function getModuletItemIdentifier(int $module_item_canvas_id) : ? Identifier {
        $identifier = null;
        $record = DB::table('module_item_dim')->where('canvas_id', $module_item_canvas_id)->first();
        if(!empty($record)){
            $identifier = new Identifier();
            $identifier->id = $record->id;
            $identifier->canvas_id = $record->canvas_id;
        }else{
            Log::notice("[InteractionDiscovery::class] [getModuletItemIdentifier] Not found record for the module item canvas id=>", [$module_item_canvas_id]);
        }
        return $identifier;
    }

    private function findResourceType(int $module_item_canvas_id) : ? string {
        $type = null;
        $record = DB::table('module_item_dim')->where('canvas_id', $module_item_canvas_id)->first();
        if(!empty($record) && isset($record->content_type)){
            $type = $record->content_type;
        }else{
            Log::notice("[InteractionDiscovery::class] [findResourceType] Not found record for the module item canvas id=>", [$module_item_canvas_id]);
        }
        return $type;
    }

    protected function attachmentResolver() : ?Identifier {
        $identifier = null;
        $regex_match = array();
        $pattern = "/(\/courses\/{$this->courseIdentifier->canvas_id}\/files\/)\K[^\/\?]+(\/|\?)??/";
        $isFile = preg_match($pattern, $this->log->url, $regex_match, PREG_OFFSET_CAPTURE);
        if($isFile && isset($regex_match[0][0]) && $this->isValidId($regex_match[0][0])){
            $canvas_id = $regex_match[0][0];
            $identifier = $this->getIdentifierFromCanvasId($canvas_id, 'file_dim');
            Log::debug('[InteractionDiscovery::class] [attachmentResolver] Attachment interaction created =>', [$canvas_id, $this->log->url, $identifier]);
        }
        if(empty($identifier)){
            $identifier = $this->identifierFromModuleItemUrl(Interaction::ATTACHMENT);
        }
        return $identifier;
    }

    protected function assignmentResolver() : ?Identifier {
        $identifier = null;
        if(!empty($this->log->assignment_id)){
            $identifier = $this->getIdentifierFromId($this->log->assignment_id, 'assignment_dim');
            Log::debug('[InteractionDiscovery::class] [assignmentResolver] Assignment interaction created =>', [$identifier, $this->log->url, $identifier]);
        }else{
            $regex_match = array();
            $pattern = "/(\/courses\/{$this->courseIdentifier->canvas_id}\/assignments\/)\K[^\/\?]+(\/|\?)??/";
            $isDiscussion = preg_match($pattern, $this->log->url, $regex_match, PREG_OFFSET_CAPTURE);
            if($isDiscussion && isset($regex_match[0][0]) && $this->isValidId($regex_match[0][0])){
                $canvas_id = $regex_match[0][0];
                $identifier = $this->getIdentifierFromCanvasId($canvas_id, 'assignment_dim');
                Log::debug('[InteractionDiscovery::class] [assignmentResolver] Assignment interaction created =>', [$identifier, $this->log->url, $identifier]);
            }
        }
        if(empty($identifier)){
            $identifier = $this->identifierFromModuleItemUrl(Interaction::ASSIGNMENT);
        }
        return $identifier;
    }

    protected function quizResolver() : ?Identifier {
        $identifier = null;
        if(!empty($this->log->quiz_id)){
            $identifier = $this->getIdentifierFromId($this->log->quiz_id, 'quiz_dim');
            Log::debug('[InteractionDiscovery::class] [quizResolver] Quiz interaction created =>', [$identifier, $this->log->url, $identifier]);
        }else{
            $regex_match = array();
            $pattern = "/(\/courses\/{$this->courseIdentifier->canvas_id}\/quizzes\/)\K[^\/\?]+(\/|\?)??/";
            $isDiscussion = preg_match($pattern, $this->log->url, $regex_match, PREG_OFFSET_CAPTURE);
            if($isDiscussion && isset($regex_match[0][0]) && $this->isValidId($regex_match[0][0])){
                $canvas_id = $regex_match[0][0];
                $identifier = $this->getIdentifierFromCanvasId($canvas_id, 'quiz_dim');
                Log::debug('[InteractionDiscovery::class] [quizResolver] Quiz interaction created =>', [$identifier, $this->log->url, $identifier]);
            }
        }
        if(empty($identifier)){
            $identifier = $this->identifierFromModuleItemUrl(Interaction::QUIZ);
        }
        return $identifier;
    }

    protected function discussionTopicResolver() : ?Identifier {
        $identifier = null;
        if(!empty($this->log->discussion_id)){
            $identifier = $this->getIdentifierFromId($this->log->discussion_id, 'discussion_topic_dim');
            Log::debug('[InteractionDiscovery::class] [discussionTopicResolver] Discussion interaction created =>', [$identifier, $this->log->url, $identifier]);
        }else{
            $regex_match = array();
            $pattern = "/(\/courses\/{$this->courseIdentifier->canvas_id}\/discussion_topics\/)\K[^\/\?]+(\/|\?)??/";
            $isDiscussion = preg_match($pattern, $this->log->url, $regex_match, PREG_OFFSET_CAPTURE);
            if($isDiscussion && isset($regex_match[0][0]) && $this->isValidId($regex_match[0][0])){
                $canvas_id = $regex_match[0][0];
                $identifier = $this->getIdentifierFromCanvasId($canvas_id, 'discussion_topic_dim');
                Log::debug('[InteractionDiscovery::class] [discussionTopicResolver] Discussion interaction created =>', [$identifier, $this->log->url, $identifier]);
            }
        }
        if(empty($identifier)){
            $identifier = $this->identifierFromModuleItemUrl(Interaction::DISCUSSION_TOPIC);
        }
        return $identifier;
    }

    private function getIdentifierFromId(int $id, string $table_name) : ? Identifier {
        $identifier = null;
        $record = DB::table($table_name)->where('id', $id)->first();
        if(!empty($record)){
            $identifier = new Identifier();
            $identifier->id = $record->id;
            $identifier->canvas_id = $record->canvas_id;
        }else{
            $this->interaction->error_label = "record_not_found_$table_name";
            $this->interaction->error_message = "Record not found for the id in the table $table_name [{$id}]";
            Log::notice("[InteractionDiscovery::class] [getFileIdentifierFromCanvasId] Record not found for the id in the table $table_name =>", [$id, $this->log->url]);
        }
        return $identifier;
    }

    private function getIdentifierFromCanvasId(int $canvas_id, string $table_name){
        $identifier = null;
        $record = DB::table($table_name)->where('canvas_id', $canvas_id)->first();
        if(!empty($record)){
            $identifier = new Identifier();
            $identifier->id = $record->id;
            $identifier->canvas_id = $record->canvas_id;
        }else{
            $this->interaction->error_label = "record_not_found_$table_name";
            $this->interaction->error_message = "Record not found for the canvas_id in the table $table_name [{$canvas_id}]";
            Log::notice("[InteractionDiscovery::class] [getFileIdentifierFromCanvasId] Record not found for the canvas_id in the table $table_name =>", [$canvas_id, $this->log->url]);
        }
        return $identifier;
    }

    private function identifierFromModuleItemUrl(string $interaction_type) : ? Identifier {
        $identifier = null;
        $moduleItemDiscovery = new ModuleItemDiscovery($this->courseIdentifier, $this->log, $interaction_type);
        if($moduleItemDiscovery->isModuleItemUrl()){
            $identifier = $moduleItemDiscovery->getIdentifier();
        }
        Log::debug("[InteractionDiscovery::class] [identifierFromModuleItemUrl] Comprobing interaction type from item module id url =>", [$interaction_type, $identifier]);
        return $identifier;
    }

    private function isValidId(mixed $id): bool {
        return is_numeric($id) && !str_contains($id, 'e') && !str_contains($id, 'E');
    }

}
