<?php
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
 * local_discoursesso
 *
 *
 * @package    local
 * @subpackage discoursesso
 * @copyright  2019 Saylor Academy
 * @author     John Azinheira
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require_once($CFG->dirroot . '/user/selector/lib.php');
require_once(__DIR__ . '/vendor/discourse-api-php/lib/DiscourseAPI.php');

function local_discoursesso_cohort_deleted_handler($event) {
    global $CFG, $DB, $OUTPUT;
    // Since a cohort was deleted, check if it is in our list of 
    // cohorts to sync. If so, remove the group from the Discourse server
    // and from the discoursesso_cohorts table.
    $cohort = $DB->get_record('discoursesso_cohorts', array('cohortid' => $event->objectid), $fields='*', $strictness=IGNORE_MISSING);
    if ($cohort != false) {
        // First, delete the group from Discourse.
        if (empty($CFG->discoursesso_api_key)) {
            // No API key was found.
            echo $OUTPUT->notification(get_string('errornoapikey', 'local_discoursesso'), \core\output\notification::NOTIFY_WARNING);
            return false;
        }

        $api = new DiscourseAPI(preg_replace("(^https?://)", "", $CFG->discoursesso_discourse_url), $CFG->discoursesso_api_key, preg_replace("(://.+)", "", $CFG->discoursesso_discourse_url));
        
        $r = $api->deleteGroup(clean_name($cohort->cohortname));

        if (!($DB->delete_records('discoursesso_cohorts', array('cohortid' => $cohort->cohortid)))) {
            
            return false;
        }

    }

    return true;
}

function get_discourse_locale($moodleuserlang) {
    switch ($moodleuserlang) {
        // For these specific locales, map to Moodle's lang code.
        // For everything else, cut off anything after underscore ie
        // es_mx and es_co will return es.
        // TODO: Moodle has A LOT more languages than Discourse... how to handle those?
        case "bs":
            $discourselocale = "bs_BA";
            break;
        case "fa":
            $discourselocale = "fa_IR";
            break;
        case "no":
            $discourselocale = "nb_NO";
            break;
        case "pl":
            $discourselocale = "pl_PL";
            break;
        case "pt_br":
            $discourselocale = "pt_BR";
            break;
        case "tr":
            $discourselocale = "tr_TR";
            break;
        case "zh_cn":
            $discourselocale = "zh_CN";
            break;
        case "zh_tw":
            $discourselocale = "zh_TW";
            break;
         default:
            $discourselocale = preg_replace('~(_[a-zA-Z0-9]+)+~s', '', $moodleuserlang);
    }
    return $discourselocale;
}

function clean_name($name) {
    $cleaned = preg_replace('/[^\p{L}\p{N}]/u', '_', $name);
    $cleaned = rtrim($cleaned, '_');

    return $cleaned;
}

/**
* Add cohort to database as group to sync
* @param int $cohortid
* @return bool
*/
function discoursesso_add_group($cohortid) {
    global $CFG, $DB, $OUTPUT;

    // Make sure the cohort is not already present.
    if ($DB->get_record('discoursesso_cohorts', array('cohortid' => $cohortid), $fields='*', $strictness=IGNORE_MISSING)) {
        
        return false;
    }
    // Make sure that there is an API key set.
    if (empty($CFG->discoursesso_api_key)) {
        // No API key was found.
        echo $OUTPUT->notification(get_string('errornoapikey', 'local_discoursesso'), \core\output\notification::NOTIFY_WARNING);
        return false;
    }

    $cohort = $DB->get_record('cohort', array('id' => $cohortid), 'name', MUST_EXIST);

    $ssocohort = new stdClass;
    $ssocohort->cohortid = $cohortid;
    $ssocohort->cohortname = $cohort->name;

    $api = new DiscourseAPI(preg_replace("(^https?://)", "", $CFG->discoursesso_discourse_url), $CFG->discoursesso_api_key, preg_replace("(://.+)", "", $CFG->discoursesso_discourse_url));

    // Create the group in Discourse.
    $r = $api->createGroup(clean_name($ssocohort->cohortname));

    if ($r->http_code != 200) {
        echo $OUTPUT->notification(get_string('errorcreategroupdiscourse', 'local_discoursesso', clean_name($ssocohort->cohortname))."<br>Response: [".$r->http_code."] ".$r->apiresult->errors[0], \core\output\notification::NOTIFY_WARNING);
        return false;
    }

    if (!($DB->insert_record('discoursesso_cohorts', $ssocohort, false))) {
        echo $OUTPUT->notification(get_string('errorcreaterecorddb', 'local_discoursesso'), \core\output\notification::NOTIFY_WARNING);
        return false;
    }

    return true;
}

