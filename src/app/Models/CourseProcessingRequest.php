<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Classes\InteractionsDiscovery\InteractionDiscovery;
use App\Classes\DataStructure\Identifier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CourseProcessingRequest extends Model
{
    protected $table = 'course_processing_requests';
    protected $fillable = ['id','course_id', 'course_canvas_id', 'processing_time',
    'process_status', 'members_count', 'queue_assigned', 'processed_logs', 'finished_at',
    'user_id', 'failed_motive'];

    public function processInteractions () : int {
        Log::debug('[CourseProcessingRequest::class] [orderInteractions]');
        $courseIdentifier = $this->getCourseIdentifier();
        $interactions_count = 0;
        $chunk_size = env('PROCESS_REQUETS_CHUNK_SIZE', 1000000);
        DB::table('requests')->select('local_id','id','timestamp','user_id','course_id',
        'quiz_id','discussion_id', 'assignment_id','url','user_agent')
        ->where('course_id', $this->course_id)->whereNotNull('user_id')
        ->orderBy('local_id')
        ->chunkById($chunk_size, function ($interactions) use ($interactions_count, $courseIdentifier) {
            foreach ($interactions as $interaction){
                new InteractionDiscovery($interaction, $courseIdentifier);
                $interactions_count++;
            }
        }, 'local_id');
        return $interactions_count;
    }

    private function getCourseIdentifier() : Identifier {
        $identifier = new Identifier();
        $identifier->id = $this->course_id;
        $identifier->canvas_id = $this->course_canvas_id;
        Log::debug('[CourseProcessingRequest::class] [getCourseIdentifier] identifier =>', [$identifier]);
        return $identifier;
    }

    public static function restoreUnprocessableInteractions(?int $course_id = null) : void {
        $records = [];
        if(!empty($course_id)){
            $records = DB::table('unprocessable_interactions')->where('course_id', $course_id)->get();
        }else{
            $records = DB::table('unprocessable_interactions')->get();
        }
        foreach($records as $key => $record){
            $log = (array) json_decode($record->log);
            DB::table('requests')->insert($log);
            DB::table('unprocessable_interactions')->where('id', $record->id)->delete();
        }
    }
}
