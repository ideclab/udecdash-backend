<?php
namespace App\Classes\DataStructure\Reports\FileTypeUsage;

use Illuminate\Support\Collection;

class VisualizationSummary {
    public int $views_count;
    public int $downloads_count;

    function __construct(){
        $this->views_count = 0;
        $this->downloads_count = 0;
    }
}
