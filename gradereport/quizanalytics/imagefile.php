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
 * The gradebook quizanalytics report
 *
 * @package   gradereport_quizanalytics
 * @author Moumita Adak <moumita.a@dualcube.com>
 * @copyright Dualcube (https://dualcube.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
global $CFG;
$source  = required_param('source', PARAM_RAW);
$userid  = required_param('userid', PARAM_INT);

require_login();

if (!empty($source) && !empty($userid)) {
    $imagedata = $source;
    $userid = $userid;
    if (!file_exists($CFG->dirroot.'/grade/report/quizanalytics/images/')) {
        mkdir($CFG->dirroot.'/grade/report/quizanalytics/images/', 0755, true);
    }
    if (!file_exists($CFG->dirroot.'/grade/report/quizanalytics/images/'.$userid)) {
        mkdir($CFG->dirroot.'/grade/report/quizanalytics/images/'.$userid, 0755, true);
    }
    $oldfiles = glob($CFG->dirroot.'/grade/report/quizanalytics/images/'.$userid.'/*');
    foreach ($oldfiles as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    $filtereddata = substr($imagedata, strpos($imagedata, ",") + 1);
    $unencodeddata = base64_decode($filtereddata);
    $filename = '/'.rand().".png";
    $filepath = $CFG->dirroot.'/grade/report/quizanalytics/images/'.$userid.$filename;
    $fileurl = $CFG->wwwroot.'/grade/report/quizanalytics/images/'.$userid.$filename;
    if (file_exists($filepath)) {
        unlink($filepath);
    }
    $fp = fopen( $filepath, 'wb' );
    fwrite( $fp, $unencodeddata);
    fclose( $fp );
    echo $fileurl;
}
