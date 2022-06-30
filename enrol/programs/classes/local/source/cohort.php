<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace enrol_programs\local\source;

use stdClass;

/**
 * Program allocation for all visible cohort members.
 *
 * @package    enrol_programs
 * @copyright  Copyright (c) 2022 Open LMS (https://www.openlms.net/)
 * @author     Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cohort extends base {
    /**
     * Return short type name of source, it is used in database to identify this source.
     *
     * NOTE: this must be unique and ite cannot be changed later
     *
     * @return string
     */
    public static function get_type(): string {
        return 'cohort';
    }

    /**
     * Is it possible to manually edit user allocation?
     *
     * @param stdClass $program
     * @param stdClass $source
     * @param stdClass $allocation
     * @return bool
     */
    public static function allocation_edit_supported(stdClass $program, stdClass $source, stdClass $allocation): bool {
        return true;
    }

    /**
     * Is it possible to manually delete user allocation?
     *
     * @param stdClass $program
     * @param stdClass $source
     * @param stdClass $allocation
     * @return bool
     */
    public static function allocation_delete_supported(stdClass $program, stdClass $source, stdClass $allocation): bool {
        if ($allocation->archived) {
            return true;
        }
        return false;
    }

    /**
     * Make sure users are allocated properly.
     *
     * This is expected to be called from cron and when
     * program allocation settings are updated.
     *
     * @param int|null $programid
     * @param int|null $userid
     * @return bool true if anything updated
     */
    public static function fix_allocations(?int $programid, ?int $userid): bool {
        global $DB;

        $updated = false;

        // Allocate all missing users and revert archived allocations.
        $params = [];
        $programselect = '';
        if ($programid) {
            $programselect = 'AND p.id = :programid';
            $params['programid'] = $programid;
        }
        $userselect = '';
        if ($userid) {
            $userselect = "AND cm.userid = :userid";
            $params['userid'] = $userid;
        }
        $now = time();
        $params['now1'] = $now;
        $params['now2'] = $now;
        $sql = "SELECT DISTINCT p.id, cm.userid, s.id AS sourceid, pa.id AS allocationid
                  FROM {cohort_members} cm
                  JOIN {enrol_programs_cohorts} pc ON pc.cohortid = cm.cohortid
                  JOIN {enrol_programs_programs} p ON p.id = pc.programid
                  JOIN {enrol_programs_sources} s ON s.programid = p.id AND s.type = 'cohort'
             LEFT JOIN {enrol_programs_allocations} pa ON pa.programid = p.id AND pa.userid = cm.userid
                 WHERE (pa.id IS NULL OR (pa.archived = 1 AND pa.sourceid = s.id))
                       AND p.archived = 0
                       AND (p.timeallocationstart IS NULL OR p.timeallocationstart <= :now1)
                       AND (p.timeallocationend IS NULL OR p.timeallocationend > :now2)
                       $programselect $userselect
              ORDER BY p.id ASC, s.id ASC";
        $rs = $DB->get_recordset_sql($sql, $params);
        $lastprogram = null;
        $lastsource = null;
        foreach ($rs as $record) {
            if ($record->allocationid) {
                $DB->set_field('enrol_programs_allocations', 'archived', 0, ['id' => $record->allocationid]);
            } else {
                if ($lastprogram && $lastprogram->id == $record->id) {
                    $program = $lastprogram;
                } else {
                    $program = $DB->get_record('enrol_programs_programs', ['id' => $record->id], '*', MUST_EXIST);
                    $lastprogram = $program;
                }
                if ($lastsource && $lastsource->id == $record->sourceid) {
                    $source = $lastsource;
                } else {
                    $source = $DB->get_record('enrol_programs_sources', ['id' => $record->sourceid], '*', MUST_EXIST);
                    $lastsource = $source;
                }
                self::allocate_user($program, $source, $record->userid, []);
                $updated = true;
            }
        }
        $rs->close();

        // Archive allocations if user not member.
        $params = [];
        $programselect = '';
        if ($programid) {
            $programselect = 'AND p.id = :programid';
            $params['programid'] = $programid;
        }
        $userselect = '';
        if ($userid) {
            $userselect = "AND pa.userid = :userid";
            $params['userid'] = $userid;
        }
        $now = time();
        $params['now1'] = $now;
        $params['now2'] = $now;
        $sql = "SELECT pa.id
                  FROM {enrol_programs_allocations} pa
                  JOIN {enrol_programs_sources} s ON s.programid = pa.programid AND s.type = 'cohort' AND s.id = pa.sourceid
                  JOIN {enrol_programs_programs} p ON p.id = pa.programid
                 WHERE p.archived = 0 AND pa.archived = 0
                       AND NOT EXISTS (
                            SELECT 1
                              FROM {cohort_members} cm
                              JOIN {enrol_programs_cohorts} pc ON pc.cohortid = cm.cohortid
                             WHERE cm.userid = pa.userid AND pc.programid = p.id
                       )
                       AND (p.timeallocationstart IS NULL OR p.timeallocationstart <= :now1)
                       AND (p.timeallocationend IS NULL OR p.timeallocationend > :now2)
                       $programselect $userselect
              ORDER BY pa.id ASC";
        $rs = $DB->get_recordset_sql($sql, $params);
        foreach ($rs as $pa) {
            // NOTE: it is expected that enrolment fixing is executed right after this method.
            $DB->set_field('enrol_programs_allocations', 'archived', 1, ['id' => $pa->id]);
            $updated = true;
        }
        $rs->close();

        return $updated;
    }
}
