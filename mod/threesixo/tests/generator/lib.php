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
 * mod_threesixo data generator.
 *
 * @package mod_threesixo
 * @category test
 * @copyright 2018 Jun Pataleta
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_threesixo\api;

defined('MOODLE_INTERNAL') || die();

/**
 * mod_threesixo data generator class.
 *
 * @package mod_threesixo
 * @category test
 * @copyright 2018 Jun Pataleta
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_threesixo_generator extends testing_module_generator {

    /**
     * Creates a 360 degree feedback instance based on the record given.
     *
     * @param stdClass $record Data for module being generated. Requires 'course' key (an id or the full object).
     *                         Also can have any fields from add module form.
     * @param array $options General options for course module. Optional.
     * @return stdClass Record from the threesixo table with additional field cmid (corresponding id in course_modules table)
     */
    public function create_instance($record = null, array $options = null) {
        global $DB;

        $record = (object)(array)$record;

        if (!empty($record->participantrolename)) {
            $roleid = $DB->get_field('role', 'id', ['shortname' => $record->participantrolename], MUST_EXIST);
            $record->participantrole = $roleid;
            unset($record->participantrolename);
        }

        if (!isset($record->timemodified)) {
            $record->timemodified = time();
        }

        if (!isset($record->timeopen)) {
            $record->timeopen = 0;
        }

        if (!isset($record->timeclose)) {
            $record->timeclose = 0;
        }

        $threesixo = parent::create_instance($record, (array)$options);

        // Generate sample questions for this instance.
        $ratedquestions = [
            'Treats co-workers with courtesy and respect.',
            'Has a positive attitude.',
            'Has initiative needed without relying on co-workers unnecessarily.',
            'Can capably lead projects effectively.',
            'Possesses strong technical skills for their position.',
            'Appears to be efficient and well organised.',
            'Delivers on their commitments.',
            'Contributes to the successful functioning of the team.',
            'Has good communication skills both verbal and written.',
            'Expresses thoughts, opinions, and ideas, in meetings and discussions.',
            'Explains ideas clearly.',
            'Makes an effort to listen and tries to understand other people\'s ideas.'
        ];
        $commentquestions = [
            'What positive comments can you give me?',
            'What are some things you encourage me to focus on?'
        ];
        $questions = [];
        foreach ($ratedquestions as $question) {
            $questions[] = (object)[
                'question' => $question,
                'type' => api::QTYPE_RATED
            ];
        }
        foreach ($commentquestions as $question) {
            $questions[] = (object)[
                'question' => $question,
                'type' => api::QTYPE_COMMENT
            ];
        }
        $questionids = [];
        foreach ($questions as $question) {
            $questionids[] = api::add_question($question);
        }
        // Assign questions for this instance.
        api::set_items($threesixo->id, $questionids);
        // Make this instance ready to the users.
        api::make_ready($threesixo->id);

        return $threesixo;
    }
}