/**
 * Remove cohort from database as group to sync
 * @param int $cohortid
 * @return bool
 */
 function discoursesso_remove_group($cohortid) {
    global $CFG, $DB, $OUTPUT;

    // First, delete the group from Discourse.
    if (empty($CFG->discoursesso_api_key)) {
        // No API key was found.
        echo $OUTPUT->notification(get_string('errornoapikey', 'local_discoursesso'), \core\output\notification::NOTIFY_WARNING);
        return false;
    }
    $api = new DiscourseAPI(preg_replace("(^https?://)", "", $CFG->discoursesso_discourse_url), $CFG->discoursesso_api_key, preg_replace("(://.+)", "", $CFG->discoursesso_discourse_url));
    $cohort = $DB->get_record('cohort', array('id' => $cohortid), 'name', MUST_EXIST);
    
    $r = $api->deleteGroup(clean_name($cohort->name));

    if (!($DB->delete_records('discoursesso_cohorts', array('cohortid' => $cohortid)))) {
        echo $OUTPUT->notification(get_string('errordeleterecorddb', 'local_discoursesso'), \core\output\notification::NOTIFY_WARNING);
        return false;
    }

    return true;
 }

abstract class cohort_selector_base extends user_selector_base {
    /** @var array JavaScript YUI3 Module definition */
    protected static $jsmodule = array(
                'name' => 'selector',
                'fullpath' => '/local/discoursesso/selector/module.js',
                'requires'  => array('node', 'event-custom', 'datasource', 'json', 'moodle-core-notification'),
                'strings' => array(
                    array('previouslyselectedusers', 'moodle', '%%SEARCHTERM%%'),
                    array('nomatchingusers', 'moodle', '%%SEARCHTERM%%'),
                    array('none', 'moodle')
                ));
    /**  @var boolean Used to ensure we only output the search options for one user selector on
     * each page. */
    private static $searchoptionsoutput = false;


    /**
     * Output one of the options checkboxes.
     *
     * @param string $name
     * @param string $on
     * @param string $label
     * @return string
     */
    private function option_checkbox($name, $on, $label) {
        if ($on) {
            $checked = ' checked="checked"';
        } else {
            $checked = '';
        }
        $name = 'userselector_' . $name;
        // For the benefit of brain-dead IE, the id must be different from the name of the hidden form field above.
        // It seems that document.getElementById('frog') in IE will return and element with name="frog".
        $output = '<div class="form-check"><input type="hidden" name="' . $name . '" value="0" />' .
                    '<label class="form-check-label" for="' . $name . 'id">' .
                        '<input class="form-check-input" type="checkbox" id="' . $name . 'id" name="' . $name .
                            '" value="1"' . $checked . ' /> ' . $label .
                    "</label>
                   </div>\n";
        user_preference_allow_ajax_update($name, PARAM_BOOL);
        return $output;
    }
    /**
     * Initialises JS for this control.
     *
     * @param string $search
     * @return string any HTML needed here.
     */
    protected function initialise_javascript($search) {
        global $USER, $PAGE, $OUTPUT;
        $output = '';

        // Put the options into the session, to allow search.php to respond to the ajax requests.
        $options = $this->get_options();
        $hash = md5(serialize($options));
        $USER->userselectors[$hash] = $options;

        // Initialise the selector.
        $PAGE->requires->js_init_call(
            'M.local_discoursesso.init_selector',
            array($this->name, $hash, $this->extrafields, $search),
            false,
            self::$jsmodule
        );
        return $output;
    }
    /**
     * Output this user_selector as HTML.
     *
     * @param boolean $return if true, return the HTML as a string instead of outputting it.
     * @return mixed if $return is true, returns the HTML as a string, otherwise returns nothing.
     */
    public function display($return = false) {
        global $PAGE;

        // Get the list of requested users.
        $search = optional_param($this->name . '_searchtext', '', PARAM_RAW);
        if (optional_param($this->name . '_clearbutton', false, PARAM_BOOL)) {
            $search = '';
        }
        $groupedusers = $this->find_users($search);

        // Output the select.
        $name = $this->name;
        $multiselect = '';
        if ($this->multiselect) {
            $name .= '[]';
            $multiselect = 'multiple="multiple" ';
        }
        $output = '<div class="userselector" id="' . $this->name . '_wrapper">' . "\n" .
                '<select name="' . $name . '" id="' . $this->name . '" ' .
                $multiselect . 'size="' . $this->rows . '" class="form-control no-overflow">' . "\n";

        // Populate the select.
        $output .= $this->output_options($groupedusers, $search);

        // Output the search controls.
        $output .= "</select>\n<div class=\"form-inline\">\n";
        $output .= '<input type="text" name="' . $this->name . '_searchtext" id="' .
                $this->name . '_searchtext" size="15" value="' . s($search) . '" class="form-control"/>';
        $output .= '<input type="submit" name="' . $this->name . '_searchbutton" id="' .
                $this->name . '_searchbutton" value="' . $this->search_button_caption() . '" class="btn btn-secondary"/>';
        $output .= '<input type="submit" name="' . $this->name . '_clearbutton" id="' .
                $this->name . '_clearbutton" value="' . get_string('clear') . '" class="btn btn-secondary"/>';

        // And the search options.
        $optionsoutput = false;
        $output .= "</div>\n</div>\n\n";

        // Return or output it.
        if ($return) {
            return $output;
        } else {
            echo $output;
        }
    }

