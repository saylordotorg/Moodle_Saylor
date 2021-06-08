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

namespace theme_saylor\output;

use moodle_url;
use html_writer;
use context_course;
use stdClass;
use action_menu_filler;
use action_menu;
use pix_icon;
use action_menu_link_secondary;
use theme_saylor\output;
use core_course;

defined('MOODLE_INTERNAL') || die;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_saylor
 * @copyright  2012 Bas Brands, www.basbrands.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \core_renderer {

    /**
     * Returns standard navigation between activities in a course.
     *
     * @return string the navigation HTML.
     */
    public function activity_navigation() {
        global $CFG;
        require_once($CFG->dirroot . '/theme/saylor/classes/output/activity_navigation.php');
        // First we should check if we want to add navigation.
        $context = $this->page->context;
        if (($this->page->pagelayout !== 'incourse' && $this->page->pagelayout !== 'frametop')
            || $context->contextlevel != CONTEXT_MODULE) {
            return '';
        }

        // If the activity is in stealth mode, show no links.
        if ($this->page->cm->is_stealth()) {
            return '';
        }

        // Get a list of all the activities in the course.
        $course = $this->page->cm->get_course();
        $modules = get_fast_modinfo($course->id)->get_cms();

        // Put the modules into an array in order by the position they are shown in the course.
        $mods = [];
        $activitylist = [];
        foreach ($modules as $module) {
            // Only add activities the user can access, aren't in stealth mode and have a url (eg. mod_label does not).
            if (!$module->uservisible || $module->is_stealth() || empty($module->url)) {
                continue;
            }
            $mods[$module->id] = $module;

            // No need to add the current module to the list for the activity dropdown menu.
            if ($module->id == $this->page->cm->id) {
                continue;
            }
            // Module name.
            $modname = $module->get_formatted_name();
            // Display the hidden text if necessary.
            if (!$module->visible) {
                $modname .= ' ' . get_string('hiddenwithbrackets');
            }
            // Module URL.
            $linkurl = new moodle_url($module->url, array('forceview' => 1));
            // Add module URL (as key) and name (as value) to the activity list array.
            $activitylist[$linkurl->out(false)] = $modname;
        }

        $nummods = count($mods);

        // If there is only one mod then do nothing.
        if ($nummods == 1) {
            return '';
        }

        // Get an array of just the course module ids used to get the cmid value based on their position in the course.
        $modids = array_keys($mods);

        // Get the position in the array of the course module we are viewing.
        $position = array_search($this->page->cm->id, $modids);

        $prevmod = null;
        $nextmod = null;

        // Check if we have a previous mod to show.
        if ($position > 0) {
            $prevmod = $mods[$modids[$position - 1]];
        }

        // Check if we have a next mod to show.
        if ($position < ($nummods - 1)) {
            $nextmod = $mods[$modids[$position + 1]];
        }

        $activitynav = new \theme_saylor\output\core_course\activity_navigation($prevmod, $nextmod, $activitylist);
        $renderer = $this->page->get_renderer('theme_saylor', 'core_course\core_course');;
        return $renderer->render($activitynav);
    }

    public function edit_button(moodle_url $url) {
        $url->param('sesskey', sesskey());
        if ($this->page->user_is_editing()) {
            $url->param('edit', 'off');
            $editstring = get_string('turneditingoff');
        } else {
            $url->param('edit', 'on');
            $editstring = get_string('turneditingon');
        }
        $button = new \single_button($url, $editstring, 'post', ['class' => 'btn btn-primary']);
        return $this->render_single_button($button);
    }


    public function user_menu($user = null, $withlinks = null) {
        global $USER, $CFG;
        require_once($CFG->dirroot . '/user/lib.php');

        if (is_null($user)) {
            $user = $USER;
        }

        // Note: this behaviour is intended to match that of core_renderer::login_info,
        // but should not be considered to be good practice; layout options are
        // intended to be theme-specific. Please don't copy this snippet anywhere else.
        if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

        // Add a class for when $withlinks is false.
        $usermenuclasses = 'usermenu';
        if (!$withlinks) {
            $usermenuclasses .= ' withoutlinks';
        }

        $returnstr = "";

        // If during initial install, return the empty return string.
        if (during_initial_install()) {
            return $returnstr;
        }

        $loginpage = $this->is_login_page();
        $loginurl = get_login_url();
        // If not logged in, show the typical not-logged-in string.
        if (!isloggedin()) {
            $returnstr = get_string('loggedinnotgreeting', 'theme_saylor');
            if (!$loginpage) {
                $returnstr .= " <a href=\"$loginurl\">".get_string('logintext', 'theme_saylor').'</a>';
            }
            return html_writer::div(
                html_writer::span(
                    $returnstr,
                    'login'
                ),
                $usermenuclasses
            );

        }

        // If logged in as a guest user, show a string to that effect.
        if (isguestuser()) {
            $returnstr = get_string('loggedinnotgreeting', 'theme_saylor');
            if (!$loginpage && $withlinks) {
                $returnstr .= " <a href=\"$loginurl\">".get_string('logintext', 'theme_saylor').'</a>';
            }

            return html_writer::div(
                html_writer::span(
                    $returnstr,
                    'login'
                ),
                $usermenuclasses
            );
        }

        // Get some navigation opts.
        $opts = user_get_user_navigation_info($user, $this->page);

        $avatarclasses = "avatars";
        $avatarcontents = html_writer::span($opts->metadata['useravatar'], 'avatar current');
        $usertextcontents = get_string('loggedingreeting', 'theme_saylor', $opts->metadata['userfullname']);

        // Other user.
        if (!empty($opts->metadata['asotheruser'])) {
            $avatarcontents .= html_writer::span(
                $opts->metadata['realuseravatar'],
                'avatar realuser'
            );
            $usertextcontents = $opts->metadata['realuserfullname'];
            $usertextcontents .= html_writer::tag(
                'span',
                get_string(
                    'loggedinas',
                    'moodle',
                    html_writer::span(
                        $opts->metadata['userfullname'],
                        'value'
                    )
                ),
                array('class' => 'meta viewingas')
            );
        }

        // Role.
        if (!empty($opts->metadata['asotherrole'])) {
            $role = core_text::strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['rolename'])));
            $usertextcontents .= html_writer::span(
                $opts->metadata['rolename'],
                'meta role role-' . $role
            );
        }

        // User login failures.
        if (!empty($opts->metadata['userloginfail'])) {
            $usertextcontents .= html_writer::span(
                $opts->metadata['userloginfail'],
                'meta loginfailures'
            );
        }

        // MNet.
        if (!empty($opts->metadata['asmnetuser'])) {
            $mnet = strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['mnetidprovidername'])));
            $usertextcontents .= html_writer::span(
                $opts->metadata['mnetidprovidername'],
                'meta mnet mnet-' . $mnet
            );
        }

        $returnstr .= html_writer::span(
            html_writer::span($usertextcontents, 'usertext') .
            html_writer::span($avatarcontents, $avatarclasses),
            'userbutton'
        );

        // Create a divider (well, a filler).
        $divider = new action_menu_filler();
        $divider->primary = false;

        $am = new action_menu();
        $am->set_menu_trigger(
            $returnstr
        );
        $am->set_alignment(action_menu::TR, action_menu::BR);
        $am->set_nowrap_on_items();
        if ($withlinks) {
            $navitemcount = count($opts->navitems);
            $idx = 0;
            foreach ($opts->navitems as $key => $value) {

                switch ($value->itemtype) {
                    case 'divider':
                        // If the nav item is a divider, add one and skip link processing.
                        $am->add($divider);
                        break;

                    case 'invalid':
                        // Silently skip invalid entries (should we post a notification?).
                        break;

                    case 'link':
                        // Process this as a link item.
                        $pix = null;
                        if (isset($value->pix) && !empty($value->pix)) {
                            $pix = new pix_icon($value->pix, $value->title, null, array('class' => 'iconsmall'));
                        } else if (isset($value->imgsrc) && !empty($value->imgsrc)) {
                            $value->title = html_writer::img(
                                $value->imgsrc,
                                $value->title,
                                array('class' => 'iconsmall')
                            ) . $value->title;
                        }

                        $al = new action_menu_link_secondary(
                            $value->url,
                            $pix,
                            $value->title,
                            array('class' => 'icon')
                        );
                        if (!empty($value->titleidentifier)) {
                            $al->attributes['data-title'] = $value->titleidentifier;
                        }
                        $am->add($al);
                        break;
                }

                $idx++;

                // Add dividers after the first item and before the last item.
                if ($idx == 1 || $idx == $navitemcount - 1) {
                    $am->add($divider);
                }
            }
        }

        return html_writer::div(
            $this->render($am),
            $usermenuclasses
        );
    }

    /*
    * This controls open graph meta property logic.
    */
    public function get_open_graph_properties() {
        global $COURSE, $PAGE, $SITE;

        $imagedomain = "https://resources.saylor.org/og/";

        // Set default properties.
        $title = str_replace($SITE->shortname.": ", "", $PAGE->title)." | ".$SITE->shortname;
        $type = "website";
        $url = $PAGE->url;
        $image = "default-1200x1200.png";
        $description = preg_replace('~((\{.*\})|(<.+>.*</.+>))~s', '', $SITE->summary);

        // Show different info in courses, such as the title and images.
        if ($PAGE->pagelayout == 'course' || $PAGE->pagelayout == 'incourse') {
            $title = str_replace("Course: ", "", $PAGE->title)." | ".$SITE->shortname;
            // Filter out instances where the course name is listed twice
            // where a resource has the course name in it (like the final exams).
            $title = str_replace($COURSE->shortname.": ".$COURSE->shortname.": ", $COURSE->shortname.": ", $title);
            $image = $COURSE->shortname."-1200x1200.png";
            $description = preg_replace('~((\{.*\})|(<.+>.*</.+>))~s', '', $COURSE->summary);
            $canonical = "/course/".$COURSE->shortname;
        }
        elseif ($PAGE->pagetype == 'site-index') {
            // Set canonical url for the frontpage, whether there is ?redirect=0 or not.
            $canonical = "/";
        }

        $properties = new stdClass();
        $properties->title = html_entity_decode($title);
        $properties->type = $type;
        $properties->url = $url;
        $properties->image = $imagedomain.$image;
        $properties->description = html_entity_decode($description);
        if (isset($canonical)) {
            $properties->canonical = $canonical;
        }

        return $properties;
    }

    /*
    * This controls course property logic.
    */
    public function get_course_properties() {
        global $CFG, $COURSE, $DB, $PAGE, $SITE;

        $imagedomain = "https://resources.saylor.org/og/";

        $courseproperties = new stdClass();
        // Show different info in courses, such as the title and images.
        if ($PAGE->pagelayout == 'course' || $PAGE->pagelayout == 'incourse') {
            $title = str_replace("Course: ", "", $PAGE->title)." | ".$SITE->shortname;
            // Filter out instances where the course name is listed twice
            // where a resource has the course name in it (like the final exams).
            $title = str_replace($COURSE->shortname.": ".$COURSE->shortname.": ", $COURSE->shortname.": ", $title);
            $image = $COURSE->shortname."-1200x1200.png";
            $description = preg_replace('~((\{.*\})|(<.+>.*</.+>))~s', '', $COURSE->summary);
            $canonical = "/course/".$COURSE->shortname;

            $courseproperties = new stdClass();
            $courseproperties->siteurl = str_replace("/", "\/", $CFG->wwwroot);
            $courseproperties->url = str_replace("/", "\/", $CFG->wwwroot.$canonical);
            $courseproperties->name = str_replace("Course: ", "", $PAGE->title);
            $courseproperties->description = $description;
            $courseproperties->coursecode = $COURSE->shortname;
            $courseproperties->subject = $DB->get_record('course_categories',array('id'=>$COURSE->category))->name;
            $courseproperties->imagename = $image;
            $courseproperties->imageurl = $imagedomain.$image;
            // Get the time advisory
            if (preg_match('~.*?Time: ([0-9]{1,3}) hours.*?~', $COURSE->summary, $time)) {
                $courseproperties->time = $time[1];
            } else {
                $courseproperties->time = "0";
            }

            // Check if the course has a credit option.
            // This is done by checking for the fa-graduation-cap
            // fontawesome icon in the course description.
            if (strpos($COURSE->summary, 'fa-graduation-cap') !== false) {
                $courseproperties->credit = true;
            }
        }

        return $courseproperties;
    }

    /*
    * This code shows an enroll button in main course view to logged in user or Login/sign up link when user is not logged in.
    */
    public function saylor_custom_enroll_button() {
        global $COURSE, $PAGE;
        // Show nothing if the page layout is not course or incourse.
        if (!($PAGE->pagelayout == 'course' || $PAGE->pagelayout == 'incourse')) {
            return "";
        }
        // Show nothing if user is already on enroll page.
        if ($PAGE->pagetype == 'enrol-index') {
                return "";
        }
        $output = html_writer::start_tag('div', array('id' => 'enroll-button-container', 'class' => 'enroll-container'));
        $output .= html_writer::start_tag('div', array('id' => 'main-enroll-button', 'class' => 'center-block'));
        $coursecontext = context_course::instance($COURSE->id);
        if (isguestuser() || !isloggedin()) {
            $link = new moodle_url('/login/index.php');
            $output .= get_string('loginorsignupmessage', 'theme_saylor', $link->out());
        } elseif (isloggedin($coursecontext) && !is_enrolled($coursecontext)) {
            $link = new moodle_url('/enrol/index.php', array('id' => $COURSE->id));
            $output .= $this->single_button($link->out(), get_string('enrolme', 'core_enrol'));
        };
        // Adding div that closes the main-enroll-button or the login/signup message.
        $output .= html_writer::end_tag('div');
        // Adding div that closes the enroll-button-container.
        $output .= html_writer::end_tag('div');
        return $output;
    }

}
