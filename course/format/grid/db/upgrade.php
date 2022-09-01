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
 * Grid Format.
 *
 * @package    format_grid
 * @version    See the value of '$plugin->version' in version.php.
 * @copyright  &copy; 2012 G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - {@link http://about.me/gjbarnard} and
 *                           {@link http://moodle.org/user/profile.php?id=442195}
 * @author     Based on code originally written by Paul Krix and Julian Ridden.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_format_grid_upgrade($oldversion = 0) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2022072200) {
        // Define table format_grid_image to be created.
        $table = new xmldb_table('format_grid_image');

        // Adding fields to table format_grid_image.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('image', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('contenthash', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null);
        $table->add_field('displayedimagestate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('sectionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table format_grid_image.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table format_grid_image.
        $table->add_index('section', XMLDB_INDEX_UNIQUE, ['sectionid']);
        $table->add_index('course', XMLDB_INDEX_NOTUNIQUE, ['courseid']);

        // Conditionally launch create table for format_grid_image.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $oldtable = new xmldb_table('format_grid_icon');
        if ($dbman->table_exists($oldtable)) {
            // Upgrade from old images.
            $oldimages = $DB->get_records('format_grid_icon');
            if (!empty($oldimages)) {
                $newimages = array();
                foreach ($oldimages as $oldimage) {
                    if (!empty($oldimage->image)) {
                        $newimagecontainer = new \stdClass();
                        $newimagecontainer->sectionid = $oldimage->sectionid;
                        $newimagecontainer->courseid = $oldimage->courseid;
                        $newimagecontainer->image = $oldimage->image;
                        $newimagecontainer->displayedimagestate = 0;
                        // Contenthash later!
                        $DB->insert_record('format_grid_image', $newimagecontainer, true);
                        $newimages[$newimagecontainer->sectionid] = $newimagecontainer;
                    }
                }

                $fs = get_file_storage();
                $currentcourseid = 0;
                foreach ($newimages as $newimage) {
                    if ($currentcourseid != $newimage->courseid) {
                        $currentcourseid = $newimage->courseid;
                        $coursecontext = context_course::instance($currentcourseid);
                        $files = $fs->get_area_files($coursecontext->id, 'course', 'section');
                        foreach ($files as $file) {
                            if (!$file->is_directory()) {
                                if ($file->get_filepath() == '/gridimage/') {
                                    $file->delete();
                                } else {
                                    $filename = $file->get_filename();
                                    $filesectionid = $file->get_itemid();
                                    if (array_key_exists($filesectionid, $newimages)) { // Ensure we know about this section.
                                        $gridimage = $newimages[$filesectionid];

                                        if (($gridimage) && ($gridimage->image == $filename)) { // Ensure the correct file.
                                            $filerecord = new stdClass();
                                            $filerecord->contextid = $coursecontext->id;
                                            $filerecord->component = 'format_grid';
                                            $filerecord->filearea = 'sectionimage';
                                            $filerecord->itemid = $filesectionid;
                                            $filerecord->filename = $filename;
                                            $newfile = $fs->create_file_from_storedfile($filerecord, $file);
                                            if ($newfile) {
                                                $DB->set_field('format_grid_image', 'contenthash', $newfile->get_contenthash(),
                                                    array('sectionid' => $filesectionid));
                                                // Don't delete the section file in case used in the summary.
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Delete 'format_grid_icon' and 'format_grid_summary' tables....
            $dbman->drop_table($oldtable);
            $oldsummarytable = new xmldb_table('format_grid_summary');
            $dbman->drop_table($oldsummarytable);
        }

        // Grid savepoint reached.
        upgrade_plugin_savepoint(true, 2022072200, 'format', 'grid');
    }

    // Automatic 'Purge all caches'....
    purge_all_caches();

    return true;
}