    /**
     * Used to generate a nice message when there are too many cohorts to show.
     *
     * The message includes the number of cohorts that currently match, and the
     * text of the message depends on whether the search term is non-blank.
     *
     * @param string $search the search term, as passed in to the find users method.
     * @param int $count the number of users that currently match.
     * @return array in the right format to return from the find_users method.
     */
    protected function too_many_results($search, $count) {
        if ($search) {
            $a = new stdClass;
            $a->count = $count;
            $a->search = $search;
            return array(get_string('toomanycohortsmatchsearch', 'local_discoursesso', $a) => array(),
                    get_string('pleasesearchmore') => array());
        } else {
            return array(get_string('toomanycohortstoshow', 'local_discoursesso', $count) => array(),
                    get_string('pleaseusesearch') => array());
        }
    }
    /**
     * Convert a cohort object to a string suitable for displaying as an option in the list box.
     *
     * @param object $cohort the user to display.
     * @return string a string representation of the user.
     */
    public function output_user($cohort) {
        $out = $cohort->name;
        $displayfields = array();
        $displayfields[] = $cohort->description;
        $out .= ' (' . implode(', ', $displayfields) . ')';

        return $out;
    }
}

/**
 * Cohort assignment candidates
 */
class discoursesso_cohort_candidate_selector extends cohort_selector_base {
    protected $cohortid;

    public function __construct($name, $options) {
        parent::__construct($name, $options);
    }

    /**
     * Candidate users
     * @param string $search
     * @return array
     */
    public function find_users($search) {
        global $DB;
        $wherecondition = " WHERE ".$DB->sql_like('name', ':name', false);
        $params = array('name' => '%'.$DB->sql_like_escape($search).'%');

        // What do we sort by?
        $sort = "c.name";
        $sortparams = array();

        $order = ' ORDER BY ' . $sort;

        $fields      = 'SELECT ' . implode(', ', array(
            'c.id as id',
            'c.name as name',
            'c.description as description'
        ));
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM {cohort} c";

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql . $wherecondition, $params);

            if ($potentialmemberscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $wherecondition . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }


        if ($search) {
            $groupname = get_string('potcohortsmatching', 'local_discoursesso', $search);
        } else {
            $groupname = get_string('potcohorts', 'local_discoursesso');
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        //$options['file'] = 'local/discoursesso/locallib.php';
        return $options;
    }
}


/**
 * Cohort assignment candidates
 */
class discoursesso_cohort_existing_selector extends cohort_selector_base {
    protected $cohortid;

    public function __construct($name, $options) {
        parent::__construct($name, $options);
    }

    /**
     * Candidate users
     * @param string $search
     * @return array
     */
    public function find_users($search) {
        global $DB;
        $wherecondition = "";
        $params = array();

        // What do we sort by?
        $sort = "c.name";
        $sortparams = array();

        $order = ' ORDER BY ' . $sort;

        $fields      = 'SELECT ' . implode(',', array(
            'c.id',
            'c.name',
            'c.description'
        ));
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM {discoursesso_cohorts} dc
            INNER JOIN {cohort} c ON (c.id = dc.cohortid)";

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }


        if ($search) {
            $groupname = get_string('currentcohortsmatching', 'local_discoursesso', $search);
        } else {
            $groupname = get_string('currentcohorts', 'local_discoursesso');
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        //$options['file'] = 'local/discoursesso/locallib.php';
        return $options;
    }
}