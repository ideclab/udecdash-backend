<?php
namespace App\Classes\DataStructure\Reports\EvaluationPanic;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class Activity {
    const LIMIT_DAYS = 2;
    public string $before_start;
    public string $before_end;
    public array $viewed_before;
    public string $after_start;
    public string $after_end;
    public array $viewed_after;
    public object $quiz;

    function __construct(object $quiz) {
        $this->viewed_before = array();
        $this->viewed_after = array();
        $this->quiz = $quiz;
        $this->assertQuizDates();
        $this->createBeforeDates();
        $this->createAfterDates();
        $this->before_end = $this->utcToChileanTz($this->quiz->unlock_at)->format('Y-m-d H:i:s');
        $this->after_start = $this->utcToChileanTz($this->quiz->lock_at)->format('Y-m-d H:i:s');;
    }

    public static function utcToChileanTz(string $date) : Carbon {
        $date = static::sanitizeDateLength($date);
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $date, 'UTC');
        $date->setTimezone('America/Santiago');
        return $date;
    }

    public static function sanitizeDateLength(string $date) : string {
        $date_length = 19;
        if(strlen($date) > $date_length){
            $date = substr($date, 0, $date_length);
        }
        return $date;
    }

    private function assertQuizDates(){
        if(empty($this->quiz->unlock_at) || empty($this->quiz->lock_at)){
            throw new \Exception("unlock_at and lock_at can't be empty");
        }
    }

    private function createBeforeDates(){
        $before = $this->utcToChileanTz($this->quiz->unlock_at);
        $this->viewed_before[$before->format('Y-m-d')] = array();
        for($i = 1; $i <= static::LIMIT_DAYS; $i++){
            $date = $before->sub(1, 'days');
            $this->viewed_before[$date->format('Y-m-d')] = array();
            if($i == static::LIMIT_DAYS){
                $this->before_start = $date->format('Y-m-d H:i:s');
            }
        }
    }

    private function createAfterDates(){
        $after = $this->utcToChileanTz($this->quiz->lock_at);
        $this->viewed_after[$after->format('Y-m-d')] = array();
        for($i = 1; $i <= static::LIMIT_DAYS; $i++){
            $date = $after->addDay(1);
            $this->viewed_after[$date->format('Y-m-d')] = array();
            if($i == static::LIMIT_DAYS){
                $this->after_end = $date->format('Y-m-d H:i:s');
            }
        }
    }
}
