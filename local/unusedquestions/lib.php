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
defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->dirroot . '/question/editlib.php');

function local_unusedquestions_get_question_bank_search_conditions($caller) {
    return array( new local_unusedquestions_question_bank_search_condition($caller));
}

class local_unusedquestions_question_bank_search_condition extends core_question\bank\search\condition  {
    protected $where;
    protected $params;

    const ONLYUSED = 1;
    const ONLYUNUSED = -1;
    const ONLYANY = 0;

    public function __construct() {
        $this->onlyused = optional_param('onlyused', 0, PARAM_INT);

        if ($this->onlyused != self::ONLYANY) {
            $this->init();
        }
    }

    public function where() {
        return $this->where;
    }

    public function params() {
        return $this->params;
    }

    public function display_options_adv() {
        echo "<br />\n";
        $options = array(self::ONLYUNUSED => get_string('onlyunused', 'local_unusedquestions'),
                self::ONLYUSED => get_string('onlyused', 'local_unusedquestions'));
        $attr = array ('class' => 'searchoptions');
        echo html_writer::select($options, 'onlyused', $this->onlyused,
                array(self::ONLYANY => get_string('usedandunused', 'local_unusedquestions')), $attr);
    }

    private function init() {
        global $DB;
        if ($this->onlyused == self::ONLYUSED) {
            $this->where = '(q.id IN (SELECT questionid FROM {quiz_slots}))';
        } else if ($this->onlyused == self::ONLYUNUSED) {
            $this->where = '(q.id NOT IN (SELECT questionid FROM {quiz_slots}))';
        }
    }

}
