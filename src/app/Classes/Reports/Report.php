<?php
namespace App\Classes\Reports;

use App\Classes\DataStructure\Identifier;
use App\Classes\Course;
use App\Classes\DataStructure\GroupedInteractionBuilder;
use App\Classes\DataStructure\LoadFilter;
use App\Classes\ResourceType;
use App\Models\AttachmentInteraction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

abstract class Report {
    protected Course $course;
    protected LoadFilter $load_filter;
    protected array $interactions;
    protected Collection | array $results;

    function __construct(Course $course){
        $this->results = new Collection();
        $this->course = $course;
        $this->load_filter = $this->setLoadFilter();
        $this->loadInteractions();
        $this->build();
    }

    abstract protected function build();
    abstract protected function setLoadFilter();

    public function getResults() : Collection|array {
        return $this->cleanResults($this->results);
    }

    protected function cleanResults(Collection|array $result) : Collection|array {
        return $result;
    }

    protected function loadInteractions() : void {
        $this->interactions = [
            ResourceType::WIKI_PAGE => $this->getInteractions(ResourceType::WIKI_PAGE),
            ResourceType::QUIZ => $this->getInteractions(ResourceType::QUIZ),
            ResourceType::ATTACHMENT => $this->getInteractions(ResourceType::ATTACHMENT),
            ResourceType::DISCUSSION_TOPIC => $this->getInteractions(ResourceType::DISCUSSION_TOPIC),
            ResourceType::ASSIGNMENT => $this->getInteractions(ResourceType::ASSIGNMENT),
            ResourceType::EXTERNAL_URL => $this->getInteractions(ResourceType::EXTERNAL_URL),
            ResourceType::CONTEXT_EXTERNAL_TOOL => $this->getInteractions(ResourceType::CONTEXT_EXTERNAL_TOOL)
        ];
    }

    protected function getInteractions(string $type) : Collection {
        $class_name = "App\\Models\\{$type}Interaction";
        $model = new $class_name();
        if($this->load_filter->load_grouped){
            $interactions = $this->getGroupedInteractions($model->getTable());
        }else{
            $interactions = $this->getAllInteractions($model->getTable());
        }
        $interactions = $this->groupInteractionCollection($interactions);
        return $interactions;
    }

    protected function getGroupedInteractions(string $table_name) : Collection {
        $builder = $this->getGroupedInteractionBuilder();
        return DB::table($table_name)
            ->select($builder->columns)
            ->where([['course_id', $this->course->getId()],
                     ['viewed','>=', $this->load_filter->from],
                     ['viewed','<=', $this->load_filter->until]])
            ->groupBy($builder->group_by)
            ->get();
    }

    protected function getGroupedInteractionBuilder() : GroupedInteractionBuilder {
        $builder = new GroupedInteractionBuilder();
        $builder->columns = ['course_id','user_id', 'item_id','item_canvas_id',
        DB::raw('min(viewed) as viewed')];
        $builder->group_by = ['course_id','user_id', 'item_id', 'item_canvas_id'];
        return $builder;
    }

    protected function getAllInteractions(string $table_name) : Collection {
        return DB::table($table_name)
            ->where([['course_id', $this->course->getId()],
                     ['viewed','>=', $this->load_filter->from],
                     ['viewed','<=', $this->load_filter->until]])
            ->get();
    }

    protected function groupInteractionCollection(Collection $interactions) : Collection {
        if(!is_null($this->load_filter->columns_to_group_interactions)){
            $interactions = $interactions->groupBy($this->load_filter->columns_to_group_interactions);
        }
        return $interactions;
    }

    protected function average(int $counted, int $count_all) : float {
        $result = 0;
        if($count_all == 0){
            return $result;
        }
        $result = $counted / $count_all;
        return number_format($result, 2);
    }

    protected function percentage(int $counted, int $count_all) : float {
        $result = 0;
        if($count_all == 0){
            return $result;
        }
        $result = ($counted * 100) / $count_all;
        return number_format($result, 2);
    }

    public static function clearCache(int $course_canvas_id){
        $key = static::getCacheIdentifier($course_canvas_id);
        Log::debug('[Report::class] [clearCache] generated key', ["key" => $key]);
        Log::debug('[Report::class] [clearCache] Has previous cache', ["exists" => Cache::has($key)]);
        Cache::forget($key);
        Log::debug('[Report::class] [clearCache] Cache removed', ["exists" => Cache::has($key)]);
    }

    public static function getCacheIdentifier(int $course_canvas_id){
        $code = static::defaultCacheCode();
        return (int) "{$course_canvas_id}{$code}";
    }
}
