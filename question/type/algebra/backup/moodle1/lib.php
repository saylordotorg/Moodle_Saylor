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
 * @package    qtype_algebra
 * @copyright  Roger Moore <rwmoore@ualberta.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Multichoice question type conversion handler
 */
class moodle1_qtype_algebra_handler extends moodle1_qtype_handler {

    /**
     * @return array
     */
    public function get_question_subpaths() {
        return array(
            'ANSWERS/ANSWER',
            'ALGEBRA',
            'ALGEBRA/VARLIST/VARIABLE'
        );
    }

    /**
     * Appends the algebra specific information to the question
     */
    public function process_question(array $data, array $raw) {
        // Convert and write the answers first.
        if (isset($data['answers'])) {
            $this->write_answers($data['answers'], $this->pluginname);
        }

        // Convert and write the algebra variables.
        if (isset($data['algebra'][0]['varlist']['variable'])) {
            $variables   = $data['algebra'][0]['varlist']['variable'];
        } else {
            $variables   = array();
        }
        $this->xmlwriter->begin_tag('algebra_variables');
        foreach ($variables as $variable) {
            $this->xmlwriter->begin_tag('algebra_variable', array('id' => $this->converter->get_nextid()));
            $this->xmlwriter->full_tag('name', $variable['name']);
            $this->xmlwriter->full_tag('min', $variable['min']);
            $this->xmlwriter->full_tag('max', $variable['max']);
            $this->xmlwriter->end_tag('algebra_variable');
        }
        $this->xmlwriter->end_tag('algebra_variables');

        // And finally the algebra options.
        $options = $data['algebra'][0];
        $this->xmlwriter->begin_tag('algebra', array('id' => $this->converter->get_nextid()));
        $this->xmlwriter->full_tag('compareby', $options['compareby']);
        $this->xmlwriter->full_tag('nchecks', $options['nchecks']);
        $this->xmlwriter->full_tag('tolerance', $options['tolerance']);
        $this->xmlwriter->full_tag('disallow', $options['disallow']);
        $this->xmlwriter->full_tag('allowedfuncs', $options['allowedfuncs']);
        $this->xmlwriter->full_tag('answerprefix', $options['answerprefix']);
        $this->xmlwriter->end_tag('algebra');
    }
}
