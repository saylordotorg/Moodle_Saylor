<?php


declare(strict_types=1);

namespace mod_solo;

use core\activity_dates;

/**
 * Class for fetching the important dates in mod_assign for a given module instance and a user.
 *
 * @copyright 2021 Shamim Rezaie <shamim@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dates extends activity_dates {

    /**
     * Returns a list of important dates in mod_assign
     *
     * @return array
     */
    protected function get_dates(): array {
        global $CFG;

       

        $course = get_course($this->cm->course);
        $context = \context_module::instance($this->cm->id);
       

        $timeopen = $this->cm->customdata['allowsubmissionsfromdate'] ?? null;
        $timedue = $this->cm->customdata['duedate'] ?? null;

        $activitygroup = groups_get_activity_group($this->cm, true);
        

        $now = time();
        $dates = [];

        if ($timeopen) {
            $openlabelid = $timeopen > $now ? 'activitydate:submissionsopen' : 'activitydate:submissionsopened';
            $date = [
                'label' => get_string($openlabelid, 'mod_solo'),
                'timestamp' => (int) $timeopen,
            ];
            if ($course->relativedatesmode ) {
                $date['relativeto'] = $course->startdate;
            }
            $dates[] = $date;
        }

        if ($timedue) {
            $date = [
                'label' => get_string('activitydate:submissionsdue', 'mod_solo'),
                'timestamp' => (int) $timedue,
            ];
            if ($course->relativedatesmode ) {
                $date['relativeto'] = $course->startdate;
            }
            $dates[] = $date;
        }

        return $dates;
    }
}
