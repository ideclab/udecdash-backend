<?php

namespace App\Jobs;

use App\Http\Controllers\ReportsManagementController;
use App\Mail\CourseUpdatedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\CourseProcessingRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ProcessCourseInteractions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $backoff = 0;
    public $tries = 3;
    public $maxExceptions = 2;
    public $timeout = 22000;

    protected $request;

    public function __construct(CourseProcessingRequest $request){
        Log::debug('[ProcessCourseInteractions::class] [construct] recived => ', [$request]);
        $this->request = $request;
    }

    public function handle() {
        $start = time();
        Log::debug('[ProcessCourseInteractions::class] [handle] recovery course processiong request => ', [$this->request]);
        $processed_logs = $this->request->processInteractions();
        $this->rebuildReports();
        $this->request->process_status = 'FINISHED';
        $this->request->processing_time = $this->secondsToNow($start);
        $this->request->processed_logs = $processed_logs;
        $this->request->finished_at = Carbon::now();
        $this->request->save();
        $this->notifyCompletion();
    }

    public function failed(Throwable $exception){
        Log::debug('[ProcessCourseInteractions::class] [failed] job failed => ', [$this->request]);
        $this->request->process_status = 'FAILED';
        $this->request->failed_motive = $exception->getMessage();
        $this->request->save();
    }

    private function secondsToNow(int $start) : int {
        $now = time();
        $difference = $now - $start;
        return $difference;
    }

    private function rebuildReports() : void {
        (new ReportsManagementController())->rebuildReports($this->request->course_canvas_id);
    }

    private function notifyCompletion() : void {
        $email = User::emailFromId($this->request->user_id);
        if(env('NOTIFICATIONS_DEBUG_MODE', true)){
            $email = env('NOTIFICATIONS_DEBUG_EMAIL', null);
        }
        if(!empty($email)){
            $author = DB::table('user_dim')->where('id', $this->request->user_id)->first();
            $course = DB::table('course_dim')->where('id', $this->request->course_id)->first();
            try{
                $mail = new CourseUpdatedMail($author->name, $course->name);
                Mail::to($email)->send($mail);
            }catch(\Exception $e){
            }
        }
    }
}
