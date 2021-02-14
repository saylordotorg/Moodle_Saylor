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
 * Theme functions.
 *
 * @package    theme_saylor
 * @copyright  2021 Saylor Academy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Check the file is being called internally from within Moodle.
defined('MOODLE_INTERNAL') || die();

/**
 * Fumble with Moodle's global navigation by leveraging Moodle's *_extend_navigation() hook.
 *
 * @param global_navigation $navigation
 */
//function theme_saylor_extend_navigation(global_navigation $navigation) {
function theme_saylor_extend_navigation_course(navigation_node $parentnode, stdClass $course, context_course $context) {
    global $CFG, $PAGE, $COURSE;

    $context = $PAGE->context;

    $showcoursemenu = false;
    $showquizmenu = false;

    // We are on the course home page.
    if (($context->contextlevel == CONTEXT_COURSE) &&
            !empty($parentnode) &&
            ($parentnode->type == navigation_node::TYPE_COURSE || $parentnode->type == navigation_node::TYPE_SECTION)) {
        $showcoursemenu = true;
    }

    $courseformat = course_get_format($PAGE->course);
    // This is a single activity course format, always show the course menu on the activity main page.
    if ($context->contextlevel == CONTEXT_MODULE &&
            !$courseformat->has_view_page()) {

        $PAGE->navigation->initialise();
        $activenode = $PAGE->navigation->find_active_node();
        // If the settings menu has been forced then show the menu.
        if ($PAGE->is_settings_menu_forced()) {
            $showcoursemenu = true;
        } else if (!empty($activenode) && ($activenode->type == navigation_node::TYPE_ACTIVITY ||
                $activenode->type == navigation_node::TYPE_RESOURCE)) {

            // We only want to show the menu on the first page of the activity. This means
            // the breadcrumb has no additional nodes.
            if ($parentnode && ($parentnode->key == $activenode->key && $parentnode->type == $activenode->type)) {
                $showcoursemenu = true;
            }
        }
    }

    // Check if we're in an activity.
    if ($context->contextlevel == CONTEXT_MODULE) {
        $showquizmenu = true;
    }

    // If the user can edit the course, add the edit settings node.
    if (has_capability('moodle/course:update', $context)) {
        $coursehomenode = $PAGE->navigation->find($COURSE->id, navigation_node::TYPE_COURSE);

        $settingsnode = navigation_node::create('Edit settings',
        new moodle_url('/course/edit.php', array('id' => $COURSE->id)),
        global_navigation::TYPE_SECTION,
        null,
        'themesaylorcoursesettings');
        // Add the edit settings node to the coursehome node.
        $coursehomenode->add_node($settingsnode);

        // If the user can edit the course and we're in a scenario where we should show the menu items, show them.
        if ($showcoursemenu) {
            // Add our question nodes.
            $questionnode = navigation_node::create('Questions',
                new moodle_url('/question/edit.php', array('id' => $COURSE->id)),
                global_navigation::TYPE_SECTION,
                null,
                'themesaylorquestion');
            $questionnode->showinflatnavigation = true;
            $PAGE->navigation->add_node($questionnode);

            $questioncategoriesnode = navigation_node::create(' Question Categories',
                new moodle_url('/question/category.php', array('id' => $COURSE->id)),
                global_navigation::TYPE_SECTION,
                null,
                'themesaylorquestioncategory');
            $questioncategoriesnode->showinflatnavigation = true;
            $questionnode->add_node($questioncategoriesnode);

            $questionimportnode = navigation_node::create(' Question Import',
                new moodle_url('/question/import.php', array('id' => $COURSE->id)),
                global_navigation::TYPE_SECTION,
                null,
                'themesaylorquestionimport');
            $questionimportnode->showinflatnavigation = true;
            $questionnode->add_node($questionimportnode);

            $questionexportnode = navigation_node::create(' Question Export',
                new moodle_url('/question/export.php', array('id' => $COURSE->id)),
                global_navigation::TYPE_SECTION,
                null,
                'themesaylorquestionexport');
            $questionexportnode->showinflatnavigation = true;
            $questionnode->add_node($questionexportnode);
        }
    }

    // If the user has the ability to edit the quiz or questions, add the relevant nodes.
    if ($showquizmenu && has_capability('mod/quiz:manage', $context)) {
        // Add our edit setting nodes.
        $activitysettingsnode = navigation_node::create('Edit activity settings',
            new moodle_url('/course/modedit.php', array('update' => $PAGE->cm->id, 'return' => 1)),
            global_navigation::TYPE_SECTION,
            null,
            'themesayloractivitysettings');
        $activitysettingsnode->showinflatnavigation = true;
        $PAGE->navigation->add_node($activitysettingsnode);
        // Add the edit quiz node.
        $quizeditnode = navigation_node::create('Edit quiz',
            new moodle_url('/mod/quiz/edit.php', array('cmid' => $PAGE->cm->id)),
            global_navigation::TYPE_SECTION,
            null,
            'themesayloreditquiz');
        $quizeditnode->showinflatnavigation = true;
        $activitysettingsnode->add_node($quizeditnode);

        if (has_capability('mod/quiz:preview', $context)) {
            $quizpreviewnode = navigation_node::create('Preview',
            new moodle_url('/mod/quiz/startattempt.php', array('cmid' => $PAGE->cm->id, 'sesskey' => sesskey())),
            global_navigation::TYPE_RESOURCE,
            null,
            'themesaylorquizpreview');
            $quizpreviewnode->showinflatnavigation = true;
            $activitysettingsnode->add_node($quizpreviewnode);
        }

        if (has_capability('moodle/question:editall', $context)) {
            $questionbanknode = navigation_node::create('Question bank',
            new moodle_url('/question/edit.php', array('cmid' => $PAGE->cm->id)),
            global_navigation::TYPE_RESOURCE,
            null,
            'themesaylorquestionbank');
            $questionbanknode->showinflatnavigation = true;
            $activitysettingsnode->add_node($questionbanknode);

            $questionbankcategoriesnode = navigation_node::create('Question bank - Categories',
            new moodle_url('/question/category.php', array('cmid' => $PAGE->cm->id)),
            global_navigation::TYPE_RESOURCE,
            null,
            'themesaylorquestionbankcategories');
            $questionbankcategoriesnode->showinflatnavigation = true;
            $activitysettingsnode->add_node($questionbankcategoriesnode);

            $questionbankimportnode = navigation_node::create('Question bank - Import',
            new moodle_url('/question/import.php', array('cmid' => $PAGE->cm->id)),
            global_navigation::TYPE_RESOURCE,
            null,
            'themesaylorquestionbankimport');
            $questionbankimportnode->showinflatnavigation = true;
            $activitysettingsnode->add_node($questionbankimportnode);

            $questionbankexportnode = navigation_node::create('Question bank - Export',
            new moodle_url('/question/export.php', array('cmid' => $PAGE->cm->id)),
            global_navigation::TYPE_RESOURCE,
            null,
            'themesaylorquestionbankexport');
            $questionbankexportnode->showinflatnavigation = true;
            $activitysettingsnode->add_node($questionbankexportnode);
        }

    }


}

