<?php

defined('MOODLE_INTERNAL') || die();

class block_checklist extends block_list {
    function init() {
        $this->title = get_string('checklist','block_checklist');
    }

    function instance_allow_multiple() {
        return true;
    }

    function has_config() {
        return false;
    }

    function instance_allow_config() {
        return true;
    }

    function applicable_formats() {
        return array('course' => true, 'course-category' => false, 'site' => true, 'my'=>true);
    }

    function specialization() {
        global $DB;

        if (!empty($this->config->checklistoverview)) {
            $this->title = get_string('checklistoverview', 'block_checklist');
        } else if (!empty($this->config->checklistid)) {
            $checklist = $DB->get_record('checklist', array('id'=>$this->config->checklistid));
            if ($checklist) {
                $this->title = s($checklist->name);
            }
        }
    }

    function instance_create() {
        global $COURSE;
        if ($COURSE->format == 'site') {
            $config = (object)array(
                'checklistoverview' => 1,
            );
            $this->instance_config_save($config);
        }
    }

    function get_content() {
        if ($this->content !== NULL) {
            return $this->content;
        }

        if (!isloggedin()) {
            return $this->content; // Only display if logged in.
        }

        if ($this->context->get_course_context(false)) {
            if (is_guest($this->context)) {
                return $this->content; // Course guests don't see the checklist block.
            }
        } else if (isguestuser()) {
            return $this->content;  // Site guests don't see the checklist block.
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->icons = array();

        if (!$this->import_checklist_plugin()) {
            $this->content->items = array(get_string('nochecklistplugin','block_checklist'));
            return $this->content;
        }

        if (!empty($this->config->checklistoverview)) {
            return $this->show_checklist_overview();
        }

        if (!empty($this->config->checklistid)) {
            return $this->show_single_checklist($this->config->checklistid);
        }

        // No checklist configured.
        $this->content->items = array(get_string('nochecklist','block_checklist'));
        return $this->content;
    }

    protected function show_single_checklist($checklistid) {
        global $DB, $CFG, $USER;

        if (!$checklist = $DB->get_record('checklist', array('id' => $checklistid))) {
            $this->content->items = array(get_string('nochecklist', 'block_checklist'));
            return $this->content;
        }
        if (!$cm = get_coursemodule_from_instance('checklist', $checklist->id, $checklist->course)) {
            $this->content->items = array('Error - course module not found');
            return $this->content;
        }
        if ($CFG->version < 2011120100) {
            $context = get_context_instance(CONTEXT_MODULE, $cm->id);
        } else {
            $context = context_module::instance($cm->id);
        }

        $viewallreports = has_capability('mod/checklist:viewreports', $context);
        $viewmenteereports = has_capability('mod/checklist:viewmenteereports', $context);
        $updateownchecklist = has_capability('mod/checklist:updateown', $context);

        // Show results for all users for a particular checklist.
        if ($viewallreports || $viewmenteereports) {
            $orderby = 'ORDER BY firstname ASC';
            $ausers = false;

            // Add the groups selector to the footer.
            $this->content->footer = $this->get_groups_menu($cm);
            $showgroup = $this->get_selected_group($cm);

            if ($users = get_users_by_capability($this->context, 'mod/checklist:updateown', 'u.id', '', '', '', $showgroup, '', false)) {
                $users = array_keys($users);
                if (!$viewallreports) { // can only see reports for their mentees
                    $users = checklist_class::filter_mentee_users($users);
                }
                if (!empty($users)) {
                    if ($CFG->version < 2013111800) {
                        $fields = 'u.firstname, u.lastname';
                    } else {
                        $fields = get_all_user_name_fields(true, 'u');
                    }
                    $ausers = $DB->get_records_sql("SELECT u.id, $fields FROM {user} u WHERE u.id IN (".implode(',',$users).') '.$orderby);
                }
            }

            if ($ausers) {
                $this->content->items = array();
                $reporturl = new moodle_url('/mod/checklist/report.php', array('id'=>$cm->id));
                foreach ($ausers as $auser) {
                    $link = '<a href="'.$reporturl->out(true, array('studentid'=>$auser->id)).'" >&nbsp;';
                    $this->content->items[] = $link.fullname($auser).checklist_class::print_user_progressbar($checklist->id, $auser->id, '50px', false, true).'</a>';
                }
            } else {
                $this->content->items = array(get_string('nousers','block_checklist'));
            }

        } else if ($updateownchecklist) {
            $viewurl = new moodle_url('/mod/checklist/view.php', array('id'=>$cm->id));
            $link = '<a href="'.$viewurl.'" >&nbsp;';
            $this->content->items = array($link.checklist_class::print_user_progressbar($checklist->id, $USER->id, '150px', false, true).'</a>');
        } else {
            $this->content = null;
        }

        return $this->content;
    }

    protected function show_checklist_overview() {
        global $COURSE, $DB;

        $allcourses = ($COURSE->format == 'site');
        if ($allcourses) {
            $mycourses = enrol_get_my_courses();
        } else {
            $mycourses = array($COURSE->id => $COURSE);
        }

        if (empty($mycourses)) {
            $this->content->items = array(get_string('notenrolled', 'block_checklist'));
            return $this->content;
        }

        $courseids = array();
        foreach ($mycourses as $id => $course) {
            $courseids[] = $id;
        }
        list($inorequal, $params) = $DB->get_in_or_equal($courseids, SQL_PARAMS_NAMED);
        $params['moduleid'] = $DB->get_field('modules', 'id', array('name' => 'checklist'));
        $sql = "SELECT ch.*, c.shortname, cm.id AS cmid
                    FROM {checklist} ch
                    JOIN {course} c ON ch.course = c.id
                    JOIN {course_modules} cm ON cm.instance = ch.id AND cm.module = :moduleid
                    WHERE ch.course $inorequal AND c.visible = 1";
        $checklists = $DB->get_records_sql($sql, $params);
        foreach ($checklists as $checklist) {
            $modinfo = get_fast_modinfo($checklist->course);
            $cminfo = $modinfo->get_cm($checklist->cmid);
            if (!$cminfo->uservisible) {
                // Hidden by show/hide, groupings, conditional access, etc.
                unset($checklists[$checklist->id]);
            }
        }

        $this->content->items = array();

        $checklists = $this->get_user_progress($checklists);
        $checklists = $this->sort_checklists($checklists, 20);

        foreach ($checklists as $checklist) {
            $viewurl = new moodle_url('/mod/checklist/view.php', array('id'=>$checklist->cmid));
            if ($allcourses) {
                $this->content->items[] = html_writer::tag('h4', $checklist->shortname);
            }
            $info = format_string($checklist->name);
            $info .= html_writer::empty_tag('br', array('class' => 'clearer'));
            $info .= $this->print_user_progressbar($checklist);
            $this->content->items[] = html_writer::link($viewurl, $info);
        }

        return $this->content;
    }

    /**
     * Get the progress for each checklist.
     *
     * @param object[] $checklists
     * @return object[]
     */
    protected function get_user_progress($checklists) {
        global $DB, $USER, $CFG;

        if (empty($checklists)) {
            return $checklists;
        }

        // Get all the items for all the checklists.
        list($csql, $params) = $DB->get_in_or_equal(array_keys($checklists), SQL_PARAMS_NAMED);
        $items = $DB->get_records_select('checklist_item', "checklist $csql AND userid = 0 AND itemoptional = ".CHECKLIST_OPTIONAL_NO." AND hidden = ".CHECKLIST_HIDDEN_NO, $params, 'checklist', 'id, checklist, grouping');
        if (empty($items)) {
            return $checklists;
        }

        // Get all the checks for this user for these items.
        list($isql, $params) = $DB->get_in_or_equal(array_keys($items), SQL_PARAMS_NAMED);
        $params['userid'] = $USER->id;
        $checkmarks = $DB->get_records_select('checklist_check', "item $isql AND userid = :userid", $params, 'item',
                                              'item, usertimestamp, teachermark');

        // If 'groupmembersonly' is enabled, get a list of groupings the user is a member of.
        $groupings = !empty($CFG->enablegroupmembersonly) && !empty($CFG->enablegroupmembersonly);
        $groupingids = array();
        if ($groupings) {
            $sql = "
            SELECT gs.groupingid
              FROM {groupings_groups} gs
              JOIN {groups_members} gm ON gm.groupid = gs.groupid
             WHERE gm.userid = ?
            ";
            $groupingids = $DB->get_fieldset_sql($sql, array($USER->id));
        }
        $checklist = null;

        // Loop through all items, counting those visible to the user and the total number of checkmarks for them.
        foreach ($items as $item) {
            $checklist = $checklists[$item->checklist];
            if ($groupings && $checklist->autopopulate) {
                // If the item has a grouping, check against the grouping memberships for this user.
                if ($item->grouping && !in_array($item->grouping, $groupingids)) {
                    continue;
                }
            }
            if (!isset($checklist->totalitems)) {
                $checklist->totalitems = 0;
                $checklist->checked = 0;
            }
            $checklist->totalitems++;
            if (isset($checkmarks[$item->id])) {
                if ($checklist->teacheredit == CHECKLIST_MARKING_STUDENT) {
                    if ($checkmarks[$item->id]->usertimestamp) {
                        $checklist->checked++;
                    }
                } else {
                    if ($checkmarks[$item->id]->teachermark == CHECKLIST_TEACHERMARK_YES) {
                        $checklist->checked++;
                    }
                }
            }
        }

        // Calculate the percentage for each checklist.
        foreach ($checklists as $checklist) {
            if (empty($checklist->totalitems)) {
                $checklist->percent = 0;
            } else {
                $checklist->percent = $checklist->checked * 100.0 / $checklist->totalitems;
            }
        }

        return $checklists;
    }

    /**
     * Sort the checklists (incomplete first, in ascending order of completeness,
     * unstarted next, then complete checklists).
     *
     * @param object[] $checklists
     * @param int $maxdisplay only return this many checklists
     * @return object[] the sorted checklists
     */
    protected function sort_checklists($checklists, $maxdisplay) {
        uasort($checklists, function($a, $b) {
            if ($a->percent == $b->percent) {
                return 0; // Same, so no defined sort order.
            }
            if ($a->percent == 0) {
                if ($b->percent == 100) {
                    return -1; // Completed checklists at the end.
                }
                return 1; // Incomplete checklist always before unstarted checklists.
            }
            if ($a->percent == 100) {
                return 1; // Completed checklists at the end.
            }
            if ($b->percent == 0) {
                return -1; // Unstarted checklists after incomplete checklists (but before completed).
            }
            if ($a->percent > $b->percent) {
                return 1;
            }
            return -1;
        });

        return array_slice($checklists, 0, $maxdisplay);
    }

    protected function print_user_progressbar($checklist) {
        global $OUTPUT;
        if (empty($checklist->totalitems)) {
            return '';
        }

        $percent = $checklist->checked * 100 / $checklist->totalitems;
        $width = '150px';

        $output = '<div class="checklist_progress_outer" style="width: '.$width.';" >';
        $output .= '<div class="checklist_progress_inner" style="width:'.$percent.'%; background-image: url('.$OUTPUT->pix_url('progress','checklist').');" >&nbsp;</div>';
        $output .= '</div>';
        $output .= '<br style="clear:both;" />';

        return $output;
    }

    function import_checklist_plugin() {
        global $CFG, $DB;

        $chk = $DB->get_record('modules', array('name'=>'checklist'));
        if (!$chk) {
            return false;
        }

        $version = get_config('mod_checklist', 'version');
        if (!$version && isset($chk->version)) {
            $version = $chk->version;
        }

        if ($version < 2010041800) {
            return false;
        }

        if (!file_exists($CFG->dirroot.'/mod/checklist/locallib.php')) {
            return false;
        }

        require_once($CFG->dirroot.'/mod/checklist/locallib.php');
        return true;
    }

    function get_groups_menu($cm) {
        global $COURSE, $OUTPUT, $USER, $CFG;

        if (!$groupmode = groups_get_activity_groupmode($cm)) {
            $this->get_selected_group($cm, null, true, true); // Make sure all users can be seen
            return '';
        }

        if ($CFG->version < 2011120100) {
            $context = get_context_instance(CONTEXT_MODULE, $cm->id);
        } else {
            $context = context_module::instance($cm->id);
        }
        $aag = has_capability('moodle/site:accessallgroups', $context);

        if ($groupmode == VISIBLEGROUPS or $aag) {
            $seeall = true;
            $allowedgroups = groups_get_all_groups($cm->course, 0, $cm->groupingid); // any group in grouping
        } else {
            $seeall = false;
            $allowedgroups = groups_get_all_groups($cm->course, $USER->id, $cm->groupingid); // only assigned groups
        }

        $selected = $this->get_selected_group($cm, $allowedgroups, $seeall);

        $groupsmenu = array();
        if (empty($allowedgroups) || $seeall) {
            $groupsmenu[0] = get_string('allparticipants');
        }
        if ($allowedgroups) {
            foreach ($allowedgroups as $group) {
                $groupsmenu[$group->id] = format_string($group->name);
            }
        }

        $baseurl = new moodle_url('/course/view.php', array('id' => $COURSE->id));
        if (count($groupsmenu) <= 1) {
            return '';
        }

        $select = new single_select($baseurl, 'group', $groupsmenu, $selected, null, 'selectgroup');
        $out = $OUTPUT->render($select);
        return html_writer::tag('div', $out, array('class' => 'groupselector'));
    }

    function get_selected_group($cm, $allowedgroups = null, $seeall = false, $forceall = false) {
        global $SESSION;

        if (!is_null($allowedgroups)) {
            if (!isset($SESSION->checklistgroup)) {
                $SESSION->checklistgroup = array();
            }
            $change = optional_param('group', -1, PARAM_INT);
            if ($change != -1) {
                $SESSION->checklistgroup[$cm->id] = $change;
            } else if (!isset($SESSION->checklistgroup[$cm->id])) {
                if (isset($this->config->groupid)) {
                    $SESSION->checklistgroup[$cm->id] = $this->config->groupid;
                } else {
                    $SESSION->checklistgroup[$cm->id] = 0;
                }
            }
            $groupok = (($SESSION->checklistgroup[$cm->id] == 0) && $seeall);
            $groupok = $groupok || array_key_exists($SESSION->checklistgroup[$cm->id], $allowedgroups);
            if (!$groupok) {
                $group = reset($allowedgroups);
                if ($group === false) {
                    unset($SESSION->checklistgroup[$cm->id]);
                } else {
                    $SESSION->checklistgroup[$cm->id] = $group->id;
                }
            }
        }
        if ($forceall || !isset($SESSION->checklistgroup[$cm->id])) {
            if ($seeall) {
                // No groups defined, but we can see all groups - return 0 => all users
                $SESSION->checklistgroup[$cm->id] = 0;
            } else {
                // No groups defined and we can't access groups outside out own - return -1 => no users
                $SESSION->checklistgroup[$cm->id] = -1;
            }
        }

        return $SESSION->checklistgroup[$cm->id];
    }
}
