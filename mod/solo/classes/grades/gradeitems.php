<?php


namespace mod_solo\grades;


use core_grades\local\gradeitem\advancedgrading_mapping;
use core_grades\local\gradeitem\itemnumber_mapping;

class gradeitems implements itemnumber_mapping, advancedgrading_mapping {

    /**
     * Return the list of grade item mappings for the assign.
     *
     * @return array
     */
    public static function get_itemname_mapping_for_component(): array {
        return [
            0 => 'solo',
        ];
    }

    /**
     * Get the list of advanced grading item names for this component.
     *
     * @return array
     */
    public static function get_advancedgrading_itemnames(): array {
        return [
            'solo' => 'solo',
        ];
    }
}
