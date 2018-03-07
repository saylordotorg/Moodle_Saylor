<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The gradebook quizanalytics report
 *
 * @package   gradereport_quizanalytics
 * @author Moumita Adak <moumita.a@dualcube.com>
 * @copyright Dualcube (https://dualcube.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');

global $DB, $USER, $PAGE, $CFG;
$quizid  = required_param('quiz', PARAM_INT);

require_login();

if (!empty($quizid)) {
    $quiz = $DB->get_record('quiz', array('id' => $quizid));

    $attemptssql = "SELECT * FROM {quiz_attempts}
      WHERE state = 'finished' AND sumgrades IS NOT NULL AND quiz = ?";

    $totalquizattempted = $DB->get_records_sql($attemptssql, array($quizid));

    $usersgradedattempts = $DB->get_records_sql($attemptssql." AND userid = ?", array($quizid, $USER->id));

    $totalnoofquestion = $DB->get_record_sql("SELECT COUNT(qs.questionid) as qnum
                  FROM {quiz_slots} qs, {question} q WHERE q.id = qs.questionid
                  AND qs.quizid = ? AND q.qtype != ?", array($quizid, 'description'));

    if (!empty($usersgradedattempts)) {
        /**
         * Return the part of random color.
         */
        function random_color_part() {
            return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
        }
        /**
         * Return the random color.
         */
        function random_color() {
            return random_color_part() . random_color_part() . random_color_part();
        }

        $catdetails = $DB->get_records_sql("SELECT qc.id, COUNT(qs.questionid) as qnum,
        qc.name FROM {quiz_slots} qs, {question} q, {question_categories} qc
        WHERE q.id = qs.questionid AND qc.id = q.category AND
        qs.quizid = ? AND q.qtype != ? GROUP BY qc.id", array($quizid, 'description'));

        $catname = array();
        $catdata = array();
        $randomcatcolor = array();
        $overallhardness = array();
        $loggdinuserhardness = array();
        $totaluserswrongattemts = array();
        $totalwrongattemts = array();
        foreach ($catdetails as $catdetail) {
            $catname[] = $catdetail->name;
            $catdata[] = $catdetail->qnum;
            $randomcatcolor[] = "#".random_color();

            $sqlattempt = "SELECT qattstep.id as qattstepid, quizatt.id as quizattid,
            qatt.questionid, qattstep.state, qattstep.sequencenumber
            FROM {quiz_attempts} quizatt, {question_attempts} qatt,
            {question_attempt_steps} qattstep, {question} q, {question_categories} qc
            WHERE qatt.questionusageid = quizatt.uniqueid AND
            qattstep.questionattemptid = qatt.id AND q.id = qatt.questionid
            AND qc.id = q.category AND quizatt.quiz = ? AND
            q.category = ? AND q.qtype != ?";

            $totalcorrectattempts = $DB->get_records_sql($sqlattempt." AND
            qattstep.sequencenumber >= 2 AND (qattstep.state = 'gradedright' OR
            qattstep.state = 'mangrright')", array($quizid, $catdetail->id, 'description'));

            $userstotalcorrectattempts = $DB->get_records_sql($sqlattempt." AND
            quizatt.userid = ? AND qattstep.sequencenumber >= 2 AND
            (qattstep.state = 'gradedright' OR qattstep.state = 'mangrright')", array($quizid, $catdetail->id, 'description', $USER->id));

            $totalquizattempts = count($totalquizattempted);
            $totalquizuserattempts = count($usersgradedattempts);

            $totalnoofcatattempts = $catdetail->qnum * $totalquizattempts;
            $totalnoofcatuserattempts = $catdetail->qnum * $totalquizuserattempts;

            $totalwrongattemts[] = ($totalnoofcatattempts - count($totalcorrectattempts));
            $totaluserswrongattemts[] = ($totalnoofcatuserattempts - count($userstotalcorrectattempts));

            $hardness = (($totalnoofcatattempts - count($totalcorrectattempts)) / $totalnoofcatattempts) * 100;
            $usershardness = (($totalnoofcatuserattempts - count($userstotalcorrectattempts)) / $totalnoofcatuserattempts) * 100;

            $overallhardness[] = round($hardness, 2);
            $loggdinuserhardness[] = round($usershardness, 2);
        }

        /* questionpercat */
        $questionpercatdata = array('labels' => $catname, 'datasets' => array(array('label'
            => get_string('questionspercategory', 'gradereport_quizanalytics'),
            'backgroundColor' => $randomcatcolor, 'data' => $catdata)));

        $questionpercatopt = array('legend' => array('display' => false,
            'position' => 'bottom', 'labels' => array('boxWidth' => 13)), 'title' => array('display' => true,
            'position' => 'bottom', 'text' => get_string('questionspercategory', 'gradereport_quizanalytics')));

        /* allusers */
        arsort($overallhardness);
        $maxhardnesskeys = array_keys($overallhardness, max($overallhardness));

        foreach ($maxhardnesskeys as $maxhardnesskey) {
            $previous = $maxhardnesskey;
            break;
        }
        $randomoverallhardnesscolor = array();
        $overallhardnessdata = array();
        $catnamedata = array();
        $catcount = 0;
        foreach ($overallhardness as $key => $val) {
            if ($totalwrongattemts[$key] > 0) {
                $twentyperpreviouswrongattempt = (($totalwrongattemts[$previous] * 20) / 100);
                if ($totalwrongattemts[$key] >= $twentyperpreviouswrongattempt) {
                    if ($catcount < 10) {
                        $overallhardnessdata[] = $val;
                        $catnamedata[] = $catname[$key];
                        $randomoverallhardnesscolor[] = "#".random_color();
                        $catcount++;
                    }
                }
            }
            $previous = $key;
        }

        $allusersdata = array(
            'labels' => $catnamedata, 'datasets' => array(
            array('label' => get_string('hardness', 'gradereport_quizanalytics'),
            'backgroundColor' => $randomoverallhardnesscolor,
            'data' => $overallhardnessdata)));

        $allusersopt = array('legend' => array('display' => false,
            'position' => 'bottom'), 'title' => array('display' => false,
            'position' => 'bottom', 'text' => get_string('hardcatalluser', 'gradereport_quizanalytics')));

        /* loggedinuser */
        arsort($loggdinuserhardness);
        $maxloggdinuserhardnesskeys = array_keys($loggdinuserhardness, max($loggdinuserhardness));

        foreach ($maxloggdinuserhardnesskeys as $maxloggdinuserhardnesskey) {
            $previouskey = $maxloggdinuserhardnesskey;
            break;
        }

        $randomuserhardnesscolor = array();
        $usershardnessdata = array();
        $usercatnamedata = array();
        $usercatcount = 0;
        foreach ($loggdinuserhardness as $key => $val) {
            if ($totaluserswrongattemts[$key] > 0) {
                $twentyperprevioususerwrongattempt = (($totaluserswrongattemts[$previouskey] * 20) / 100);
                if ($totaluserswrongattemts[$key] >= $twentyperprevioususerwrongattempt) {
                    if ($usercatcount < 10) {
                        $usershardnessdata[] = $val;
                        $usercatnamedata[] = $catname[$key];
                        $randomuserhardnesscolor[] = "#".random_color();
                        $usercatcount++;
                    }
                }
            }
            $previouskey = $key;
        }

        $loggedinuserdata = array(
            'labels' => $usercatnamedata, 'datasets' => array(
            array('label' => get_string('hardness', 'gradereport_quizanalytics'),
            'backgroundColor' => $randomuserhardnesscolor, 'data' => $usershardnessdata)));

        $loggedinuseropt = array('legend' => array('display' => false,
          'position' => 'bottom'), 'title' => array('display' => false,
          'position' => 'bottom', 'text' => get_string('hardcatlogginuser', 'gradereport_quizanalytics')));

        /* lastattemptsummary */
        $lastattemptid = $DB->get_record_sql("SELECT quizatt.id FROM {quiz_attempts} quizatt
        WHERE quizatt.state = 'finished' AND quizatt.sumgrades IS NOT NULL
        AND quizatt.quiz = ? AND quizatt.userid= ?
        ORDER BY quizatt.id DESC LIMIT 1", array($quizid, $USER->id));

        $attemptdetailssql = "SELECT qatt.questionid, qattstep.state, qattstep.fraction,
        qatt.maxmark FROM {quiz_attempts} quizatt, {question_attempts} qatt,
        {question_attempt_steps} qattstep WHERE qatt.questionusageid = quizatt.uniqueid
        AND qattstep.questionattemptid = qatt.id AND quizatt.userid = ?
        AND quizatt.id = ? AND quizatt.quiz = ?";

        $totalattempted = $DB->get_records_sql($attemptdetailssql."
            AND qattstep.sequencenumber = 2", array($USER->id, $lastattemptid->id, $quizid));

        $rightattempt = $DB->get_records_sql($attemptdetailssql." AND (qattstep.state =
        'gradedright' OR qattstep.state = 'mangrright')", array($USER->id, $lastattemptid->id, $quizid));

        $partialcorrectattempt = $DB->get_records_sql($attemptdetailssql."
        AND (qattstep.state = 'gradedpartial' OR qattstep.state = 'mangrpartial')", array($USER->id, $lastattemptid->id, $quizid));

        $userscores = array();
        $quesmarks = array();
        $partialcorrectcount = 0;
        if (!empty($partialcorrectattempt)) {
            foreach ($partialcorrectattempt as $partialcorrect) {
                $partialcorrectcount++;
                $userscores[] = $partialcorrect->fraction;
                $quesmarks[] = $partialcorrect->maxmark;
            }
            $totaluserscores = array_sum($userscores);
            $totalquesmarks = array_sum($quesmarks);

            $percentageofmarks = ($totaluserscores / $totalquesmarks) * 100;

            $numofpartialcorrect = $partialcorrectcount * ($percentageofmarks / 100);

        } else {
            $numofpartialcorrect = 0;
        }

        $correctattempted = count($rightattempt) + round($numofpartialcorrect);

        if (!empty($totalattempted)) {
            $accuracyrate = ($correctattempted / count($totalattempted)) * 100;
        } else {
            $accuracyrate = 0;
        }

        if (count($totalattempted) != 0) {
            if (count($partialcorrectattempt) != 0) {
                $lastattemptsummarydata = array('labels' => array(
                get_string('noofquestionattempt', 'gradereport_quizanalytics'),
                get_string('noofrightans', 'gradereport_quizanalytics'),
                get_string('noofpartialcorrect', 'gradereport_quizanalytics')),
                'datasets' => array(array(
                'backgroundColor' => array("#2EA0EF", "#79D527", "#FF9827"),
                'data' => array(count($totalattempted), count($rightattempt),
                count($partialcorrectattempt)))));
            } else {
                $lastattemptsummarydata = array('labels' => array(
                get_string('noofquestionattempt', 'gradereport_quizanalytics'),
                get_string('noofrightans', 'gradereport_quizanalytics')),
                'datasets' => array(array(
                'backgroundColor' => array("#2EA0EF", "#79D527"),
                'data' => array(count($totalattempted), count($rightattempt)))));
            }
            $lastattemptsummaryopt = array('legend' => array('display' => false),
            'title' => array('display' => false), 'scales' => array(
            'xAxes' => array(array('ticks' => array('min' => 0),
            'scaleLabel' => array('display' => true,
            'labelString' => get_string('accuaracyrate', 'gradereport_quizanalytics').round($accuracyrate, 2)."%"))),
            'yAxes' => array(array('barPercentage' => 0.4))));
        } else {
            $lastattemptsummarydata = array();
            $lastattemptsummaryopt = array();
        }

        /* attemptssnapshot */
        $attemptsql = "SELECT COUNT(qatt.questionid) as num
                    FROM {quiz_attempts} quizatt, {question_attempts} qatt,
                    {question_attempt_steps} qattstep, {question} q
                    WHERE qatt.questionusageid = quizatt.uniqueid
                    AND qattstep.sequencenumber = 2 AND q.id = qatt.questionid
                    AND qattstep.questionattemptid = qatt.id
                    AND quizatt.userid = ? AND quizatt.quiz= ? AND q.qtype != ?";

        $snapdata = array();
        $snapshotdata = array();
        $snapshotopt = array();
        if (!empty($usersgradedattempts)) {
            $count = 1;
            foreach ($usersgradedattempts as $attemptvalue) {
                $numofattempt = $DB->get_record_sql("SELECT COUNT(qatt.questionid) as anum
                    FROM {quiz_attempts} quizatt, {question_attempts} qatt,
                    {question_attempt_steps} qattstep, {question} q
                    WHERE qatt.questionusageid = quizatt.uniqueid AND q.id = qatt.questionid
                    AND qattstep.questionattemptid = qatt.id AND qattstep.sequencenumber = 2
                    AND quizatt.userid = ? AND quizatt.quiz= ? AND quizatt.attempt = ? AND q.qtype != ?",
                    array($USER->id, $quizid, $attemptvalue->attempt, 'description'));

                $timediff = ($attemptvalue->timefinish - $attemptvalue->timestart);
                $timetaken = round(($timediff / 60), 2);

                $numofunattempt = ($totalnoofquestion->qnum - $numofattempt->anum);

                $correct = $DB->get_record_sql($attemptsql." AND quizatt.attempt = ?
                AND qattstep.state = ?",
                array($USER->id, $quizid, 'description', $attemptvalue->attempt, 'gradedright'));

                $incorrect = $DB->get_record_sql($attemptsql." AND quizatt.attempt = ?
                AND qattstep.state = ?",
                array($USER->id, $quizid, 'description', $attemptvalue->attempt, 'gradedwrong'));

                $partialcorrect = $DB->get_record_sql($attemptsql." AND quizatt.attempt = ?
                AND qattstep.state = ?",
                array($USER->id, $quizid, 'description', $attemptvalue->attempt, 'gradedpartial'));

                $snapdata[$count][0] = intval($numofunattempt);
                $snapdata[$count][1] = intval($correct->num);
                $snapdata[$count][2] = intval($incorrect->num);
                $snapdata[$count][3] = intval($partialcorrect->num);


                $snapshotdata[$count] = array('labels' => array(
                    get_string('unattempted', 'gradereport_quizanalytics'),
                    get_string('correct', 'gradereport_quizanalytics'),
                    get_string('incorrect', 'gradereport_quizanalytics'),
                    get_string('partialcorrect', 'gradereport_quizanalytics')),
                    'datasets' => array(array('label' => 'Attempt'.$count,
                    'backgroundColor' => array('#3e95cd', '#8e5ea2', '#3cba9f', '#e8c3b9'),
                    'data' => $snapdata[$count])));

                $snapshotopt[$count] = array('title' => array('display' => true,
                'position' => 'bottom', 'text' => get_string('timetaken',
                'gradereport_quizanalytics').$timetaken.'min)'),
                'legend' => array('display' => false, 'position' => 'bottom',
                'labels' => array('boxWidth' => 13)));

                $count++;
            }
        } else {
            $snapshotdata[1] = array('labels' => array(
                get_string('unattempted', 'gradereport_quizanalytics'),
                get_string('correct', 'gradereport_quizanalytics'),
                get_string('incorrect', 'gradereport_quizanalytics'),
                get_string('partialcorrect', 'gradereport_quizanalytics')),
                'datasets' => array(array('label' => 'Attempt1',
                'backgroundColor' => array('#3e95cd', '#8e5ea2', '#3cba9f', '#e8c3b9'),
                'data' => array(0, 0, 0, 0))));

            $snapshotopt[1] = array('title' => array('display' => true,
            'position' => 'bottom', 'text' => 'Attempts Snapshot( timetaken: 0min )'));
        }


        /* timechart */
        if ($quiz->attempts == 1) {
            $scores = array();
            $scoredata = array();
            foreach ($totalquizattempted as $totalquizattempt) {
                $scores[] = ($totalquizattempt->sumgrades / $quiz->sumgrades ) * 100;
            }

            $userscore = $DB->get_record('quiz_attempts', array('quiz' => $quizid, 'userid' => $USER->id));
            $userscoredata = ($userscore->sumgrades / $quiz->sumgrades ) * 100;

            $scoredata[0] = round($userscoredata, 2);
            $scoredata[1] = round(max($scores), 2);
            $scoredata[2] = round((array_sum($scores) / count($scores)), 2);
            $scoredata[3] = round(min($scores), 2);

            $timechartdata = array('labels' => array(
                get_string('userscore', 'gradereport_quizanalytics'),
                get_string('bestscore', 'gradereport_quizanalytics'),
                get_string('avgscore', 'gradereport_quizanalytics'),
                get_string('lowestscore', 'gradereport_quizanalytics')),
                'datasets' => array(array('label' => get_string('score', 'gradereport_quizanalytics'),
                'backgroundColor' => "#3e95cd", 'data' => $scoredata)));

            $timechartopt = array('showTooltips' => false,
            'legend' => array('display' => false),
            'title' => array('display' => true, 'text' => get_string('peerscores', 'gradereport_quizanalytics')));
        } else {
            $timechartdata = array();
            $timechartopt = array();
        }

        /* mixchart */
        $bestnthattempt = array();
        $bestscored = array();
        $totalnthattempt = array();

        $gradetopass = ($quiz->sumgrades * $CFG->gradereport_quizanalytics_cutoff) / 100;

        $attempttorichcutoff = $DB->get_records_sql($attemptssql."
        AND sumgrades >= ? GROUP BY userid", array($quizid, $gradetopass));

        foreach ($attempttorichcutoff as $torichcutoff) {
            $totalnthattempt[] = $torichcutoff->attempt;
        }
        if (!empty($totalnthattempt)) {
            $averagenthattempt = array_sum($totalnthattempt) / count($totalnthattempt);
        } else {
            $averagenthattempt = 0;
        }

        $torichcutoffarray = array();
        for ($i = 0; $i <= round($averagenthattempt); $i++) {
            $torichcutoffarray[] = round($gradetopass, 2);
        }

        $usersattempts = $DB->get_records_sql("SELECT * FROM {quiz_attempts} WHERE
        state = 'finished' AND quiz = ? AND userid = ?", array($quizid, $USER->id));

        if (!empty($usersattempts)) {
            $attemptnum = array(0);
            $scored = array(0);
            $attemptno = 1;
            foreach ($usersattempts as $usersattempt) {
                if (!empty($usersattempt->sumgrades)) {
                    array_push($attemptnum, $attemptno);
                    array_push($scored, round($usersattempt->sumgrades, 2));
                } else {
                    array_push($attemptnum, $attemptno.'(NG)');
                    array_push($scored, 0);
                }
                $attemptno++;
            }
        }
        if (round($averagenthattempt) >= $attemptno) {
            for ($j = $attemptno; $j <= round($averagenthattempt); $j++) {
                array_push($attemptnum, $j);
            }
        }

        $mixchartdata = array(
            'labels' => $attemptnum,
            'datasets' => array(array(
                'label' => get_string('cutoffscore', 'gradereport_quizanalytics'),
                'borderColor' => "#3e95cd",
                'data' => $torichcutoffarray,
                'fill' => true
                ),
            array(
                'label' => get_string('score', 'gradereport_quizanalytics'),
                'borderColor' => "#8e5ea2",
                'data' => $scored,
                'fill' => false
                ))
            );

        $mixchartopt = array('title' => array('display' => true, 'position' => 'bottom',
        'text' => get_string('impandpredicanalysis', 'gradereport_quizanalytics')),
        'legend' => array('display' => true, 'position' => 'bottom', 'labels' => array('boxWidth' => 13)));


        /* gradeanalysis */
        $gradeanalysislables = array();
        $randomcolor = array();
        $gradeanalysisdataarray = array();
        if ($CFG->gradereport_quizanalytics_globalboundary == 1) {
                $gradeboundarydetails = $CFG->gradereport_quizanalytics_gradeboundary;
                $gradeboundarydetail = explode(",", $gradeboundarydetails);
            foreach ($gradeboundarydetail as $gradeboundary) {
                    $grades = explode("-", $gradeboundary);

                    $mingrade = ($grades[0] * $quiz->grade) / 100;
                    $maxgrade = ($grades[1] * $quiz->grade) / 100;

                    $gradeanalysislables[] = $mingrade." - ".$maxgrade;
                    $randomcolor[] = "#".random_color();

                    $userrecords = $DB->get_record_sql("SELECT COUNT(qg.id)
                    as numofstudents FROM {quiz_grades} qg, {quiz} q WHERE
                    q.id = qg.quiz AND qg.quiz = ? AND qg.grade BETWEEN ? AND ?",
                    array($quizid, $mingrade, $maxgrade));

                    $gradeanalysisdataarray[] = $userrecords->numofstudents;
            }
        } else {
            $gradeboundaryrecs = $DB->get_records_sql("SELECT id, mingrade, maxgrade
            FROM {quiz_feedback} WHERE quizid = ?", array($quizid));

            foreach ($gradeboundaryrecs as $gradeboundaryrec) {
                $mingrade = round($gradeboundaryrec->mingrade);
                $maxgrade = round($gradeboundaryrec->maxgrade) - 1;

                $gradeanalysislables[] = $mingrade." - ".$maxgrade;
                $randomcolor[] = "#".random_color();

                $userrecords = $DB->get_record_sql("SELECT COUNT(qg.id) as numofstudents
                FROM {quiz_grades} qg, {quiz} q WHERE q.id = qg.quiz
                AND qg.quiz = ? AND qg.grade BETWEEN ? AND ?",
                array($quizid, $mingrade, $maxgrade));

                $gradeanalysisdataarray[] = $userrecords->numofstudents;
            }
        }

        $gradeanalysisdata = array('labels' => $gradeanalysislables, 'datasets' => array(
        array('label' => get_string('noofstudents', 'gradereport_quizanalytics'),
        'backgroundColor' => $randomcolor, 'data' => $gradeanalysisdataarray)));

        $gradeanalysisopt = array('title' => array('display' => true,
        'text' => get_string('noofstudents', 'gradereport_quizanalytics'), 'position' => 'bottom'),
        'legend' => array('display' => false, 'position' => 'bottom', 'labels' => array('boxWidth' => 13)));


        /* quesanalysis */
        $totalquestions = $DB->get_records_sql("SELECT qs.questionid, q.qtype
        FROM {quiz_slots} qs, {question} q WHERE q.id = qs.questionid AND
        qs.quizid= ? AND q.qtype != ?", array($quizid, 'description'));

        $totalunattempted = array();
        $correctresponse = array();
        $incorrectresponse = array();
        $partialcorrectresponse = array();
        $queslabels = array();
        $queshardness = array();
        $wrongandunattemptd = array();
        $quesattempts = array();
        $selectedquestionid = array();
        $quescount = 1;

        foreach ($totalquestions as $totalquestion) {
            if ($totalquestion->qtype == "essay") {
                $questionresponsesql = "SELECT COUNT(qatt.id) as qnum FROM
                {question_attempts} qatt, {quiz_attempts} quizatt,
                {question_attempt_steps} qas WHERE qas.questionattemptid = qatt.id
                AND quizatt.uniqueid = qatt.questionusageid AND qas.sequencenumber = 3
                AND quizatt.sumgrades <> 'NULL' AND quizatt.quiz= ?
                AND qatt.questionid = ?";
            } else {
                $questionresponsesql = "SELECT COUNT(qatt.id) as qnum FROM
                {question_attempts} qatt, {quiz_attempts} quizatt,
                {question_attempt_steps} qas WHERE qas.questionattemptid = qatt.id
                AND quizatt.uniqueid = qatt.questionusageid AND qas.sequencenumber = 2
                AND quizatt.sumgrades <> 'NULL' AND quizatt.quiz= ?
                AND qatt.questionid = ?";
            }

            $totalcorrectresponse = $DB->get_record_sql($questionresponsesql."
            AND (qas.state = 'gradedright' OR qas.state = 'mangrright')",
            array($quizid, $totalquestion->questionid));

            $totalincorrectresponse = $DB->get_record_sql($questionresponsesql."
            AND (qas.state = 'gradedwrong' OR qas.state = 'mangrwrong')",
            array($quizid, $totalquestion->questionid));

            $totalpartialcorrectresponse = $DB->get_record_sql($questionresponsesql."
            AND (qas.state = 'gradedpartial' OR qas.state = 'mangrpartial')",
            array($quizid, $totalquestion->questionid));

            $unattempted = count($totalquizattempted) - (
            $totalcorrectresponse->qnum + $totalincorrectresponse->qnum + $totalpartialcorrectresponse->qnum);

            $totalunattempted[] = $unattempted;

            $correctresponse[] = $totalcorrectresponse->qnum;
            $incorrectresponse[] = $totalincorrectresponse->qnum;
            $partialcorrectresponse[] = $totalpartialcorrectresponse->qnum;

            $queslabels[] = "Q".$quescount;

            $quesattempts[] = ($totalcorrectresponse->qnum + $totalincorrectresponse->qnum + $totalpartialcorrectresponse->qnum);

            $wrongandunattemptd[] = $unattempted + $totalincorrectresponse->qnum;

            $queshardness[] = round((($unattempted + $totalincorrectresponse->qnum) / count($totalquizattempted)) * 100, 2);

            $selectedquestionid[] = "Q".$quescount.",".$totalquestion->questionid;
            $quescount++;
        }

        arsort($queshardness);

        $maxwrunkeys = array_keys($queshardness, max($queshardness));

        foreach ($maxwrunkeys as $maxwrunkey) {
            $previous = $maxwrunkey;
            break;
        }
        $hardestquesdatalabel = array();
        $totalquizattemptdata = array();
        $wrongandunattemptdata = array();
        $qcount = 0;
        foreach ($queshardness as $key => $val) {
            if ($wrongandunattemptd[$key] > 0) {
                $twentyperpreviouswrongattempted = (($wrongandunattemptd[$previous] * 20) / 100);
                if ($wrongandunattemptd[$key] >= $twentyperpreviouswrongattempted) {
                    if ($qcount < 10) {
                        $hardestquesdatalabel[] = $queslabels[$key];
                        $totalquizattemptdata[] = count($totalquizattempted);
                        $wrongandunattemptdata[] = $wrongandunattemptd[$key];
                        $qcount++;
                    }
                }
            }
            $previous = $key;
        }

        $hardestquesdata = array('labels' => $hardestquesdatalabel, 'datasets' => array(
            array('label' => get_string('totalquizattempt', 'gradereport_quizanalytics'),
            'backgroundColor' => "#8e5ea2", 'data' => $totalquizattemptdata),
            array('label' => get_string('wrongandunattemptd', 'gradereport_quizanalytics'),
            'backgroundColor' => "#EB2838", 'data' => $wrongandunattemptdata)));

        $hardestquesopt = array('title' => array('display' => false,
        'text' => get_string('hardestquestion', 'gradereport_quizanalytics')),
        'legend' => array('display' => true, 'position' => 'bottom',
        'labels' => array('boxWidth' => 13)),
        'barPercentage' => 1.0, 'categoryPercentage' => 1.0);

        /*Quesanalysis*/
        $quesanalysisdata = array('labels' => $queslabels, 'datasets' => array(
        array('data' => $correctresponse, 'borderColor' => "#3e95cd", 'fill' => false,
        'label' => get_string('correct', 'gradereport_quizanalytics')),
        array('data' => $incorrectresponse, 'borderColor' => "#8e5ea2", 'fill' => false,
        'label' => get_string('incorrect', 'gradereport_quizanalytics')),
        array('data' => $partialcorrectresponse, 'borderColor' => "#3cba9f",
        'fill' => false, 'label' => get_string('partialcorrect', 'gradereport_quizanalytics')),
        array('data' => $totalunattempted, 'borderColor' => "#c45850", 'fill' => false,
        'label' => get_string('unattempted', 'gradereport_quizanalytics'))));

        $quesanalysisopt = array('title' => array('display' => false),
        'legend' => array('display' => true, 'position' => 'bottom', 'labels' => array('boxWidth' => 13)));

        $totalarray = array();
        $totalarray = array(
                  'questionpercat' => array(
                    'data' => $questionpercatdata,
                    'opt' => $questionpercatopt
                  ),
                  'allusers' => array(
                    'data' => $allusersdata,
                    'opt' => $allusersopt
                  ),
                  'loggedinuser' => array(
                    'data' => $loggedinuserdata,
                    'opt' => $loggedinuseropt
                  ),
                  'lastattemptsummary' => array(
                    'data' => $lastattemptsummarydata,
                    'opt' => $lastattemptsummaryopt
                  ),
                  'attemptssnapshot' => array(
                    'data' => $snapshotdata,
                    'opt' => $snapshotopt
                  ),
                  'mixchart' => array(
                    'data' => $mixchartdata,
                    'opt' => $mixchartopt
                  ),
                  'timechart' => array(
                    'data' => $timechartdata,
                    'opt' => $timechartopt
                  ),
                  'gradeanalysis' => array(
                    'data' => $gradeanalysisdata,
                    'opt' => $gradeanalysisopt
                  ),
                  'quesanalysis' => array(
                    'data' => $quesanalysisdata,
                    'opt' => $quesanalysisopt
                    ),
                  'hardestques' => array(
                    'data' => $hardestquesdata,
                    'opt' => $hardestquesopt
                    ),
                  'userattempts' => count($usersgradedattempts),
                  'quizattempt' => $quiz->attempts,
                  'allquestion' => $selectedquestionid,
                  'quizid' => $quizid,
                  'url' => $CFG->wwwroot
                  );
        $totalvalue = json_encode($totalarray);
        echo $totalvalue;
    }
}
