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
 * POAS abstract question type restore code.
 *
 * @package    qtype_poasquestion
 * @copyright  2012 Oleg Sychev, Volgograd State Technical University
 * @author     Valeriy Streltsov <vostreltsov@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/bank.php');

class restore_qtype_poasquestion_plugin extends restore_qtype_plugin {
    /**
     * Describes, whether plugin handles denormalized table of answers in Moodle
     * @var bool
     */
    protected $supportdenormalizedanswers = true;

    protected $qtypeobj;
    protected $oldquestionid;
    protected $newquestionid;
    protected $questioncreated;
    protected $currentanswer;

    public function __construct($plugintype, $pluginname, $step) {
        parent::__construct($plugintype, $pluginname, $step);
        $this->qtypeobj = question_bank::get_qtype($this->pluginname);
        $this->oldquestionid = -1;
        $this->newquestionid = -1;
        $this->questioncreated = false;
        $this->currentanswer = null;
    }

    /**
     * Returns the paths to be handled by the plugin at question level.
     */
    protected function define_question_plugin_structure() {
        $paths = array();

        // This qtype uses question_answers, add them.
        $this->add_question_question_answers($paths);

        // Add own qtype stuff.
        $elepath = $this->get_pathfor('/' . $this->qtypeobj->name());
        $paths[] = new restore_path_element($this->qtypeobj->name(), $elepath);

        $elepath = $this->get_pathfor('/answers/answer/extraanswerdata');
        $paths[] = new restore_path_element('extraanswerdata', $elepath);

        return $paths;
    }

    public function process_question_answer($data) {
        parent::process_question_answer($data);
        $this->currentanswer = $data;
        $this->currentanswer['newid'] = $this->get_mappingid('question_answer', $data['id']);
    }

    public function process_extraanswerdata($data) {
        global $DB;

        $extra = $this->qtypeobj->extra_answer_fields();
        $tablename = array_shift($extra);

        if ($this->questioncreated) {
            $data['answerid'] = $this->currentanswer['newid'];
            /*$newid =*/ $DB->insert_record($tablename, $data);
        } else {
            $DB->update_record($tablename, $data);
        }
    }

    /**
     * Process the qtype/... element.
     */
    public function process_poasquestion($data) {
        global $DB;

        $oldid = $data['id'];

        // Detect if the question is created or mapped.
        $this->oldquestionid   = $this->get_old_parentid('question');
        $this->newquestionid   = $this->get_new_parentid('question');
        $this->questioncreated = $this->get_mappingid('question_created', $this->oldquestionid) ? true : false;

        // If the question has been created by restore, we need to create its qtype_... too.
        if ($this->questioncreated) {
            $extraquestionfields = $this->qtypeobj->extra_question_fields();
            $tablename = array_shift($extraquestionfields);

            // Adjust some columns.
            $qtfield = $this->qtypeobj->questionid_column_name();
            $data[$qtfield] = $this->newquestionid;

            if ($this->supportdenormalizedanswers)  {
            }
            // Insert record.
            $newitemid = $DB->insert_record($tablename, $data);

            // Create mapping.
            $this->set_mapping($tablename, $oldid, $newitemid);
        }
    }
}
