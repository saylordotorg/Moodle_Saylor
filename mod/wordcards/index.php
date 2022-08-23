<?php
/**
 * Displays information about the wordcards in the course.
 *
 * @package mod_wordcards
 * @author  Frédéric Massart - FMCorz.net
 */

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);
require_login($course);

$PAGE->set_url('/mod/wordcards/index.php', ['id' => $id]);
$PAGE->set_pagelayout('incourse');

// Print the header.
$strplugin = get_string('pluginname', 'mod_wordcards');
$PAGE->navbar->add($strplugin);
$PAGE->set_title($strplugin);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($strplugin));

mod_wordcards_module::require_view_in_context($PAGE->context);

if (!($mods = get_all_instances_in_course('wordcards', $course))) {
    notice(get_string('thereareno', 'moodle', $strplugin), '../../course/view.php?id=$course->id');
}
$modinfo = get_fast_modinfo($course);

$table = new html_table();
$table->head  = [get_string('name', 'mod_wordcards')];

foreach ($modinfo->instances['wordcards'] as $cm) {
    if (!$cm->uservisible) {
        continue;
    }

    $url = new moodle_url('/mod/wordcards/view.php', ['id' => $cm->id]);

    $linkattrs = [];
    if (!$cm->visible) {
        $linkattrs = ['class' => 'dimmed'];
    }

    $name = html_writer::link($url, format_string($cm->name, true), $linkattrs);

    $table->data[] = [$name];
}

echo html_writer::table($table);

echo $OUTPUT->footer();
