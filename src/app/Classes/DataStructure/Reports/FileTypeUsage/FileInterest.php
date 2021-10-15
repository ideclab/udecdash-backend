<?php
namespace App\Classes\DataStructure\Reports\FileTypeUsage;

use Illuminate\Support\Collection;

class FileInterest {
    public int $file_count;
    public float $file_percentage;
    public Collection $resources;
    public Collection $sections;

    function __construct(){
        $this->file_count = 0;
        $this->file_percentage = 0;
        $this->resources = new Collection();
        $this->sections = new Collection();
    }
}
