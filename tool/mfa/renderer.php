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
 * MFA renderer.
 *
 * @package     tool_mfa
 * @author      Mikhail Golenkov <golenkovm@gmail.com>
 * @copyright   Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class tool_mfa_renderer extends plugin_renderer_base {

    /**
     * Returns the state of the factor as a badge
     *
     * @return html
     */
    public function get_state_badge($state) {

        switch ($state) {
            case \tool_mfa\plugininfo\factor::STATE_PASS:
                return \html_writer::tag('span', get_string('state:pass', 'tool_mfa'), array('class' => 'badge badge-success'));

            case \tool_mfa\plugininfo\factor::STATE_FAIL:
                return \html_writer::tag('span', get_string('state:fail', 'tool_mfa'), array('class' => 'badge badge-danger'));

            case \tool_mfa\plugininfo\factor::STATE_NEUTRAL:
                return \html_writer::tag('span', get_string('state:neutral', 'tool_mfa'), array('class' => 'badge badge-warning'));

            case \tool_mfa\plugininfo\factor::STATE_UNKNOWN:
                return \html_writer::tag('span', get_string('state:unknown', 'tool_mfa'),
                        array('class' => 'badge badge-secondary'));

            case \tool_mfa\plugininfo\factor::STATE_LOCKED:
                return \html_writer::tag('span', get_string('state:locked', 'tool_mfa'), array('class' => 'badge badge-error'));

            default:
                return \html_writer::tag('span', get_string('pending', 'tool_mfa'), array('class' => 'badge badge-secondary'));
        }
    }

    /**
     * Returns a list of factors which a user can add
     *
     * @return html
     */
    public function available_factors() {
        $html = $this->output->heading(get_string('preferences:availablefactors', 'tool_mfa'), 2);

        $factors = \tool_mfa\plugininfo\factor::get_enabled_factors();
        foreach ($factors as $factor) {

            // TODO is_configured / is_ready.
            if (!$factor->has_setup() || !$factor->show_setup_buttons()) {
                continue;
            }
            $html .= $this->setup_factor($factor);
        }

        return $html;
    }

    public function setup_factor($factor) {
        $html = '';

        $html .= html_writer::start_tag('div', array('class' => 'card'));

        $html .= html_writer::tag('h4', $factor->get_display_name(), array('class' => 'card-header'));
        $html .= html_writer::start_tag('div', array('class' => 'card-body'));
        $html .= $factor->get_info();

        $setupparams = array('action' => 'setup', 'factor' => $factor->name, 'sesskey' => sesskey());
        $setupurl = new \moodle_url('action.php', $setupparams);
        $html .= $this->output->single_button($setupurl, $factor->get_setup_string());
        $html .= html_writer::end_tag('div');
        $html .= html_writer::end_tag('div');
        $html .= '<br>';

        return $html;
    }

    /**
     * Defines section with active user's factors.
     *
     * @return string $html
     * @throws \coding_exception
     */
    public function active_factors() {
        global $USER, $CFG;

        require_once($CFG->dirroot . '/iplookup/lib.php');

        $html = $this->output->heading(get_string('preferences:activefactors', 'tool_mfa'), 2);

        $headers = get_strings(array(
            'factor',
            'devicename',
            'created',
            'createdfromip',
            'lastverified',
            'revoke',
        ), 'tool_mfa');

        $table = new \html_table();
        $table->id = 'active_factors';
        $table->attributes['class'] = 'generaltable table table-bordered';
        $table->head  = array(
            $headers->factor,
            $headers->devicename,
            $headers->created,
            $headers->createdfromip,
            $headers->lastverified,
            $headers->revoke,
        );
        $table->colclasses = array(
            'leftalign',
            'leftalign',
            'centeralign',
            'centeralign',
            'centeralign',
            'centeralign',
            'centeralign',
            'centeralign',
        );
        $table->data  = array();

        $factors = \tool_mfa\plugininfo\factor::get_enabled_factors();

        foreach ($factors as $factor) {

            $userfactors = $factor->get_active_user_factors($USER);

            if (!$factor->has_setup()) {
                continue;
            }

            foreach ($userfactors as $userfactor) {
                if ($factor->has_revoke()) {
                    $revokeparams = array('action' => 'revoke', 'factor' => $factor->name,
                        'factorid' => $userfactor->id, 'sesskey' => sesskey());
                    $revokeurl = new \moodle_url('action.php', $revokeparams);
                    $revokelink = \html_writer::link($revokeurl, $headers->revoke);
                } else {
                    $revokelink = "";
                }

                $timecreated  = $userfactor->timecreated == '-' ? '-'
                    : userdate($userfactor->timecreated,  get_string('strftimedatetime'));
                $lastverified = $userfactor->lastverified;
                if ($lastverified != '-') {
                    $lastverified = userdate($userfactor->lastverified, get_string('strftimedatetime'));
                    $lastverified .= '<br>';
                    $lastverified .= get_string('ago', 'core_message', format_time(time() - $userfactor->lastverified));
                }

                $info = iplookup_find_location($userfactor->createdfromip);
                $ip = $userfactor->createdfromip;
                $ip .= '<br>' . $info['country'] . ' - ' . $info['city'];

                $row = new \html_table_row(array(
                    $factor->get_display_name(),
                    $userfactor->label,
                    $timecreated,
                    $ip,
                    $lastverified,
                    $revokelink,
                ));
                $table->data[] = $row;
            }
        }
        // If table has no data, don't output.
        if (count($table->data) == 0) {
            return '';
        }
        $html .= \html_writer::table($table);
        $html .= '<br>';

        return $html;
    }

    /**
     * Generates notification text for display when user cannot login.
     *
     * @return string $notification
     */
    public function not_enough_factors() {
        global $CFG, $SITE;

        $notification = \html_writer::tag('h4', get_string('error:notenoughfactors', 'tool_mfa'));
        $notification .= \html_writer::tag('p', get_string('error:reauth', 'tool_mfa'));

        // Support link.
        $supportemail = $CFG->supportemail;
        if (!empty($supportemail)) {
            $subject = get_string('email:subject', 'tool_mfa', $SITE->fullname);
            $maillink = \html_writer::link("mailto:$supportemail?Subject=$subject", $supportemail);
            $notification .= get_string('error:support', 'tool_mfa');
            $notification .= \html_writer::tag('p', $maillink);
        }

        // Support page link.
        $supportpage = $CFG->supportpage;
        if (!empty($supportpage)) {
            $linktext = \html_writer::link($supportpage, $supportpage);
            $notification .= $linktext;
        }
        $return = $this->output->notification($notification, 'notifyerror');

        // Logout button.
        $url = new \moodle_url('/admin/tool/mfa/auth.php', ['logout' => 1]);
        $btn = new \single_button($url, get_string('logout'), 'post', true);
        $return .= $this->render($btn);

        $return .= $this->guide_link();

        return $return;
    }

    /**
     * Displays a table of all factors in use currently.
     *
     * @param int $lookback the period to view.
     * @return string the HTML for the table
     */
    public function factors_in_use_table($lookback) {
        global $DB;

        $factors = \tool_mfa\plugininfo\factor::get_factors();

        // Setup 2 arrays, one with internal names, one pretty.
        $columns = array('');
        $displaynames = $columns;
        $colclasses = array('center', 'center', 'center', 'center', 'center');

        // Force the first 4 columns to custom data.
        $displaynames[] = get_string('totalusers', 'tool_mfa');
        $displaynames[] = get_string('usersauthedinperiod', 'tool_mfa');
        $displaynames[] = get_string('nonauthusers', 'tool_mfa');
        $displaynames[] = get_string('nologinusers', 'tool_mfa');

        foreach ($factors as $factor) {
            $columns[] = $factor->name;
            $displaynames[] = get_string('pluginname', 'factor_'.$factor->name);
            $colclasses[] = 'right';
        }

        // Add total column to the end.
        $displaynames[] = get_string('total');
        $colclasses[] = 'center';

        $table = new \html_table();
        $table->head = $displaynames;
        $table->align = $colclasses;
        $table->attributes['class'] = 'generaltable table table-bordered w-auto';
        $table->attributes['style'] = 'width: auto; min-width: 50%; margin-bottom: 0;';

        // Manually handle Total users and MFA users.
        $alluserssql = "SELECT auth,
                            COUNT(id)
                        FROM {user}
                        WHERE deleted = 0
                        AND suspended = 0
                    GROUP BY auth";
        $allusersinfo = $DB->get_records_sql($alluserssql, []);

        $noncompletesql = "SELECT u.auth, COUNT(u.id)
                             FROM {user} u
                        LEFT JOIN {tool_mfa_auth} mfaa ON u.id = mfaa.userid
                            WHERE u.lastlogin >= ?
                              AND (mfaa.lastverified < ?
                               OR mfaa.lastverified IS NULL)
                         GROUP BY u.auth";
        $noncompleteinfo = $DB->get_records_sql($noncompletesql, [$lookback, $lookback]);

        $nologinsql = "SELECT auth, COUNT(id)
                         FROM {user}
                        WHERE deleted = 0
                          AND suspended = 0
                          AND lastlogin < ?
                     GROUP BY auth";
        $nologininfo = $DB->get_records_sql($nologinsql, [$lookback]);

        $mfauserssql = "SELECT auth,
                            COUNT(DISTINCT tm.userid)
                        FROM {tool_mfa} tm
                        JOIN {user} u ON u.id = tm.userid
                        WHERE tm.lastverified >= ?
                        AND u.deleted = 0
                        AND u.suspended = 0
                    GROUP BY u.auth";
        $mfausersinfo = $DB->get_records_sql($mfauserssql, [$lookback]);

        $factorsusedsql = "SELECT CONCAT(u.auth, '_', tm.factor) as id,
                                COUNT(*)
                            FROM {tool_mfa} tm
                            JOIN {user} u ON u.id = tm.userid
                            WHERE tm.lastverified >= ?
                            AND u.deleted = 0
                            AND u.suspended = 0
                            AND (tm.revoked = 0 OR (tm.revoked = 1 AND tm.timemodified > ?))
                        GROUP BY CONCAT(u.auth, '_', tm.factor)";
        $factorsusedinfo = $DB->get_records_sql($factorsusedsql, [$lookback, $lookback]);

        // Auth rows.
        $authtypes = get_enabled_auth_plugins(true);
        foreach ($authtypes as $authtype) {
            $row = array();
            $row[] = \html_writer::tag('b', $authtype);

            // Setup the overall totals columns.
            $row[] = $allusersinfo[$authtype]->count ?? '-';
            $row[] = $mfausersinfo[$authtype]->count ?? '-';
            $row[] = $noncompleteinfo[$authtype]->count ?? '-';
            $row[] = $nologininfo[$authtype]->count ?? '-';

            // Create a running counter for the total.
            $authtotal = 0;

            // Now for each factor add the count from the factor query, and increment the running total.
            foreach ($columns as $column) {
                if (!empty($column)) {
                    // Get the information from the data key.
                    $key = $authtype . '_' . $column;
                    $count = $factorsusedinfo[$key]->count ?? 0;
                    $authtotal += $count;

                    $row[] = $count ? format_float($count, 0) : '-';
                }
            }

            // Append the total of all factors to final column.
            $row[] = $authtotal ? format_float($authtotal, 0) : '-';

            $table->data[] = $row;
        }

        // Total row.
        $totals = [0 => html_writer::tag('b', get_string('total'))];
        for ($colcounter = 1; $colcounter < count($row); $colcounter++) {
            $column = array_column($table->data, $colcounter);
            // Transform string to int forcibly, remove -.
            $column = array_map(function($element) {
                return $element === '-' ? 0 : (int) $element;
            }, $column);
            $columnsum = array_sum($column);
            $colvalue = $columnsum === 0 ? '-' : $columnsum;
            $totals[$colcounter] = $colvalue;
        }
        $table->data[] = $totals;

        // Wrap in a div to cleanly scroll.
        return \html_writer::div(\html_writer::table($table), '', ['style' => 'overflow:auto;']);
    }

    /**
     * Displays a table of all factors in use currently.
     *
     * @return string the HTML for the table
     */
    public function factors_locked_table() {
        global $DB;

        $factors = \tool_mfa\plugininfo\factor::get_factors();

        $table = new \html_table();

        $table->attributes['class'] = 'generaltable table table-bordered w-auto';
        $table->attributes['style'] = 'width: auto; min-width: 50%';

        $table->head = [
            'factor' => get_string('factor', 'tool_mfa'),
            'active' => get_string('active'),
            'locked' => get_string('state:locked', 'tool_mfa'),
            'actions' => get_string('actions')
        ];
        $table->align = [
            'left',
            'left',
            'right',
            'right'
        ];
        $table->data = [];
        $locklevel = (int) get_config('tool_mfa', 'lockout');

        foreach ($factors as $factor) {
            $sql = "SELECT COUNT(DISTINCT(userid))
                      FROM {tool_mfa}
                     WHERE factor = ?
                       AND lockcounter >= ?
                       AND revoked = 0";
            $lockedusers = $DB->count_records_sql($sql, [$factor->name, $locklevel]);
            $enabled = $factor->is_enabled() ? \html_writer::tag('b', get_string('yes')) : get_string('no');

            $actions = \html_writer::link( new moodle_url($this->page->url,
                ['reset' => $factor->name, 'sesskey' => sesskey()]), get_string('performbulk', 'tool_mfa'));
            $lockedusers = \html_writer::link(new moodle_url($this->page->url, ['view' => $factor->name]), $lockedusers);

            $table->data[] = [
                $factor->get_display_name(),
                $enabled,
                $lockedusers,
                $actions
            ];
        }

        return \html_writer::table($table);
    }

    /**
     * Displays a table of all users with a locked instance of the given factor.
     *
     * @return string the HTML for the table
     */
    public function factor_locked_users_table($factor) {
        global $DB;

        $table = new html_table();
        $table->attributes['class'] = 'generaltable table table-bordered w-auto';
        $table->attributes['style'] = 'width: auto; min-width: 50%';
        $table->head = [
            'userid' => get_string('userid', 'grades'),
            'fullname' => get_string('fullname'),
            'factorip' => get_string('ipatcreation', 'tool_mfa'),
            'lastip' => get_string('lastip'),
            'modified' => get_string('modified'),
            'actions' => get_string('actions')
        ];
        $table->align = [
            'left',
            'left',
            'left',
            'left',
            'left',
            'right'
        ];
        $table->data = [];

        $locklevel = (int) get_config('tool_mfa', 'lockout');
        $sql = "SELECT mfa.id as mfaid, u.*, mfa.createdfromip, mfa.timemodified
                  FROM {tool_mfa} mfa
                  JOIN {user} u ON mfa.userid = u.id
                 WHERE factor = ?
                   AND lockcounter >= ?
                   AND revoked = 0";
        $records = $DB->get_records_sql($sql, [$factor->name, $locklevel]);

        foreach ($records as $record) {

            // Construct profile link.
            $proflink = \html_writer::link(new moodle_url('/user/profile.php',
                ['id' => $record->id]), fullname($record));

            // IP link.
            $creatediplink = \html_writer::link(new moodle_url('/iplookup/index.php',
                ['ip' => $record->createdfromip]), $record->createdfromip);
            $lastiplink = \html_writer::link(new moodle_url('/iplookup/index.php',
                ['ip' => $record->lastip]), $record->lastip);

            // Deep link to logs
            $logicon = $this->pix_icon('i/report', get_string('userlogs', 'tool_mfa'));
            $actions = \html_writer::link(new moodle_url('/report/log/index.php', [
                'id' => 1, // Site.
                'user' => $record->id
            ]), $logicon);

            $action = new confirm_action(get_string('resetfactorconfirm', 'tool_mfa', fullname($record)));
            $actions .= $this->action_link(
                new moodle_url($this->page->url, ['reset' => $factor->name, 'id' => $record->id, 'sesskey' => sesskey()]),
                $this->pix_icon('t/delete', get_string('resetconfirm', 'tool_mfa')),
                $action
            );

            $table->data[] = [
                $record->id,
                $proflink,
                $creatediplink,
                $lastiplink,
                userdate($record->timemodified, get_string('strftimedatetime', 'langconfig')),
                $actions
            ];
        }

        return \html_writer::table($table);
    }

    public function guide_link() {
        if (!get_config('tool_mfa', 'guidance')) {
            return '';
        }
        $html = $this->heading(get_string('needhelp', 'tool_mfa'), 3);
        $html .= $this->render_from_template('tool_mfa/guide_link', []);
        return $this->notification($html, 'info');
    }

    public function mform_element($element, $required, $advanced, $error, $ingroup) {
        $script = null;
        if ($element instanceof tool_mfa\local\form\verification_field) {
            if ($this->page->pagelayout === 'secure') {
                $script = $element->secure_js();
            }
        }

        $result = parent::mform_element($element, $required, $advanced, $error, $ingroup);

        if (!empty($script)) {
            $result .= $script;
        }

        return $result;
    }
}
