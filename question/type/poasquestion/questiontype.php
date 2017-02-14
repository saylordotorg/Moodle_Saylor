<?php
// This file is part of Poasquestion question type - https://code.google.com/p/oasychev-moodle-plugins/
//
// Poasquestion question type is free software: you can redistribute it and/or modify
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
 * Defines the POAS abstract question type class.
 *
 * @package    qtype_poasquestion
 * @copyright  2012 Oleg Sychev, Volgograd State Technical University
 * @author     Valeriy Streltsov <vostreltsov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/type/questiontypebase.php');

class qtype_poasquestion extends question_type {

    public function menu_name() {
        // Don't include this question type in the 'add new question' menu.
        return false;
    }

    /**
     * Checks is dot of graphviz is installed and available via.
     */
    public static function is_dot_available() {
        global $CFG;

        if (empty($CFG->pathtodot)) {
            return false;
        }

        $cmd = escapeshellarg($CFG->pathtodot);

        $descriptorspec = array(0 => array('pipe', 'r'),  // stdin
                                1 => array('pipe', 'w'),  // stdout
                                2 => array('pipe', 'w')); // stderr

        $process = proc_open($cmd, $descriptorspec, $pipes);
        if (is_resource($process)) {
            fwrite($pipes[0], 'graph{"hey";}');
            fclose($pipes[0]);

            $err = stream_get_contents($pipes[2]);

            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            return empty($err);
        }
        return false;
    }
}
