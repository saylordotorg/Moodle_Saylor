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
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package   theme_bootstrapbase
 * @copyright 2012
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Check the file is being called internally from within Moodle.
defined('MOODLE_INTERNAL') || die();

// course renderer
require_once($CFG->dirroot . "/course/renderer.php");
require_once($CFG->dirroot . "/completion/completion_completion.php");
require_once($CFG->dirroot . "/blocks/course_overview/renderer.php");

class theme_saylor_core_renderer extends core_renderer
{


    public function user_menu($user = null, $withlinks = null) {
        global $CFG, $USER;

        if (is_null($user)) {
            $user = $USER;
        }

        $usermenu = new custom_menu('', current_language());
        return $this->render_user_menu($usermenu, $user);
    }

    protected function render_user_menu(custom_menu $menu, $user) {
        global $CFG, $USER, $DB, $PAGE;

        $addusermenu = true;
        $addlangmenu = true;
        $addmessagemenu = true;

        if (!isloggedin() || isguestuser()) {
            $addmessagemenu = false;
        }

        /*
        $messagecount = $DB->count_records('message', array('useridto' => $USER->id));
        if ($messagecount<1) {
        $addmessagemenu = false;
        }
        */

        if (!$CFG->messaging) {
            $addmessagemenu = false;
        } else {
            // Check whether or not the "popup" message output is enabled
            // This is after we check if messaging is enabled to possibly save a DB query
            $popup = $DB->get_record('message_processors', array('name' => 'popup'));
            if (!$popup) {
                $addmessagemenu = false;
            }
        }

        if ($addmessagemenu) {
            $messages = $this->get_user_messages();
            $messagecount = count($messages);

            if ($messagecount == 0) {
                $messagemenu = $menu->add('<i class="fa fa-comments"> </i>' . get_string('messages', 'message') . '',
                    new moodle_url('/message/'),
                    get_string('messages', 'message'), 9999);
            } else {
                $messagemenu = $menu->add('<i class="fa fa-comments"> </i>' .
                 get_string('messages', 'message') . '<span id="messagebubble">' . $messagecount .
                 '</span>',
                    new moodle_url('#'),
                    get_string('messages', 'message'),
                    9999);
            }
            foreach ($messages as $message) {
                $senderpicture = new user_picture($message->from);
                $senderpicture->link = false;
                $senderpicture = $this->render($senderpicture);

                $messagecontent = $senderpicture;
                $messagecontent .= html_writer::start_tag('span', array('class' => 'msg-body'));
                $messagecontent .= html_writer::start_tag('span', array('class' => 'msg-title'));
                $messagecontent .= html_writer::tag('span', $message->from->firstname . ': ', array('class' => 'msg-sender'));
                $messagecontent .= $message->text;
                $messagecontent .= html_writer::end_tag('span');
                $messagecontent .= html_writer::start_tag('span', array('class' => 'msg-time'));
                $messagecontent .= html_writer::tag('i', '', array('class' => 'icon-time'));
                $messagecontent .= html_writer::tag('span', $message->date);
                $messagecontent .= html_writer::end_tag('span');

                $messagemenu->add($messagecontent, new moodle_url('/message/index.php', array('user1' => $user->id, 'user2' => $message->from->id)));
            }
        }

        $langs = get_string_manager()->get_list_of_translations();
        if (count($langs) < 2
            or empty($CFG->langmenu)
            or ($this->page->course != SITEID and !empty($this->page->course->lang))
        ) {
            $addlangmenu = false;
        }

        $content = '<ul class="usermenu2 nav navbar-nav navbar-right">';
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item, 1);
        }

        return $content.'</ul>';
    }


    protected function process_user_messages() {

        $messagelist = array();

        foreach ($usermessages as $message) {
            $cleanmsg = new stdClass();
            $cleanmsg->from = fullname($message);
            $cleanmsg->msguserid = $message->id;

            $userpicture = new user_picture($message);
            $userpicture->link = false;
            $picture = $this->render($userpicture);

            $cleanmsg->text = $picture . ' ' . $cleanmsg->text;

            $messagelist[] = $cleanmsg;
        }

        return $messagelist;
    }

    protected function get_user_messages() {
        global $USER, $DB;
        $messagelist = array();

        $newmessagesql = "SELECT id, smallmessage, useridfrom, useridto, timecreated, fullmessageformat, notification
                            FROM {message}
                           WHERE useridto = :userid";

        $newmessages = $DB->get_records_sql($newmessagesql, array('userid' => $USER->id));

        foreach ($newmessages as $message) {
            $messagelist[] = $this->bootstrap_process_message($message);
        }

        $showoldmessages = (empty($this->page->theme->settings->showoldmessages)) ? 0 : $this->page->theme->settings->showoldmessages;
        if ($showoldmessages) {
            $maxmessages = 5;
            $readmessagesql = "SELECT id, smallmessage, useridfrom, useridto, timecreated, fullmessageformat, notification
                                 FROM {message_read}
                                WHERE useridto = :userid
                             ORDER BY timecreated DESC
                                LIMIT $maxmessages";

            $readmessages = $DB->get_records_sql($readmessagesql, array('userid' => $USER->id));

            foreach ($readmessages as $message) {
                $messagelist[] = $this->bootstrap_process_message($message);
            }
        }

        return $messagelist;
    }

    protected function saylor_process_message($message, $state) {
        global $DB;
        $messagecontent = new stdClass();

        if ($message->notification) {
            $messagecontent->text = get_string('unreadnewnotification', 'message');
        } else {
            if ($message->fullmessageformat == FORMAT_HTML) {
                $message->smallmessage = html_to_text($message->smallmessage);
            }
            $messagecontent->text = $message->smallmessage;
        }

        if ((time() - $message->timecreated ) <= (3600 * 3)) {
            $messagecontent->date = format_time(time() - $message->timecreated);
        } else {
            $messagecontent->date = userdate($message->timecreated, get_string('strftimetime', 'langconfig'));
        }

        $messagecontent->from = $DB->get_record('user', array('id' => $message->useridfrom));
        $messagecontent->state = $state;
        return $messagecontent;
    }

    protected function bootstrap_process_message($message) {
        global $DB;
        $messagecontent = new stdClass();

        if ($message->notification) {
            $messagecontent->text = get_string('unreadnewnotification', 'message');
        } else {
            if ($message->fullmessageformat == FORMAT_HTML) {
                $message->smallmessage = html_to_text($message->smallmessage);
            }
            if (core_text::strlen($message->smallmessage) > 15) {
                $messagecontent->text = core_text::substr($message->smallmessage, 0, 15).'...';
            } else {
                $messagecontent->text = $message->smallmessage;
            }
        }

        if ((time() - $message->timecreated ) <= (3600 * 3)) {
            $messagecontent->date = format_time(time() - $message->timecreated);
        } else {
            $messagecontent->date = userdate($message->timecreated, get_string('strftimetime', 'langconfig'));
        }

        $messagecontent->from = $DB->get_record('user', array('id' => $message->useridfrom));
        return $messagecontent;
    }
    // end usermenu

    /**
     * @var custom_menu_item language The language menu if created
     */
    protected $language = null;

    /*
     * This renders a notification message.
     * Uses bootstrap compatible html.
     */
    public function notification($message, $classes = 'notifyproblem') {
        $message = clean_text($message);
        $type = '';

        if ($classes == 'notifyproblem') {
            $type = 'alert alert-error';
        }
        if ($classes == 'notifysuccess') {
            $type = 'alert alert-success';
        }
        if ($classes == 'notifymessage') {
            $type = 'alert alert-info';
        }
        if ($classes == 'redirectmessage') {
            $type = 'alert alert-block alert-info';
        }
        return "<div class=\"$type\">$message</div>";
    }

    /*
     * This renders the navbar.
     * Uses bootstrap compatible html.
     */
    public function navbar() {
        $items = $this->page->navbar->get_items();
        $breadcrumbs = array();
        foreach ($items as $item) {
            $item->hideicon = true;
            $breadcrumbs[] = $this->render($item);
        }
        $divider = '<span class="divider">/</span>';
        $listitems = '<li>'.join(" $divider</li><li>", $breadcrumbs).'</li>';
        $title = '<span class="accesshide">'.get_string('pagepath').'</span>';
        return $title . "<ul class=\"breadcrumb\">$listitems</ul>";
    }

    /*
     * Overriding the custom_menu function ensures the custom menu is
     * always shown, even if no menu items are configured in the global
     * theme settings page.
     */
    public function custom_menu($custommenuitems = '') {
        global $CFG;

        if (!empty($CFG->custommenuitems)) {
            $custommenuitems .= $CFG->custommenuitems;
        }
        $custommenu = new custom_menu($custommenuitems, current_language());
        return $this->render_custom_menu($custommenu);
    }

    /*
     * This renders the bootstrap top menu.
     *
     * This renderer is needed to enable the Bootstrap style navigation.
     */
    protected function render_custom_menu(custom_menu $menu) {
        global $CFG, $USER;

        // $branchurlb   = new moodle_url('/');
        // $branch = $menu->add("<i class='fa fa-home'></i>", $branchurlb, "title", -10000);

        if (isloggedin() && !isguestuser()) {
               $mycoursetitle = "My Courses";
                 $branchtitle = "My Courses";
               $branchlabel = ''.$branchtitle;
            $branchurl   = new moodle_url('/my/index.php');
            $branchsort  = 10000;

            $branch = $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);
            if ($courses = enrol_get_my_courses(null, 'fullname ASC')) {
                foreach ($courses as $course) {
                    // Setting up params array for completion_completion object
                    $params = array (
                    'userid' => $USER->id,
                    'course' => $course->id
                               );

                           // Create completion_completion object; will use to check whether the course is completed before adding to the menu.
                           $ccompletion = new completion_completion($params);

                           // Only add course to menu if course is not completed and is visible.
                    if ($course->visible && !$ccompletion->is_complete()) {
                            $branch->add(format_string($course->fullname), new moodle_url('/course/view.php?id='.$course->id), format_string($course->shortname));
                    }
                }
            } else {
                  $noenrolments = get_string('noenrolments', 'theme_saylor');
                $branch->add('<em>'.$noenrolments.'</em>', new moodle_url('/'), $noenrolments);
            }
        }

        // TODO: eliminate this duplicated logic, it belongs in core, not
        // here. See MDL-39565.
        $addlangmenu = true;
        $langs = get_string_manager()->get_list_of_translations();
        if (count($langs) < 2
            or empty($CFG->langmenu)
            or ($this->page->course != SITEID and !empty($this->page->course->lang))
        ) {
            $addlangmenu = false;
        }

        if (!$menu->has_children() && $addlangmenu === false) {
            return '';
        }

        $content = '<ul class="nav">';
        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item, 1);
        }

        return $content.'</ul>';
    }

    /*
     * This code renders the custom menu items for the
     * bootstrap dropdown menu.
     */
    protected function render_custom_menu_item(custom_menu_item $menunode, $level = 0) {
        static $submenucount = 0;

        if ($menunode->has_children()) {
            if ($level == 1) {
                $class = 'dropdown';
            } else {
                $class = 'dropdown-submenu';
            }

            if ($menunode === $this->language) {
                $class .= ' langmenu';
            }
            $content = html_writer::start_tag('li', array('class' => $class));
            // If the child has menus render it as a sub menu.
            $submenucount++;
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#cm_submenu_'.$submenucount;
            }
            $content .= html_writer::start_tag('a', array('href' => $url, 'class' => 'dropdown-toggle', 'data-toggle' => 'dropdown', 'title' => $menunode->get_title()));
            $content .= $menunode->get_text();
            if ($level == 1) {
                $content .= '<i class="fa fa-caret-down"></i>';
            }
            $content .= '</a>';
            $content .= '<ul class="dropdown-menu">';
            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode, 0);
            }
            $content .= '</ul>';
        } else {
            $content = '<li>';
            // The node doesn't have children so produce a final menuitem.
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#';
            }
            $content .= html_writer::link($url, $menunode->get_text(), array('title' => $menunode->get_title()));
        }
        return $content;
    }

    /**
     * Renders tabtree
     *
     * @param  tabtree $tabtree
     * @return string
     */
    protected function render_tabtree(tabtree $tabtree) {
        if (empty($tabtree->subtree)) {
            return '';
        }
        $firstrow = $secondrow = '';
        foreach ($tabtree->subtree as $tab) {
            $firstrow .= $this->render($tab);
            if (($tab->selected || $tab->activated) && !empty($tab->subtree) && $tab->subtree !== array()) {
                $secondrow = $this->tabtree($tab->subtree);
            }
        }
        return html_writer::tag('ul', $firstrow, array('class' => 'nav nav-tabs')) . $secondrow;
    }

    /**
     * Renders tabobject (part of tabtree)
     *
     * This function is called from {@link core_renderer::render_tabtree()}
     * and also it calls itself when printing the $tabobject subtree recursively.
     *
     * @param  tabobject $tabobject
     * @return string HTML fragment
     */
    protected function render_tabobject(tabobject $tab) {
        if ($tab->selected or $tab->activated) {
            return html_writer::tag('li', html_writer::tag('a', $tab->text), array('class' => 'active'));
        } else if ($tab->inactive) {
            return html_writer::tag('li', html_writer::tag('a', $tab->text), array('class' => 'disabled'));
        } else {
            if (!($tab->link instanceof moodle_url)) {
                // backward compartibility when link was passed as quoted string
                $link = "<a href=\"$tab->link\" title=\"$tab->title\">$tab->text</a>";
            } else {
                $link = html_writer::link($tab->link, $tab->text, array('title' => $tab->title));
            }
            return html_writer::tag('li', $link);
        }
    }


    public function saylorblocks($region, $classes = array(), $tag = 'aside') {
        $classes = (array)$classes;
        $classes[] = 'block-region';
        $attributes = array(
            'id' => 'block-region-'.preg_replace('#[^a-zA-Z0-9_\-]+#', '-', $region),
            'class' => join(' ', $classes),
            'data-blockregion' => $region,
            'data-droptarget' => '1'
        );
        return html_writer::tag($tag, $this->blocks_for_region($region), $attributes);
    }
}

