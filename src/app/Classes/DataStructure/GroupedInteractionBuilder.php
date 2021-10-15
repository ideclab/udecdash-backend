<?php
namespace App\Classes\DataStructure;

class GroupedInteractionBuilder {
    public array $columns;
    public array $group_by;

    function construct(){
        $this->columns = array();
        $this->group_by = array();
    }
}
