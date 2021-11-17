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
 *
 * @package   theme_saylor
 * @copyright 2018 Saylor Academy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
require_once($CFG->libdir . '/behat/lib.php');

// Settings for frontpage alerts.
$enable1alert = '';
$alert1type = '';
$alert1title = '';
$alert1text = '';
$enable2alert = '';
$alert2type = '';
$alert2title = '';
$alert2text = '';
$enable3alert = '';
$alert3type = '';
$alert3title = '';
$alert3text = '';

if (isset($PAGE->theme->settings->enablealert)  && $PAGE->theme->settings->enablealert == 1) {
    $enable1alert = [
        'enable1alert' => true
    ];
}
if (isset($PAGE->theme->settings->enable2alert)  && $PAGE->theme->settings->enable2alert == 1) {
    $enable2alert = [
        'enable2alert' => true
    ];
}
if (isset($PAGE->theme->settings->enable3alert)  && $PAGE->theme->settings->enable3alert == 1) {
    $enable3alert = [
        'enable3alert' => true
    ];
}
if ($enable1alert || $enable2alert || $enable3alert) {
    $alertinfo = '<span class="fa-stack alerticon"><span aria-hidden="true" class="fa fa-info fa-stack-1x "></span></span>';
    $alerterror = '<span class="fa-stack alerticon"><span aria-hidden="true" class="fa fa-warning fa-stack-1x "></span></span>';
    $alertsuccess = '<span class="fa-stack alerticon"><span aria-hidden="true" class="fa fa-bullhorn fa-stack-1x "></span></span>';
}
if ($enable1alert) {
    $alert1type = $PAGE->theme->settings->alert1type;
    $alert1title = $PAGE->theme->settings->alert1title;
    $alert1text = $PAGE->theme->settings->alert1text;
}
if ($enable2alert) {
    $alert2type = $PAGE->theme->settings->alert2type;
    $alert2title = $PAGE->theme->settings->alert2title;
    $alert2text = $PAGE->theme->settings->alert2text;
}
if ($enable2alert) {
    $alert3type = $PAGE->theme->settings->alert3type;
    $alert3title = $PAGE->theme->settings->alert3title;
    $alert3text = $PAGE->theme->settings->alert3text;
}

if (isloggedin()) {
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
} else {
    $navdraweropen = false;
}
$extraclasses = [];
if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}
$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$blockshtml = $OUTPUT->blocks('side-pre');
$hassideblocks = strpos($blockshtml, 'data-block=') !== false;
$topblockshtml = $OUTPUT->blocks('top');
$hastopblocks = strpos($topblockshtml, 'data-block=') !== false;
$topintblockshtml = $OUTPUT->blocks('top-interior');
$hastopintblocks = strpos($topintblockshtml, 'data-block=') !== false;
$regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'sitesummary' => get_string('bannerdescription', 'theme_saylor'),
    'bannerimageurl' => $OUTPUT->image_url('logos/frontpage', 'theme_saylor'),
    'output' => $OUTPUT,
    'page' => $PAGE,
    'CFG' => $CFG,
    'sidepreblocks' => $blockshtml,
    'hassideblocks' => $hassideblocks,
    'topblocks' => $topblockshtml,
    'hastopblocks' => $hastopblocks,
    'topintblocks' => $topintblockshtml,
    'hastopintblocks' => $hastopintblocks,
    'bodyattributes' => $bodyattributes,
    'navdraweropen' => $navdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'currentyear' => date('Y'),
    'opengraph' => $OUTPUT->get_open_graph_properties(),
    'enable1alert' => $enable1alert,
    'alert1type' => $alert1type,
    'alert1title' => $alert1title,
    'alert1text' => $alert1text,
    'enable2alert' => $enable2alert,
    'alert2type' => $alert2type,
    'alert2title' => $alert2title,
    'alert2text' => $alert2text,
    'enable3alert' => $enable3alert,
    'alert3type' => $alert3type,
    'alert3title' => $alert3title,
    'alert3text' => $alert3text
];

$nav = $PAGE->flatnav;
$templatecontext['flatnavigation'] = $nav;
$templatecontext['firstcollectionlabel'] = $nav->get_collectionlabel();
echo $OUTPUT->render_from_template('theme_saylor/frontpage', $templatecontext);
