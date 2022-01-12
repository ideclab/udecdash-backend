<?php
namespace App\Classes;

use App\Classes\Helpers\FriendlyTime;
use App\Jobs\ProcessCourseInteractions;
use App\Models\CourseProcessingRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueueManager {
    const FIRST_QUEUE = 'first_queue';
    const SECOND_QUEUE = 'second_queue';
    const THIRD_QUEUE = 'third_queue';
    const FOURTH_QUEUE = 'fourth_queue';
    const FIFTH_QUEUE = 'fifth_queue';
    const SIXTH_QUEUE = 'sixth_queue';
    const SEVENTH_QUEUE = 'seventh_queue';
    const EIGHTH_QUEUE = 'eighth_queue';
    const NINETH_QUEUE = 'nineth_queue';
    const TENTH_QUEUE = 'tenth_queue';
    private int $first_queue_limit;
    private int $second_queue_limit;
    private int $third_queue_limit;
    private int $fourth_queue_limit;
    private int $fifth_queue_limit;
    private int $sixth_queue_limit;
    private int $seventh_queue_limit;
    private int $eighth_queue_limit;
    private int $nineth_queue_limit;
    private int $tenth_queue_limit;
    private int $course_id;
    private CourseProcessingRequest $request;
    private int $members_count;

    public function __construct(CourseProcessingRequest $request, int $course_id){
        $this->first_queue_limit = env('MAX_MEMBERS_FIRST_QUEUE', 12);
        $this->second_queue_limit = env('MAX_MEMBERS_SECOND_QUEUE', 20);
        $this->third_queue_limit = env('MAX_MEMBERS_THIRD_QUEUE', 30);
        $this->fourth_queue_limit = env('MAX_MEMBERS_FOURTH_QUEUE', 40);
        $this->fifth_queue_limit = env('MAX_MEMBERS_FIFTH_QUEUE', 50);
        $this->sixth_queue_limit = env('MAX_MEMBERS_SIXTH_QUEUE', 60);
        $this->seventh_queue_limit = env('MAX_MEMBERS_SEVENTH_QUEUE', 70);
        $this->eighth_queue_limit = env('MAX_MEMBERS_EIGHTH_QUEUE', 85);
        $this->nineth_queue_limit = env('MAX_MEMBERS_NINETH_QUEUE', 110);
        $this->tenth_queue_limit = env('MAX_MEMBERS_TENTH_QUEUE', 3000);
        $this->course_id = $course_id;
        $this->request = $request;
        $this->members_count = Membership::countAll($this->course_id);
    }

    public static function hasPendingJob(int $course_canvas_id) : bool {
        $previous_job_count = DB::table('course_processing_requests')
        ->where('course_canvas_id', $course_canvas_id)
        ->whereNull('finished_at')->count();
        return $previous_job_count > 0;
    }

    public static function wasProcessedRecently(int $course_canvas_id) : bool {
        $hours_limit = env('COURSE_UPDATE_HOURS_LIMIT', 24);
        $from_time = (Carbon::now())->subHours($hours_limit);
        $previous_job_processed = DB::table('course_processing_requests')
            ->where('course_canvas_id', $course_canvas_id)
            ->where('finished_at', '>=', $from_time)
            ->count();
        return $previous_job_processed > 0;
    }

    public function add() : bool {
        $this->setRequestCacheExpiration();
        $added = false;
        $queue = $this->getQueue();
        if(!is_null($queue)){
            $added = true;
            ProcessCourseInteractions::dispatch($this->request)->onQueue($queue);
            $this->updateCourseProcessingRequest();
        }
        return $added;
    }

    private function setRequestCacheExpiration() : void {
        $seconds_to_expire = (int) env('CACHE_LIFETIME', 0);
        $expires_at = Carbon::now('America/Santiago')->addSeconds($seconds_to_expire);
        $this->request->cache_expires = $expires_at;
    }

    public function getEstimatedTime() : string {
        $seconds_average = 0;
        $records_for_calculate = 30;
        $taked_time = DB::table('course_processing_requests')
        ->select(DB::raw('EXTRACT(EPOCH FROM (finished_at - created_at)) as time'))
        ->where('queue_assigned', $this->getQueue())
        ->whereNotNull('finished_at')
        ->orderBy('id', 'DESC')
        ->limit($records_for_calculate)
        ->pluck('time')
        ->toArray();
        if(!empty($taked_time)){
            $seconds_average = array_sum($taked_time) / count($taked_time);
        }
        return FriendlyTime::fromSeconds($seconds_average);
    }

    private function updateCourseProcessingRequest() : void {
        $this->request->queue_assigned = $this->getQueue();
        $this->request->members_count = $this->members_count;
        $this->request->update();
    }

    private function getQueue() : ?string {
        $queue = null;
        if($this->isFirstQueue()){
            $queue = self::FIRST_QUEUE;
        }else if($this->isSecondQueue()){
            $queue = self::SECOND_QUEUE;
        }else if($this->isThirdQueue()){
            $queue = self::THIRD_QUEUE;
        }else if($this->isFourthQueue()){
            $queue = self::FOURTH_QUEUE;
        }else if($this->isFifthQueue()){
            $queue = self::FIFTH_QUEUE;
        }else if($this->isSixthQueue()){
            $queue = self::SIXTH_QUEUE;
        }else if($this->isSeventhQueue()){
            $queue = self::SEVENTH_QUEUE;
        }else if($this->isEighthQueue()){
            $queue = self::EIGHTH_QUEUE;
        }else if($this->isNinethQueue()){
            $queue = self::NINETH_QUEUE;
        }else if($this->isTenthQueue()){
            $queue = self::TENTH_QUEUE;
        }
        Log::debug('[QueueManager] [getQueue] Cola asignada para procesar el curso.',
                ['course_id' => $this->course_id, 'members' => $this->members_count, 'queue' => $queue]);
        return $queue;
    }

    private function isFirstQueue() : bool {
        return $this->first_queue_limit > 0 && $this->members_count <= $this->first_queue_limit;
    }

    private function isSecondQueue() : bool {
        return $this->second_queue_limit > 0 && $this->members_count <= $this->second_queue_limit;
    }

    private function isThirdQueue() : bool {
        return $this->third_queue_limit > 0 && $this->members_count <= $this->third_queue_limit;
    }

    private function isFourthQueue() : bool {
        return $this->fourth_queue_limit > 0 && $this->members_count <= $this->fourth_queue_limit;
    }

    private function isFifthQueue() : bool {
        return $this->fifth_queue_limit > 0 && $this->members_count <= $this->fifth_queue_limit;
    }

    private function isSixthQueue() : bool {
        return $this->sixth_queue_limit > 0 && $this->members_count <= $this->sixth_queue_limit;
    }

    private function isSeventhQueue() : bool {
        return $this->seventh_queue_limit > 0 && $this->members_count <= $this->seventh_queue_limit;
    }

    private function isEighthQueue() : bool {
        return $this->eighth_queue_limit > 0 && $this->members_count <= $this->eighth_queue_limit;
    }

    private function isNinethQueue() : bool {
        return $this->nineth_queue_limit > 0 && $this->members_count <= $this->nineth_queue_limit;
    }

    private function isTenthQueue() : bool {
        return $this->tenth_queue_limit > 0 && $this->members_count <= $this->tenth_queue_limit;
    }

}
