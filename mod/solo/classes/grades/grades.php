<?php

namespace mod_solo\grades;

use dml_exception;

/**
 * Class grades
 *
 * Defines a listing of student grades for this course and module.
 *
 * @package mod_solo\grades
 */
class grades {
    /**
     * Gets listing of grades for students.
     *
     * @param int $courseid Course ID of chat.
     * @param int $coursemoduleid
     * @param int $moduleinstance Module instance ID for given chat.
     * @return array
     * @throws dml_exception
     */
    public function getGrades($courseid, $coursemoduleid, $moduleinstance, $groupid) {
        global $DB;
        $results=[];
        if($groupid>0){
            list($groupswhere, $groupparams) = $DB->get_in_or_equal($groupid);
            $sql = "select pa.id as attemptid,
                    u.lastname,
                    u.firstname,
                    p.name,
                    p.transcriber,
                    pat.words,
                    pat.targetwords,
                    pat.totaltargetwords,
                    pat.turns,
                    pat.avturn,
                    par.accuracy,
                    pa.solo,
                    pat.aiaccuracy,
                    pa.manualgraded,
                    pa.grade,
                    pa.userid
                from {solo} as p
                    inner join {solo_attempts} pa on p.id = pa.solo
                    inner join {course_modules} as cm on cm.course = p.course and cm.id = ?
                    inner join {groups_members} gm ON pa.userid=gm.userid
                    inner join {user} as u on pa.userid = u.id
                    inner join {solo_attemptstats} as pat on pat.attemptid = pa.id and pat.userid = u.id
                    left outer join {solo_ai_result} as par on par.attemptid = pa.id and par.courseid = p.course
                where p.course = ?
                    AND pa.solo = ?
                    AND gm.groupid $groupswhere 
                order by pa.id DESC";

            $alldata = $DB->get_records_sql($sql, array_merge([$coursemoduleid, $courseid, $moduleinstance] , $groupparams));

        //not groups
        }else {
            $sql = "select pa.id as attemptid,
                    u.lastname,
                    u.firstname,
                    p.name,
                    p.transcriber,
                    pat.words,
                    pat.targetwords,
                    pat.totaltargetwords,
                    pat.turns,
                    pat.avturn,
                    par.accuracy,
                    pa.solo,
                    pat.aiaccuracy,
                    pa.manualgraded,
                    pa.grade,
                    pa.userid
                from {solo} as p
                    inner join {solo_attempts} pa on p.id = pa.solo
                    inner join {course_modules} as cm on cm.course = p.course and cm.id = ?
                    inner join {user} as u on pa.userid = u.id
                    inner join {solo_attemptstats} as pat on pat.attemptid = pa.id and pat.userid = u.id
                    left outer join {solo_ai_result} as par on par.attemptid = pa.id and par.courseid = p.course
                where p.course = ?
                    AND pa.solo = ?
                order by pa.id DESC";

            $alldata = $DB->get_records_sql($sql, [$coursemoduleid, $courseid, $moduleinstance]);
        }



        //loop through data getting most recent attempt
        if ($alldata) {
            $results=array();
            $user_attempt_totals = array();
            foreach ($alldata as $thedata) {

                //we ony take the most recent attempt
                if (array_key_exists($thedata->userid, $user_attempt_totals)) {
                    $user_attempt_totals[$thedata->userid] = $user_attempt_totals[$thedata->userid] + 1;
                    continue;
                }
                $user_attempt_totals[$thedata->userid] = 1;

                $results[] = $thedata;
            }
            foreach ($results as $thedata) {
                $thedata->totalattempts = $user_attempt_totals[$thedata->userid];
            }
        }
        return $results;
    }
}