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
 * @package    local_mb2builder
 * @copyright  2018 - 2020 Mariusz Boloz (https://mb2themes.com/)
 * @license    Commercial https://themeforest.net/licenses
 */

defined('MOODLE_INTERNAL') || die();




/**
 * Serve the files from the MYPLUGIN file areas
 *
 * @param stdClass $course the course object
 * @param stdClass $cm the course module object
 * @param stdClass $context the context
 * @param string $filearea the name of the file area
 * @param array $args extra arguments (itemid, path)
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if the file not found, just send the file otherwise and do not return anything
 */
function local_mb2builder_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {

    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    if ($context->contextlevel != CONTEXT_SYSTEM)
    {
        return false;
    }

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'images' && $filearea !== 'pagesexport')
    {
        return false;
    }

    // Make sure the user is logged in and has access to the module (plugins that are not course modules should leave out the 'cm' part).
    //require_login($course, false, $cm);

    // Check the relevant capabilities - these may vary depending on the filearea being accessed.
    if (!has_capability('local/mb2builder:view', $context))
    {
        //return false;
    }

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    //$itemid = array_shift($args); // The first item in the $args array.

    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    //if (!$args) {
    $filepath = '/'; // $args is empty => the path is '/'
    //} else {
    //    $filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
    //}

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'local_mb2builder', $filearea, 0, $filepath, $filename);
    if (!$file)
    {
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    // From Moodle 2.3, use send_stored_file instead.
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}




function local_mb2builder_get_images_base_url()
{
    global $CFG;

    $output = '';
    $context = \context_system::instance();

    if ($CFG->slasharguments)
    {
        $output = new moodle_url($CFG->wwwroot . '/pluginfile.php/' . $context->id . '/local_mb2builder/images/',array());
    }
    else
    {
        $output = new moodle_url($CFG->wwwroot . '/pluginfile.php',array('file' => '/' . $context->id . '/local_mb2builder/images/'));
    }

    return $output;

}


function local_mb2builder_get_db_pages()
{

    global $CFG, $DB;
    $results = array();
    $context = \context_system::instance();

    $query = 'SELECT * FROM ' . $CFG->prefix . 'files WHERE component=\'local_mb2builder\' AND contextid=' . $context->id;
    $row =  $DB->get_records_sql($query);

    foreach ($row as $el)
    {
        $results[] = $el->filename;
    }

    return $results;
}



function local_mb2builder_showon_field($data)
{

    $output = '';

    if ($data == '')
    {
        return;
    }

    $data_arr = explode(':', $data);

    $output .= ' data-showon_field="' . $data_arr[0] . '"';
    $output .= ' data-showon_value="' . $data_arr[1] . '"';

    return $output;

}
