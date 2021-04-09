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
 * @package    moodlecore
 * @subpackage backup-moodle2
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * restore plugin class that provides the necessary information
 * needed to restore one poodllrecording qtype plugin
 *
 * @copyright  2012 Justin Hunt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_qtype_poodllrecording_plugin extends restore_qtype_plugin {

    /**
     * Returns the paths to be handled by the plugin at question level
     */
    protected function define_question_plugin_structure() {
        return array(
            new restore_path_element('poodllrecording', $this->get_pathfor('/poodllrecording'))
        );
    }

    /**
     * Process the qtype/poodllrecording element
     */
    public function process_poodllrecording($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        // Detect if the question is created or mapped
        $questioncreated = $this->get_mappingid('question_created',
                $this->get_old_parentid('question')) ? true : false;

        // If the question has been created by restore, we need to create its
        // qtype_poodllrecording too
        if ($questioncreated) {
            $data->questionid = $this->get_new_parentid('question');
            $newitemid = $DB->insert_record('qtype_poodllrecording_opts', $data);
            $this->set_mapping('qtype_poodllrecording', $oldid, $newitemid);
        }
    }

    /**
     * Return the contents of this qtype to be processed by the links decoder
     */
    public static function define_decode_contents() {
        return array(
            new restore_decode_content('qtype_poodllrecording_opts', array(\qtype_poodllrecording\constants::FILEAREA_GRADERINFO,
                'qresource'),'qtype_poodllrecording')
        );
    }

    /**
     * When restoring old data, that does not have the poodllrecording options information
     * in the XML, supply defaults.
     */
    protected function after_execute_question() {
        global $DB;

        $poodllrecordingswithoutoptions = $DB->get_records_sql("
                    SELECT *
                      FROM {question} q
                     WHERE q.qtype = ?
                       AND NOT EXISTS (
                        SELECT 1
                          FROM {qtype_poodllrecording_opts}
                         WHERE questionid = q.id
                     )
                ", array('poodllrecording'));

        foreach ($poodllrecordingswithoutoptions as $q) {
            $defaultoptions = new stdClass();
            $defaultoptions->questionid = $q->id;
            $defaultoptions->responseformat = 'editor';
            $defaultoptions->responsefieldlines = 15;
            $defaultoptions->attachments = 0;
            $defaultoptions->graderinfo = '';
			$defaultoptions->qresource = '';
			$defaultoptions->boardsize = '320x320';
            $defaultoptions->graderinfoformat = FORMAT_HTML;
            $defaultoptions->timelimit = 0;
            $DB->insert_record('qtype_poodllrecording_opts', $defaultoptions);
        }
    }
}