class theme_saylor_block_course_overview_renderer extends block_course_overview_renderer
{

    /**
     * Construct contents of course_overview block
     *
     * Override
     *
     * @param  array $courses   list of courses in sorted order
     * @param  array $overviews list of course overviews
     * @return string html to be displayed in course_overview block
     */
    public function course_overview($courses, $overviews) {
        global $USER;
        $html = '';
        $config = get_config('block_course_overview');
        if ($config->showcategories != BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_NONE) {
            global $CFG;
            include_once($CFG->libdir.'/coursecatlib.php');
        }
        $ismovingcourse = false;
        $courseordernumber = 0;
        $maxcourses = count($courses);
        $userediting = false;
        // Intialise string/icon etc if user is editing and courses > 1
        if ($this->page->user_is_editing() && (count($courses) > 1)) {
            $userediting = true;
            $this->page->requires->js_init_call('M.block_course_overview.add_handles');

            // Check if course is moving
            $ismovingcourse = optional_param('movecourse', false, PARAM_BOOL);
            $movingcourseid = optional_param('courseid', 0, PARAM_INT);
        }

        // Render first movehere icon.
        if ($ismovingcourse) {
            // Remove movecourse param from url.
            $this->page->ensure_param_not_in_url('movecourse');

            // Show moving course notice, so user knows what is being moved.
            $html .= $this->output->box_start('notice');
            $a = new stdClass();
            $a->fullname = $courses[$movingcourseid]->fullname;
            $a->cancellink = html_writer::link($this->page->url, get_string('cancel'));
            $html .= get_string('movingcourse', 'block_course_overview', $a);
            $html .= $this->output->box_end();

            $moveurl = new moodle_url(
                '/blocks/course_overview/move.php',
                array('sesskey' => sesskey(), 'moveto' => 0, 'courseid' => $movingcourseid)
            );
            // Create move icon, so it can be used.
            $movetofirsticon = html_writer::empty_tag(
                'img',
                array('src' => $this->output->pix_url('movehere'),
                        'alt' => get_string('movetofirst', 'block_course_overview', $courses[$movingcourseid]->fullname),
                'title' => get_string('movehere'))
            );
            $moveurl = html_writer::link($moveurl, $movetofirsticon);
            $html .= html_writer::tag('div', $moveurl, array('class' => 'movehere'));
        }

        $html .= html_writer::start_span('inprogresscoursebox') . 'In Progress Courses' . html_writer::end_span();

        // Active course box
        foreach ($courses as $key => $course) {
            // If moving course, then don't show course which needs to be moved.
            if ($ismovingcourse && ($course->id == $movingcourseid)) {
                continue;
            }
            // Build params to get course completion info
            $params = array (
                'userid' => $USER->id,
                'course' => $course->id
                );
            // Create completion_completion object
            $ccompletion = new completion_completion($params);

            // If course has already been completed, skip.
            if ($ccompletion->is_complete()) {
                continue;
            }

            $courseordernumber++;

            $html .= self::render_course($course, $config, $userediting, $ismovingcourse, $courseordernumber);
        }

        // Separate active/completed course boxes. Should these be in separate divs?
        $html .= html_writer::empty_tag('br', null);
        $html .= html_writer::empty_tag('hr', null);
        $html .= html_writer::empty_tag('br', null);

        $html .= html_writer::start_span('completedcoursebox') . 'Completed Courses' . html_writer::end_span();

        // Completed course box
        foreach ($courses as $key => $course) {
            // If moving course, then don't show course which needs to be moved.
            if ($ismovingcourse && ($course->id == $movingcourseid)) {
                continue;
            }

            // Build params to get course completion info
            $params = array (
                'userid' => $USER->id,
                'course' => $course->id
                );
            // Create completion_completion object
            $ccompletion = new completion_completion($params);

            // If course has NOT been completed (is active), skip.
            if (!$ccompletion->is_complete()) {
                continue;
            }

            $courseordernumber++;

            $html .= self::render_course($course, $config, $userediting, $ismovingcourse, $courseordernumber);
        }

        // Wrap course list in a div and return.
        return html_writer::tag('div', $html, array('class' => 'course_list'));
    }

