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
 * Display debug interface for REIN filter.
 *
 * Prints markup which triggers loading of REIN Library JavaScript and CSS.
 * Presents skinned widgets for theming, testing, and copying markup for
 * paste into Moodle textarea fields.
 *
 * @package   filter_rein
 * @copyright 2013 onwards Remote-Learner {@link http://www.remote-learner.net/}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__).'/../../config.php');

global $OUTPUT, $CFG, $PAGE;
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');

require_login();

// Start setting up the page.
$params = array();

// Set up page context.
$context = context_system::instance();
$PAGE->set_context($context);
require_capability('moodle/site:config', $context);

// Verify that the filter is enabled.
$filters = filter_get_active_in_context($context);
$exists = array_key_exists('rein', $filters);
if (!$exists) {
    print_error('filterdisabled', 'filter_rein', new moodle_url('/admin/filters.php'));
}

// Set up URL, title, heading.
$url = new moodle_url('/filter/rein/debug.php');
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard'); // HOSSUP-6713: cannot use other pagelayouts as $PAGE->requires->jquery() will give errors!
$PAGE->set_title(get_string('debugtitle', 'filter_rein'));
$PAGE->set_heading(get_string('debugtitle', 'filter_rein'));

// Determine whether debug is enabled.
$debugmode = (bool)get_config('filter_rein', 'testmode');

// Add extra box class if debug is enabled.
$boxclasses = 'generalbox debug-interface';
if ($debugmode) {
    $boxclasses .= ' debug-enabled';
} else {
    $boxclasses .= ' debug-disabled';
}

// Define rein markup output options.
$reinoptions = array(
    'noclean' => true,
    'trusted' => true,
    'nocache' => true,
    'allowid' => true
);

$urlparams = array(
    'class' => 'btn',
    'target' => '_blank'
);

// Define image src.
$imgpath = $CFG->wwwroot.'/filter/rein/pix/demo/';

// Begin output.
echo $OUTPUT->header();
echo $OUTPUT->box_start($boxclasses);

// Get filter renderers.
$output = $PAGE->get_renderer('filter_rein');

// Print debug introduction.
echo $output->print_debug_intro();

// Print accordion content.
echo $output->print_debug_accordion($reinoptions, $urlparams);

// Print tabs content.
echo $output->print_debug_tabs($reinoptions, $urlparams);

// Print equal columns content.
echo $output->print_debug_equal_columns($reinoptions, $urlparams);

// Print modal content.
echo $output->print_debug_modal($reinoptions, $urlparams);

// Print toggle content.
echo $output->print_debug_toggle($reinoptions, $urlparams);

// Print flip book content.
echo $output->print_debug_flipbook($reinoptions, $urlparams);

// Print flipcard content.
echo $output->print_debug_flipcard($reinoptions, $urlparams);

// Print click hotspot content.
echo $output->print_debug_clickhotspot($reinoptions, $imgpath, $urlparams);

// Print sort multiple lists content.
echo $output->print_debug_sortmultiple($reinoptions, $urlparams);

// Print drop bubble content.
echo $output->print_debug_dropbubble($reinoptions, $urlparams);

// Print stepwise process with pop-ups content.
echo $output->print_debug_stepwise($reinoptions, $urlparams);

// Print sequential appearance content.
echo $output->print_debug_sequential($reinoptions, $imgpath, $urlparams);

// Print image rotator content.
echo $output->print_debug_rotator($reinoptions, $imgpath, $urlparams);

// Print MarkIt content.
echo $output->print_debug_markit($reinoptions, $imgpath, $urlparams);

// Print Tooltip content.
echo $output->print_debug_tooltip($reinoptions, $imgpath, $urlparams);

// Print overlay content.
echo $output->print_debug_overlay($reinoptions, $imgpath, $urlparams);

// Print swiper content.
echo $output->print_debug_swiper($reinoptions, $imgpath, $urlparams);

echo $OUTPUT->box_end();
echo $OUTPUT->footer();
