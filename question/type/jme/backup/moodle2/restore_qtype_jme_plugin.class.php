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
 * @package    qtype_jme
 * @copyright  2013 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Restore plugin class that provides the necessary information
 * needed to restore one jme qtype plugin.
 *
 * @copyright  2013 Jean-Michel Vedrine
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_qtype_jme_plugin extends restore_qtype_plugin {

    /**
     * Returns the paths to be handled by the plugin at question level
     */
    protected function define_question_plugin_structure() {

        $paths = array();

        // This qtype uses question_answers, add them.
        $this->add_question_question_answers($paths);

        // Add own qtype stuff.
        $elename = 'jme';
        // We used get_recommended_name() so this works.
        $elepath = $this->get_pathfor('/jme');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths; // And we return the interesting paths.
    }

    /**
     * Process the qtype/jme element
     */
    public function process_jme($data) {
        global $DB, $CFG;

        $data = (object)$data;
        $oldid = $data->id;

        // Detect if the question is created or mapped.
        $oldquestionid   = $this->get_old_parentid('question');
        $newquestionid   = $this->get_new_parentid('question');
        $questioncreated = $this->get_mappingid('question_created', $oldquestionid) ? true : false;

        // If the question has been created by restore, we need to create its
        // qtype_jme_options too.
        if ($questioncreated) {
            $data->questionid = $newquestionid;
            if (!isset($data->jmeoptions)) {
                $data->jmeoptions = $CFG->qtype_jme_options;
            }
            if (!isset($data->width)) {
                $data->width = 360;
            }
            if (!isset($data->height)) {
                $data->height = 315;
            }
            $newitemid = $DB->insert_record('qtype_jme_options', $data);
            $this->set_mapping('qtype_jme_options', $oldid, $newitemid);
        }
    }

    /**
     * When restoring old data, that does not have the jme options information
     * in the XML, supply defaults.
     */
    protected function after_execute_question() {
        global $DB, $CFG;

        $jmewithoutoptions = $DB->get_records_sql("
                    SELECT *
                      FROM {question} q
                     WHERE q.qtype = ?
                       AND NOT EXISTS (
                        SELECT 1
                          FROM {qtype_jme_options}
                         WHERE questionid = q.id
                     )
                ", array('jme'));

        foreach ($jmewithoutoptions as $q) {
            $defaultoptions = new stdClass();
            $defaultoptions->questionid = $q->id;
            $defaultoptions->jmeoptions = $CFG->qtype_jme_options;
            $defaultoptions->width = 360;
            $defaultoptions->height = 315;
            $DB->insert_record('qtype_jme_options', $defaultoptions);
        }
    }
}