    /**
     * Renders a course for the coursebox
     *
     *
     * @param  course object $course
     * @return string HTML fragment
     */
    protected function render_course($course, $config, $userediting, $ismovingcourse, $courseordernumber) {
        $html = $this->output->box_start('coursebox', "course-{$course->id}");
        $html .= html_writer::start_tag('div', array('class' => 'course_title'));
        // If user is editing, then add move icons.
        if ($userediting && !$ismovingcourse) {
            $moveicon = html_writer::empty_tag(
                'img',
                array('src' => $this->pix_url('t/move')->out(false),
                        'alt' => get_string('movecourse', 'block_course_overview', $course->fullname),
                'title' => get_string('move'))
            );
            $moveurl = new moodle_url($this->page->url, array('sesskey' => sesskey(), 'movecourse' => 1, 'courseid' => $course->id));
            $moveurl = html_writer::link($moveurl, $moveicon);
            $html .= html_writer::tag('div', $moveurl, array('class' => 'move'));
        }

        // No need to pass title through s() here as it will be done automatically by html_writer.
        $attributes = array('title' => $course->fullname);
        if ($course->id > 0) {
            if (empty($course->visible)) {
                $attributes['class'] = 'dimmed';
            }
            $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
            $coursefullname = format_string(get_course_display_name_for_list($course), true, $course->id);
            $link = html_writer::link($courseurl, $coursefullname, $attributes);
            $html .= $this->output->heading($link, 2, 'title');
        } else {
            $html .= $this->output->heading(
                html_writer::link(
                    new moodle_url('/auth/mnet/jump.php', array('hostid' => $course->hostid, 'wantsurl' => '/course/view.php?id='.$course->remoteid)),
                    format_string($course->shortname, true),
                    $attributes
                ) . ' (' . format_string($course->hostname) . ')',
                2,
                'title'
            );
        }
        $html .= $this->output->box('', 'flush');
        $html .= html_writer::end_tag('div');

        if (!empty($config->showchildren) && ($course->id > 0)) {
            // List children here.
            if ($children = block_course_overview_get_child_shortnames($course->id)) {
                $html .= html_writer::tag('span', $children, array('class' => 'coursechildren'));
            }
        }

        // If user is moving courses, then down't show overview.
        if (isset($overviews[$course->id]) && !$ismovingcourse) {
            $html .= $this->activity_display($course->id, $overviews[$course->id]);
        }

        if ($config->showcategories != BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_NONE) {
            // List category parent or categories path here.
            $currentcategory = coursecat::get($course->category, IGNORE_MISSING);
            if ($currentcategory !== null) {
                $html .= html_writer::start_tag('div', array('class' => 'categorypath'));
                if ($config->showcategories == BLOCKS_COURSE_OVERVIEW_SHOWCATEGORIES_FULL_PATH) {
                    foreach ($currentcategory->get_parents() as $categoryid) {
                        $category = coursecat::get($categoryid, IGNORE_MISSING);
                        if ($category !== null) {
                            $html .= $category->get_formatted_name().' / ';
                        }
                    }
                }
                $html .= $currentcategory->get_formatted_name();
                $html .= html_writer::end_tag('div');
            }
        }

        $html .= $this->output->box('', 'flush');
        $html .= $this->output->box_end();
        if ($ismovingcourse) {
            $moveurl = new moodle_url(
                '/blocks/course_overview/move.php',
                array('sesskey' => sesskey(), 'moveto' => $courseordernumber, 'courseid' => $movingcourseid)
            );
            $a = new stdClass();
            $a->movingcoursename = $courses[$movingcourseid]->fullname;
            $a->currentcoursename = $course->fullname;
            $movehereicon = html_writer::empty_tag(
                'img',
                array('src' => $this->output->pix_url('movehere'),
                        'alt' => get_string('moveafterhere', 'block_course_overview', $a),
                'title' => get_string('movehere'))
            );
            $moveurl = html_writer::link($moveurl, $movehereicon);
            $html .= html_writer::tag('div', $moveurl, array('class' => 'movehere'));
        }

        return $html;
    }
}

