<?php

namespace mod_solo\attempt;

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
 * Internal library of functions for module solo
 *
 * All the solo specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_solo
 * @copyright  COPYRIGHTNOTICE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \mod_solo\constants;

class helper
{


    public static function delete_attempt($solo, $attemptid, $context)
    {
        global $DB;
        $ret = false;

        //remove records
        if (!$DB->delete_records(constants::M_ATTEMPTSTABLE, array('id' => $attemptid))) {
            print_error("Could not delete attempt");
            return $ret;
        }


        //remove files
        $fs = get_file_storage();

        $fileareas = array(constants::M_FILEAREA_TOPICMEDIA,constants::M_FILEAREA_SUBMISSIONS);
        foreach ($fileareas as $filearea) {
            $fs->delete_area_files($context->id, 'mod_solo', $filearea, $attemptid);
        }
        $ret = true;
        return $ret;
    }


    public static function fetch_editor_options($course, $modulecontext)
    {
        $maxfiles = 99;
        $maxbytes = $course->maxbytes;
        return array('trusttext' => true, 'subdirs' => true, 'maxfiles' => $maxfiles,
            'maxbytes' => $maxbytes, 'context' => $modulecontext);
    }

    public static function fetch_filemanager_options($course, $maxfiles = 1)
    {
        $maxbytes = $course->maxbytes;
        return array('subdirs' => true, 'maxfiles' => $maxfiles, 'maxbytes' => $maxbytes, 'accepted_types' => array('audio', 'image'));
    }

}
