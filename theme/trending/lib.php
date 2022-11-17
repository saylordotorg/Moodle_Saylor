<?php
// This line protects the file from being accessed by a URL directly.
defined('MOODLE_INTERNAL') || die();
function theme_trending_get_main_scss_content($theme) {
global $CFG;
$scss = '';
$filename = !empty($theme->settings->preset) ? $theme->settings->preset : null;
$fs = get_file_storage();
$context = context_system::instance();
//$scss .= file_get_contents($CFG->dirroot . '/theme/trending/scss/trending/pre.scss');
if ($filename && ($presetfile = $fs->get_file($context->id, 'theme_trending', 'preset', 0, '/', $filename))) {
$scss .= $presetfile->get_content();
} else {
// Safety fallback - maybe new installs etc.
/*
====== post.scss for internal pages/sections====
*/
$scss .= file_get_contents($CFG->dirroot . '/theme/trending/scss/preset/default.scss');
}
/*
====== post.scss for internal page such as Dashboard====
*/
$scss .= file_get_contents($CFG->dirroot . '/theme/trending/scss/trending/post.scss');
/*
====== custom.scss for customization pages/sections ====
*/
$scss .= file_get_contents($CFG->dirroot . '/theme/trending/scss/custom.scss');

$scss .= "form#searchbox_demo input[type='submit'], .search-input-wrapper div[role='button'] {background-image: url([[pix:theme|search]]);}";
$scss .= ".camera_target_content .camera_link {background-image: url([[pix:theme|blank]]);}";
$scss .= ".camera_loader {background-image: url([[pix:theme|camera-loader]]);}";
$scss .= ".camera_prevThumbs div,.camera_nextThumbs div,.camera_prev > span,.camera_next > span,.camera_commands > .camera_play,.camera_commands > .camera_stop {background-image: url([[pix:theme|camera_skins]]);}";
$scss .= ".pattern_1 .camera_overlayer {background-image: url([[patterns:theme|overlay1]]);}";

$scss .= ".pattern_1 .camera_overlayer {background-image: url([[patterns:theme|overlay1]]);}";
$scss .= ".pattern_2 .camera_overlayer {background-image: url([[patterns:theme|overlay2]]);}";
$scss .= ".pattern_3 .camera_overlayer {background-image: url([[patterns:theme|overlay3]]);}";
$scss .= ".pattern_4 .camera_overlayer {background-image: url([[patterns:theme|overlay4]]);}";
$scss .= ".pattern_5 .camera_overlayer {background-image: url([[patterns:theme|overlay5]]);}";
$scss .= ".pattern_6 .camera_overlayer {background-image: url([[patterns:theme|overlay6]]);}";
$scss .= ".pattern_7 .camera_overlayer {background-image: url([[patterns:theme|overlay7]]);}";
$scss .= ".pattern_8 .camera_overlayer {background-image: url([[patterns:theme|overlay8]]);}";
$scss .= ".pattern_9 .camera_overlayer {background-image: url([[patterns:theme|overlay9]]);}";
$scss .= ".pattern_10 .camera_overlayer {background-image: url([[patterns:theme|overlay10]]);}";

$scss .= ".owl-carousel .owl-nav .owl-prev span {background-image: url([[pix:theme|prev]]);}";
$scss .= ".owl-carousel .owl-nav .owl-next span {background-image: url([[pix:theme|next]]);}";
$scss .= "caption.calendar-controls .previous {background-image: url([[pix:theme|arrow-left]]);}";
$scss .= "caption.calendar-controls .next {background-image: url([[pix:theme|arrow-right]]);}";

$scss .= ".newsletter .container {background-image: url([[pix:theme|newsletterbackground]]);}";

$scss .= "body#page-login-signup .card-body,body.pagelayout-login #page #page-content #region-main-box .loginform,body.pagelayout-login #page #page-content #region-main-box .btn:hover{background-image: url([[pix:theme|tr]]);}";



if ($allcoursesbgurl = $theme->setting_file_url('allcoursesbg', 'allcoursesbg')) {
$scss .= "#allcourses {background-image: url('$allcoursesbgurl');}";
} else {
$scss .= "#allcourses {background-image: url([[pix:theme|allcoursesbg]]);}";
}
if ($enrolledcoursesbgurl = $theme->setting_file_url('enrolledcoursesbg', 'enrolledcoursesbg')) {
$scss .= "#enrolledcourses {background-image: url('$enrolledcoursesbgurl');}";
} else {
$scss .= "#enrolledcourses {background-image: url([[pix:theme|enrolledcourses-bg]]);}";
}
if ($tutorsbgurl = $theme->setting_file_url('tutorsbg', 'tutorsbg')) {
$scss .= ".tutors {background-image: url('$tutorsbgurl');}";
} else {
$scss .= ".tutors {background-image: url([[pix:theme|tutors-bg]]);}";
}
// Set the student1image.
if ($student1imageurl = $theme->setting_file_url('student1image', 'student1image')) {
$scss .= ".colorful-tab-wrapper.flatline .colorful-tab-menu-item a.student-a {background-image: url('$student1imageurl');}";
} else {
$scss .= ".colorful-tab-wrapper.flatline .colorful-tab-menu-item a.student-a {background-image: url([[pix:theme|student/01]]) !important;}";
}

if ($student2imageurl = $theme->setting_file_url('student2image', 'student2image')) {
$scss .= ".colorful-tab-wrapper.flatline .colorful-tab-menu-item a.student-b {background-image: url('$student2imageurl');}";
} else {
$scss .= ".colorful-tab-wrapper.flatline .colorful-tab-menu-item a.student-b {background-image: url([[pix:theme|student/02]]) !important;}";
}

if ($student3imageurl = $theme->setting_file_url('student3image', 'student3image')) {
$scss .= ".colorful-tab-wrapper.flatline .colorful-tab-menu-item a.student-c {background-image: url('$student3imageurl');}";
} else {
$scss .= ".colorful-tab-wrapper.flatline .colorful-tab-menu-item a.student-c {background-image: url([[pix:theme|student/03]]) !important;}";
}

if ($student4imageurl = $theme->setting_file_url('student4image', 'student4image')) {
$scss .= ".colorful-tab-wrapper.flatline .colorful-tab-menu-item a.student-d {background-image: url('$student4imageurl');}";
} else {
$scss .= ".colorful-tab-wrapper.flatline .colorful-tab-menu-item a.student-d {background-image: url([[pix:theme|student/04]]) !important;}";
}

if ($student5imageurl = $theme->setting_file_url('student5image', 'student5image')) {
$scss .= ".colorful-tab-wrapper.flatline .colorful-tab-menu-item a.student-e {background-image: url('$student5imageurl');}";
} else {
$scss .= ".colorful-tab-wrapper.flatline .colorful-tab-menu-item a.student-e {background-image: url([[pix:theme|student/05]]) !important;}";
}

if ($internalbannerimageurl = $theme->setting_file_url('internalbannerimage', 'internalbannerimage')) {
$scss .= ".internalbanner {background-image: url('$internalbannerimageurl');}";
} else {
$scss .= ".internalbanner {background-image: url([[pix:theme|internalbanner]]);}";
}
if ($loginbackgroundurl = $theme->setting_file_url('loginbackground', 'loginbackground')) {
$scss .= "body.pagelayout-login #page{background-image: url('$loginbackgroundurl');}";
} else {
$scss .= "body.pagelayout-login #page{background-image: url([[pix:theme|loginbackground]]);}";
}
return $scss;
}
/**
* Get SCSS to prepend.
*
* @param theme_config $theme The theme config object.
* @return array
*/
function theme_trending_get_pre_scss($theme) {
$scss = '';
$configurable = [
// Config key => [variableName, ...].

'sitebluecolor' => 'siteblcolor',
'siteyellowcolor' => 'siteyecolor',
'sitevioletcolor' => 'sitevicolor',
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
/**
* Inject additional SCSS.
*
* @param theme_config $theme The theme config object.
* @return string
*/
function theme_trending_get_extra_scss($theme) {
global $CFG;
$content = '';
// Set the page background image.
$imageurl = $theme->setting_file_url('backgroundimage', 'backgroundimage');
if (!empty($imageurl)) {
$content .= '$imageurl: "' . $imageurl . '";';
$content .= file_get_contents($CFG->dirroot .
'/theme/trending/scss/trending/body-background.scss');
}
if (!empty($theme->settings->navbardark)) {
$content .= file_get_contents($CFG->dirroot .
'/theme/trending/scss/trending/navbar-dark.scss');
} else {
$content .= file_get_contents($CFG->dirroot .
'/theme/trending/scss/trending/navbar-light.scss');
}
if (!empty($theme->settings->scss)) {
$content .= $theme->settings->scss;
}
return $content;
}
/**
* Get compiled css.
*
* @return string compiled css
*/
function theme_trending_get_precompiled_css() {
global $CFG;
return file_get_contents($CFG->dirroot . '/theme/trending/style/moodle.css');
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
function theme_trending_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
$coursecats = core_course_category::make_categories_list();
foreach ($coursecats as $key => $value) {
if ($context->contextlevel == CONTEXT_SYSTEM and $filearea === 'categoryimage'.$key) {
$theme = theme_config::load('trending');
return $theme->setting_file_serve('categoryimage'.$key, $args, $forcedownload, $options);
}
}
if ($context->contextlevel == CONTEXT_SYSTEM && ( $filearea === 'logo' || $filearea === 'defaultcategoryimage' || $filearea === 'allcoursesbg' || $filearea === 'enrolledcoursesbg' || $filearea === 'tutorsbg' || $filearea === 'loginbackground' || $filearea === 'tutor1image' || $filearea === 'tutor2image' || $filearea === 'tutor3image' || $filearea === 'tutor4image' || $filearea === 'tutor5image' || $filearea === 'tutor6image' || $filearea === 'tutor7image' || $filearea === 'tutor8image' || $filearea === 'tutor9image' || $filearea === 'student1image' || $filearea === 'student2image' || $filearea === 'student3image' || $filearea === 'student4image' || $filearea === 'student5image' || $filearea === 'footerlogo' || $filearea === 'internalbannerimage' || $filearea === 'favicon' || $filearea === 'aboutusimage')) {
$theme = theme_config::load('trending');
// By default, theme files must be cache-able by both browsers and proxies.
if (!array_key_exists('cacheability', $options)) {
$options['cacheability'] = 'public';
}
return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
} else {
send_file_not_found();
}    
}
/* Multilanguage
--------------------- */
function theme_trending_get_setting($setting, $format = false) {
global $CFG;
require_once($CFG->dirroot . '/lib/weblib.php');
static $theme;
if (empty($theme)) {
$theme = theme_config::load('trending');
}
if (empty($theme->settings->$setting)) {
return false;
} else if (!$format) {
return $theme->settings->$setting;
} else if ($format === 'format_text') {
return format_text($theme->settings->$setting, FORMAT_PLAIN);
} else if ($format === 'format_html') {
return format_text($theme->settings->$setting, FORMAT_HTML, array('trusted' => true, 'noclean' => true));
} else {
return format_string($theme->settings->$setting);
}
}