/**
 * Post process the CSS tree.
 *
 * @param string $tree The CSS tree.
 * @param theme_config $theme The theme config object.
 */
function theme_saylor_css_tree_post_processor($tree, $theme) {
    error_log('theme_saylor_css_tree_post_processor() is deprecated. Required' .
        'prefixes for Bootstrap are now in theme/saylor/scss/moodle/prefixes.scss');
    $prefixer = new theme_saylor\autoprefixer($tree);
    $prefixer->prefix();
}

/**
 * Inject additional SCSS.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_saylor_get_extra_scss($theme) {
    $content = '';
    $imageurl = $theme->setting_file_url('backgroundimage', 'backgroundimage');

    // Sets the background image, and its settings.
    if (!empty($imageurl)) {
        $content .= 'body { ';
        $content .= "background-image: url('$imageurl'); background-size: cover;";
        $content .= ' }';
    }

    // Always return the background image with the scss when we have it.
    return !empty($theme->settings->scss) ? $theme->settings->scss . ' ' . $content : $content;
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_saylor_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM && ($filearea === 'logo' || $filearea === 'backgroundimage')) {
        $theme = theme_config::load('saylor');
        // By default, theme files must be cache-able by both browsers and proxies.
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}

/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_saylor_get_main_scss_content($theme) {
    global $CFG;

    $scss = '';
    $filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
    $fs = get_file_storage();

    $context = context_system::instance();
    if ($filename == 'default.scss') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    } else if ($filename == 'plain.scss') {
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/plain.scss');
    } else if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_saylor', 'preset', 0, '/', $filename))) {
        $scss .= $presetfile->get_content();
    } else {
        // Safety fallback - maybe new installs etc.
        $scss .= file_get_contents($CFG->dirroot . '/theme/boost/scss/preset/default.scss');
    }

    // Saylor scss.
    $saylorpre = file_get_contents($CFG->dirroot . '/theme/saylor/scss/saylor/_pre.scss');
    $saylorvariables = file_get_contents($CFG->dirroot . '/theme/saylor/scss/saylor/_variables.scss');
    $saylor = file_get_contents($CFG->dirroot . '/theme/saylor/scss/saylor.scss');

    // Combine files together.
    $allscss = $saylorvariables . "\n" . $saylorpre . "\n" . $scss . "\n" . $saylor;

    return $allscss;
}

/**
 * Get compiled css.
 *
 * @return string compiled css
 */
function theme_saylor_get_precompiled_css() {
    global $CFG;
    return file_get_contents($CFG->dirroot . '/theme/saylor/style/moodle.css');
}

/**
 * Get SCSS to prepend.
 *
 * @param theme_config $theme The theme config object.
 * @return array
 */
function theme_saylor_get_pre_scss($theme) {
    global $CFG;

    $scss = '';
    $configurable = [
        // Config key => [variableName, ...].
        'brandcolor' => ['primary'],
    ];

    // Prepend variables first.
    foreach ($configurable as $configkey => $targets) {
        $value = isset($theme->settings->{$configkey}) ? $theme->settings->{$configkey} : null;
        if (empty($value)) {
            continue;
        }
        array_map(function($target) use (&$scss, $value) {
            $scss .= '$' . $target . ': ' . $value . ";\n";
        }, (array) $targets);
    }

    // Prepend pre-scss.
    if (!empty($theme->settings->scsspre)) {
        $scss .= $theme->settings->scsspre;
    }

    return $scss;
}
