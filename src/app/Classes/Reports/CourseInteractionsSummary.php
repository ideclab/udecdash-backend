<?php

namespace App\Classes\Reports;

use App\Classes\Course;
use App\Classes\DataStructure\GroupedInteractionBuilder;
use App\Classes\DataStructure\LoadFilter;
use App\Classes\Membership;
use App\Interfaces\Cacheable;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CourseInteractionsSummary extends Report {

    private Collection $merged_interactions;
    private int $year;
    private int $month;

    function __construct(Course $course, int $section_canvas_id, int $year, int $month){
        $this->merged_interactions = new Collection();
        $this->year = $year;
        $this->month = $month;
        $this->students_id = Membership::getStudentIds($course->getId(), $section_canvas_id);
        parent::__construct($course);
    }

    protected function setLoadFilter() : LoadFilter {
        $date = $this->getStartDate();
        $filter = new LoadFilter();
        $filter->load_grouped = true;
        $filter->from = $date;
        $filter->until = $date->addMonth();
        return $filter;
    }

    private function getStartDate() : Carbon {
        $month = $this->month < 10 ? "0{$this->month}" : $this->month;
        $dateTime = "{$this->year}-{$month}-01 00:00:00";
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $dateTime, 'America/Santiago');
        return $date;
    }

    protected function build() : void {
        $empty_structure = $this->createWeekStructure();
        $interactions = $this->addInteractions($empty_structure);
        $this->results = $interactions;
    }

    private function createWeekStructure() : array {
        $days = ['Lunes','Martes','Miercoles','Jueves','Viernes','Sabado', 'Domingo'];
        $structure = array();
        foreach($days as $day){
            $structure[$day] = array();
            $structure[$day]['Mañana'] = 0;
            $structure[$day]['Tarde'] = 0;
            $structure[$day]['Noche'] = 0;
            $structure[$day]['Madrugada'] = 0;
        }
        return $structure;
    }

    private function addInteractions(array $structure) : array {
        foreach($this->interactions as $interaction){
            $day = $this->dayNameFromPosition((int) $interaction->day_of_week);
            $structure[$day][$interaction->day_part] += $interaction->views_count;
        }
        return $structure;
    }

    private function dayNameFromPosition(int $position) : string {
        $days = ['Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado'];
        return $days[$position];
    }

    private function setMergedInteractions(){
        $interactions = new Collection();
        foreach($this->interactions as $interaction){
            $interactions = $interactions->merge($interaction);
        }
        $interactions = $interactions->groupBy('user_id');
        $this->merged_interactions = $interactions;
    }



    // OVERRIDE METHOD
    protected function loadInteractions() : void {
        parent::loadInteractions();
        $merged_interactions = new Collection();
        foreach($this->interactions as $type => $interactions){
            $merged_interactions = $merged_interactions->merge($interactions);
        }
        $this->interactions = $merged_interactions->toArray();
    }

    // OVERRIDE METHOD
    protected function getGroupedInteractionBuilder() : GroupedInteractionBuilder {
        $builder = parent::getGroupedInteractionBuilder();
        $builder->columns = [
            DB::raw('count (*) as views_count'),
            DB::raw('extract(dow from viewed) as day_of_week'),
            DB::raw("case
                        when extract(hour from viewed) between 0 and 5 then 'Madrugada'
                        when extract(hour from viewed) between 6 and 11 then 'Mañana'
                        when extract(hour from viewed) between 12 and 17 then 'Tarde'
                        when extract(hour from viewed) between 18 and 23 then 'Noche'
                        else 'Undefined' end as day_part")
        ];
        $builder->group_by = ['user_id', 'day_of_week', 'day_part'];
        return $builder;
    }

    // OVERRIDE METHOD
    protected function getGroupedInteractions(string $table_name) : Collection {
        $builder = $this->getGroupedInteractionBuilder();
        return DB::table($table_name)
            ->select($builder->columns)
            ->where([['course_id', $this->course->getId()],
                     ['viewed','>=', $this->load_filter->from],
                     ['viewed','<=', $this->load_filter->until]])
            ->whereIn('user_id', $this->students_id)
            ->groupBy($builder->group_by)
            ->get();
    }
}
