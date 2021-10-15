<?php
namespace App\Classes\DataStructure;

use Carbon\Carbon;

class LoadFilter {
    public bool $load_grouped;
    public string $from;
    public string $until;
    public ?array $columns_to_group_interactions;

    function __construct(){
        $this->load_grouped = false;
        $this->from = "2018-01-01 00:00:00";
        $this->until = Carbon::now()->format('Y-m-d H:m:s');
        $this->columns_to_group_interactions = null;
    }
}
