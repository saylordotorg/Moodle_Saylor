<?php
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . "/course/renderer.php");
class theme_trending_core_course_renderer extends core_course_renderer {
protected $enablecategoryimage;
public function __construct(moodle_page $page, $target) {
parent::__construct($page, $target);
static $theme;
if (empty($theme)) {
$theme = theme_config::load('trending');
}
$this->enablecategoryimage = (!empty($theme->settings->enablecategoryimage)) ? $theme->settings->enablecategoryimage : false;
}
protected function coursecat_coursebox(coursecat_helper $chelper, $course, $additionalclasses = '') {
global $CFG;
if (!isset($this->strings->summary)) {
$this->strings->summary = get_string('summary');
}
if ($chelper->get_show_courses() <= self::COURSECAT_SHOW_COURSES_COUNT) {
return '';
}
if ($course instanceof stdClass) {
//require_once($CFG->libdir. '/coursecatlib.php');
$course = new core_course_list_element($course);
}
$content = '';
$classes = trim('panel panel-default coursebox clearfix '. $additionalclasses);
if ($chelper->get_show_courses() < self::COURSECAT_SHOW_COURSES_EXPANDED) {
$classes .= ' collapsed';
}
// .coursebox
$content .= html_writer::start_tag('div', array(
'class' => $classes,
'data-courseid' => $course->id,
'data-type' => self::COURSECAT_TYPE_COURSE,
));
// Display course overview files.
$content .= html_writer::start_tag('div', array('class' => 'panel-image'));
$contentimages = $contentfiles = '';
foreach ($course->get_course_overviewfiles() as $file) {
$isimage = $file->is_valid_image();
$url = file_encode_url("$CFG->wwwroot/pluginfile.php",
'/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
$file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
if ($isimage) {
//$contentimages .= html_writer::start_tag('div', array('class' => 'imagebox'));
$images = html_writer::empty_tag('img', array('src' => $url, 'alt' => 'Course Image '. $course->fullname,
'class' => 'courseimage'));
$contentimages .= html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)), $images);
//$contentimages .= html_writer::end_tag('div');
} else {
$image = $this->output->image_icon(file_file_icon($file, 24), $file->get_filename(), 'moodle');
$filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
html_writer::tag('span', $file->get_filename(), array('class' => 'fp-filename'));
$contentfiles .= html_writer::tag('span',
html_writer::link($url, $filename),
array('class' => 'coursefile fp-filename-icon'));
}
}
$content .= $contentimages. $contentfiles;
$content .= html_writer::end_tag('div'); // .panel-image
$content .= html_writer::start_tag('div', array('class' => 'content panel-body'));
$content .= html_writer::start_tag('div', array('class' => 'panel-heading info'));
// course name
$coursename = $chelper->get_course_formatted_name($course);
$coursenamelink = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)),
$coursename, array('class' => $course->visible ? '' : 'dimmed'));
$content .= html_writer::tag('span', $coursenamelink, array('class' => 'coursename'));
// If we display course in collapsed form but the course has summary or course contacts, display the link to the info page.
$content .= html_writer::start_tag('span', array('class' => 'moreinfo'));
if ($chelper->get_show_courses() < self::COURSECAT_SHOW_COURSES_EXPANDED) {
if ($course->has_summary() || $course->has_course_contacts() || $course->has_course_overviewfiles()) {
$url = new moodle_url('/course/info.php', array('id' => $course->id));
$image = html_writer::empty_tag('img', array('src' => $this->output->image_url('i/info'),
'alt' => $this->strings->summary));
$content .= html_writer::link($url, $image, array('title' => $this->strings->summary));
// Make sure JS file to expand course content is included.
$this->coursecat_include_js();
}
}
$content .= html_writer::end_tag('span'); // .moreinfo
// print enrolmenticons
if ($icons = enrol_get_course_info_icons($course)) {
$content .= html_writer::start_tag('div', array('class' => 'enrolmenticons'));
foreach ($icons as $image_icon) {
$content .= $this->render($image_icon);
}
$content .= html_writer::end_tag('div'); // .enrolmenticons
}
$content .= html_writer::end_tag('div'); // .info
$content .= html_writer::start_tag('div', array('class' => 'inner-con'));
$content .= $this->coursecat_coursebox_content($chelper, $course);
$content .= html_writer::end_tag('div'); // .inner-con
if ($chelper->get_show_courses() >= self::COURSECAT_SHOW_COURSES_EXPANDED) {
$icondirection = 'left';
if ('ltr' === get_string('thisdirection', 'langconfig')) {
$icondirection = 'right';
}
//if (is_enrolled(context_course::instance($course->id))) {
//$arrow = html_writer::tag('span', '', array('class' => ' arrow-right'));
$visit = html_writer::tag('span', get_string('course'));
$visitlink = html_writer::link(new moodle_url('/course/view.php',
array('id' => $course->id)), $visit);
$content .= html_writer::tag('div', $visitlink, array('class' => 'visitlink'));
//}
}
$content .= html_writer::end_tag('div'); // .content
$content .= html_writer::end_tag('div'); // .coursebox
return $content;
}
protected function coursecat_coursebox_content(coursecat_helper $chelper, $course) {
global $CFG;
if ($chelper->get_show_courses() < self::COURSECAT_SHOW_COURSES_EXPANDED) {
return '';
}
if ($course instanceof stdClass) {
//require_once($CFG->libdir. '/coursecatlib.php');
$course = new core_course_list_element($course);
}
$content = '';
// Display course summary.
if ($course->has_summary()) {
$content .= $chelper->get_course_formatted_summary($course);
}
// Display course contacts. See core_course_list_element::get_course_contacts().
if ($course->has_course_contacts()) {
$content .= html_writer::start_tag('ul', array('class' => 'teachers'));
foreach ($course->get_course_contacts() as $userid => $coursecontact) {
global $DB, $OUTPUT;
$user = $DB->get_record('user', array('id' => $userid));
$face = $OUTPUT->user_picture($user, array('size' => 50));
$name = $face.$coursecontact['rolename'].': '.
html_writer::link(new moodle_url('/user/view.php',
array('id' => $userid, 'course' => SITEID)),
$coursecontact['username']);
$content .= html_writer::tag('li', $name);
}
$content .= html_writer::end_tag('ul'); // .teachers
}
return $content;
}
protected function coursecat_category(coursecat_helper $chelper, $coursecat, $depth) {
//$content .= html_writer::start_tag('div', array('class' => 'customcategory')); 
global $theme, $PAGE;
if (!$this->enablecategoryimage) {
return parent::coursecat_category($chelper, $coursecat, $depth);
}
global $CFG, $DB;
// Open category tag.
$classes = array('category');
if (empty($coursecat->visible)) {
$classes[] = 'dimmed_category';
}
if ($chelper->get_subcat_depth() > 0 && $depth >= $chelper->get_subcat_depth()) {
// Do not load content.
$categorycontent = '';
$classes[] = 'notloaded';
if ($coursecat->get_children_count() ||
($chelper->get_show_courses() >= self::COURSECAT_SHOW_COURSES_COLLAPSED && $coursecat->get_courses_count())
) {
$classes[] = 'with_children';
$classes[] = 'collapsed';
}
} else {
// Load category content.
$categorycontent = $this->coursecat_category_content($chelper, $coursecat, $depth);
$classes[] = 'loaded';
if (!empty($categorycontent)) {
$classes[] = 'with_children';
}
}
$classes[] = 'trendingcats';
//$classes[] = 'span3';
if (intval($CFG->version) >= 2013111800) {
// Make sure JS file to expand category content is included.
$this->coursecat_include_js();
}
$content = html_writer::start_tag('div', array(
'class' => join(' ', $classes),
'data-categoryid' => $coursecat->id,
'data-depth' => $depth,
'data-showcourses' => $chelper->get_show_courses(),
'data-type' => self::COURSECAT_TYPE_CATEGORY,
));
// Category name.
$categoryname = html_writer::tag('span', $coursecat->get_formatted_name());
$categoryimagenum = 'categoryimage' . $coursecat->id;
// Do a settings check to output our icon for the category.
if (!empty($PAGE->theme->settings->enablecategoryimage)) {
if (!empty($PAGE->theme->settings->$categoryimagenum) &&
!empty($PAGE->theme->settings->enablecustomcategoryimage)
) {
// User has set a value for the category.
$val =  $PAGE->theme->setting_file_url($categoryimagenum, $categoryimagenum);
} else {
// User hasn't set a value for the category, get the default.
$val =  $PAGE->theme->setting_file_url('defaultcategoryimage', 'defaultcategoryimage');
}
}
if (!empty($val)) {
$icon = html_writer::tag('img', '', array('aria-hidden' => 'true', 'src' => '' . $val));
} else {
$df = $CFG->wwwroot . "/theme/trending/pix/defaultCategories.png";
$icon = html_writer::tag('img', '', array('aria-hidden' => 'true', 'src' => '' . $df));
}
$categoryimage = html_writer::link(new moodle_url('/course/index.php', array('categoryid' => $coursecat->id)), $icon);
$categoryname = html_writer::link(new moodle_url('/course/index.php', array('categoryid' => $coursecat->id)), $categoryname);
if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_COUNT && ($coursescount = $coursecat->get_courses_count())) {
$categoryname .= html_writer::tag('span', ' (' . $coursescount . ')', array('title' => get_string('numberofcourses'), 'class' => 'numberofcourse'));
}
$content .= html_writer::tag('div', $categoryimage, array('class' => 'categoryimage'));
$content .= html_writer::start_tag('div', array('class' => 'info'));
$content .= html_writer::tag(($depth > 1) ? 'h4' : 'h3', $categoryname, array('class' => 'categoryname'));
$content .= html_writer::end_tag('div'); // Class .info.
$content .= html_writer::start_tag('div', array('class' => 'categorydescription'));
$content .= html_writer::tag('div', $coursecat->description);
$content .= html_writer::end_tag('div'); // Class .categorydescription.
// Add category content to the output.
$content .= html_writer::tag('div', $categorycontent, array('class' => 'content'));
$content .= html_writer::end_tag('div'); // Class .category.
return $content;
}
}
