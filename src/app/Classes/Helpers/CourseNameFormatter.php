<?php

namespace App\Classes\Helpers;

class CourseNameFormatter{

    private string $name;
    private ?int $course_code;
    private array $sections;
    private ?string $period;
    private ?int $year;

    function __construct(string $name){
        $this->name = trim($name);
        $this->course_code = null;
        $this->sections = [];
        $this->period = null;
        $this->year = null;
        $this->setPrefix();
        $this->setPostfix();
    }

    public function getSummary() : object {
        $summary = new \StdClass();
        $summary->name = $this->getCleanName();
        $summary->course_code = $this->course_code;
        $summary->sections = $this->sections;
        $summary->period = $this->period;
        $summary->year = $this->year;
        return $summary;
    }

    public function getOriginalName(){
        return $this->name;
    }

    public function getCourseCode(){
        return $this->course_code;
    }

    public function getSections(){
        return $this->sections;
    }

    private function setPrefix(){
        if($this->hasPrefix()){
            $raw_prefix = $this->getRawPrefix();
            $this->setCourseCode($raw_prefix);
            $this->setSections($raw_prefix);
        }
    }

    private function setCourseCode(string $prefix) : void {
        $this->course_code = explode('-', $prefix)[0] ?? null;
    }

    private function setSections(string $prefix) : void {
        $this->sections = explode('-', $prefix) ?? null;
        if(isset($this->sections[0])){
            unset($this->sections[0]);
        }
        $this->sections = array_values($this->sections);
    }

    private function hasPrefix() : bool {
        return !empty($this->getRawPrefix());
    }

    private function getRawPrefix() : ?string {
        $pattern = "/^\d+[-\d{1,}]+/";
        $matchs = array();
        preg_match($pattern, $this->name, $matchs, PREG_OFFSET_CAPTURE);
        $prefix = $matchs[0][0] ?? null;
        return $prefix;
    }

    private function setPostfix(){
        if($this->hasPostfix()){
            $raw_postfix = $this->getRawPostfix();
            $this->setPeriod($raw_postfix);
            $this->setYear($raw_postfix);
        }
    }

    private function hasPostfix() : bool {
        return !empty($this->getRawPostfix());
    }

    private function setPeriod(string $postfix) : void {
        $postfix = preg_replace("/[\(\)]/", '', $postfix);
        $this->period = explode("-", $postfix)[0] ?? null;
    }

    private function setYear(string $postfix) : void {
        $postfix = preg_replace("/[\(\)]/", '', $postfix);
        $this->year = explode("-", $postfix)[1] ?? null;
    }

    private function getRawPostfix() : ?string {
        $pattern = "/\([SsTt]\d-\d{4}\)$/";
        $matchs = array();
        preg_match($pattern, $this->name, $matchs, PREG_OFFSET_CAPTURE);
        $postfix = $matchs[0][0] ?? null;
        return $postfix;
    }

    private function getCleanName() : ?string {
        $prefix = $this->getRawPrefix();
        $postfix = $this->getRawPostfix();
        $name = str_replace($prefix, "", $this->name);
        $name = str_replace($postfix, "", $name);
        return trim($name);
    }
}
