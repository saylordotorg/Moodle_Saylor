<?php


declare(strict_types=1);

namespace mod_readaloud;

use core\activity_dates;

class dates extends activity_dates {

    /**
     * Returns a list of important dates in mod_assign
     *
     * @return array
     */
    protected function get_dates(): array {
        global $CFG;

        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        $course = get_course($this->cm->course);
        $context = \context_module::instance($this->cm->id);
        $assign = new \assign($context, $this->cm, $course);

        $timeopen = $this->cm->customdata['allowsubmissionsfromdate'] ?? null;
        $timedue = $this->cm->customdata['duedate'] ?? null;

        $activitygroup = groups_get_activity_group($this->cm, true);
        if ($activitygroup) {
            if ($assign->can_view_grades()) {
                $groupoverride = \cache::make('mod_assign', 'overrides')->get("{$this->cm->instance}_g_{$activitygroup}");
                if (!empty($groupoverride->allowsubmissionsfromdate)) {
                    $timeopen = $groupoverride->allowsubmissionsfromdate;
                }
                if (!empty($groupoverride->duedate)) {
                    $timedue = $groupoverride->duedate;
                }
            }
        }

        $now = time();
        $dates = [];

        if ($timeopen) {
            $openlabelid = $timeopen > $now ? 'activitydate:submissionsopen' : 'activitydate:submissionsopened';
            $date = [
                'label' => get_string($openlabelid, 'mod_readaloud'),
                'timestamp' => (int) $timeopen,
            ];
            if ($course->relativedatesmode && $assign->can_view_grades()) {
                $date['relativeto'] = $course->startdate;
            }
            $dates[] = $date;
        }

        if ($timedue) {
            $date = [
                'label' => get_string('activitydate:submissionsdue', 'mod_readaloud'),
                'timestamp' => (int) $timedue,
            ];
            if ($course->relativedatesmode && $assign->can_view_grades()) {
                $date['relativeto'] = $course->startdate;
            }
            $dates[] = $date;
        }

        return $dates;
    }
}