class theme_saylor_core_course_renderer extends core_course_renderer
{
           // Change searchcriteria to only focus on courses from category 2.
    protected function coursecat_courses(coursecat_helper $chelper, $courses, $totalcount = null) {
        global $CFG;

        // New array with filtered courses.
        $coursestorender = array();

        // First, create whitelist of courses in cat 2.
        $options['recursive'] = true;
        $options['coursecontacts'] = false;
        $options['summary'] = false;
        $options['sort']['idnumber'] = 1;

        $cat2courselist = coursecat::get(2)->get_courses($options);
        // Check all courses and put those with id 2 in whitelist.
        foreach ($cat2courselist as $cat2course) {
            $id = $cat2course->__get('id');
            $cat2courses[$id] = $id;
        }

        // Get list of courses and check if each course is in category 2.
        foreach ($courses as $course) {
            $courseisincat2 = false; // False = 0
            // Checking if course is in whitelist.
            foreach ($cat2courses as $cat2course) {
                if ($cat2course == $course->id) {
                    $courseisincat2 = true;
                    break;
                }
            }

            // If you are an admin you can see everything otherwise you see only courses in cat 2.
            if ($courseisincat2 == false && !is_siteadmin()) {
                continue;
            }

            // Add filtered courses from whitelist into a new array.
            $coursestorender[] = $course;
        }

        if ($totalcount === null) {
            $totalcount = count($coursestorender);
        }
        if (!$totalcount) {
            // Courses count is cached during courses retrieval.
            return '';
        }

        if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_AUTO) {
            // In 'auto' course display mode we analyse if number of courses is more or less than $CFG->courseswithsummarieslimit.
            if ($totalcount <= $CFG->courseswithsummarieslimit) {
                $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED);
            } else {
                $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_COLLAPSED);
            }
        }

        // Prepare content of paging bar if it is needed.
        $paginationurl = $chelper->get_courses_display_option('paginationurl');
        $paginationallowall = $chelper->get_courses_display_option('paginationallowall');
        if ($totalcount > count($courses)) {
            // There are more results that can fit on one page.
            if ($paginationurl) {
                // The option paginationurl was specified, display pagingbar.
                $perpage = $chelper->get_courses_display_option('limit', $CFG->coursesperpage);
                $page = $chelper->get_courses_display_option('offset') / $perpage;
                $pagingbar = $this->paging_bar($totalcount, $page, $perpage,
                        $paginationurl->out(false, array('perpage' => $perpage)));
                if ($paginationallowall) {
                    $pagingbar .= html_writer::tag('div', html_writer::link($paginationurl->out(false, array('perpage' => 'all')),
                            get_string('showall', '', $totalcount)), array('class' => 'paging paging-showall'));
                }
            } else if ($viewmoreurl = $chelper->get_courses_display_option('viewmoreurl')) {
                // The option for 'View more' link was specified, display more link.
                $viewmoretext = $chelper->get_courses_display_option('viewmoretext', new lang_string('viewmore'));
                $morelink = html_writer::tag('div', html_writer::link($viewmoreurl, $viewmoretext),
                        array('class' => 'paging paging-morelink'));
            }
        } else if (($totalcount > $CFG->coursesperpage) && $paginationurl && $paginationallowall) {
            // There are more than one page of results and we are in 'view all' mode, suggest to go back to paginated view mode.
            $pagingbar = html_writer::tag('div', html_writer::link($paginationurl->out(false, array('perpage' => $CFG->coursesperpage)),
                get_string('showperpage', '', $CFG->coursesperpage)), array('class' => 'paging paging-showperpage'));
        }

        // Display list of courses.
        $attributes = $chelper->get_and_erase_attributes('courses');
        $content = html_writer::start_tag('div', $attributes);

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }

        $coursecount = 0;

        // Renders each course that we want rendered.
        foreach ($coursestorender as $course) {
            $classes = ($coursecount % 2) ? 'odd' : 'even';
            if ($coursecount == 1) {
                $classes .= ' first';
            }
            if ($coursecount >= count($coursestorender)) {
                $classes .= ' last';
            }
            $content .= $this->coursecat_coursebox($chelper, $course, $classes);
            $coursecount += 1;
        }

        if (!empty($pagingbar)) {
            $content .= $pagingbar;
        }
        if (!empty($morelink)) {
            $content .= $morelink;
        }

        $content .= html_writer::end_tag('div'); // .courses
        return $content;
    }
}
