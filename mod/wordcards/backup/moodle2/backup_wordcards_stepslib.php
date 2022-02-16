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
 * Define all the backup steps that will be used by the backup_wordcards_activity_task
 *
 * @package   mod_wordcards
 * @category  backup
 * @copyright 2019 Your Name <justin@poodll.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
/**
 * Define the complete wordcards structure for backup, with file and id annotations
 *
 * @package   mod_wordcards
 * @category  backup
 * @copyright 2016 Jerome Mouneyrac <jerome@mouneyrac.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_wordcards_activity_structure_step extends backup_activity_structure_step {
    /**
     * Defines the backup structure of the module
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        // Get know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');
        // Define the root element describing the wordcards instance.
        $wordcards = new backup_nested_element('wordcards', array('id'), array(
            'name', 'intro', 'introformat', 'journeymode','step1termcount', 'step2termcount', 'step3termcount', 'step4termcount','step5termcount',
                'grade','gradeoptions','mingrade',
                'step1practicetype','step2practicetype','step3practicetype','step4practicetype','step5practicetype',
                'completionwhenfinish','maxattempts', 'timecreated', 'timemodified','skipreview', 'finishedstepmsg',
                'completedmsg', 'ttslanguage','deflanguage','transcriber','passagehash','hashisold','foriframe',
                'showimageflip', 'frontfaceflip','viewstart','viewend'));

        $terms = new backup_nested_element('terms');
        $term = new backup_nested_element('term', array('id'), array(
            'term', 'definition','model_sentence','sourcedef','translations','image','audio','model_sentence_audio', 'ttsvoice','alternates','deleted','phonetic','phoneticms'));

        $seens = new backup_nested_element('seens');
        $seen = new backup_nested_element('seen', array('id'), array(
            'userid', 'timecreated'));

        $associations = new backup_nested_element('associations');
        $association = new backup_nested_element('association', array('id'), array(
            'userid', 'lastfail', 'lastsuccess', 'failcount', 'successcount'));

        $progresses = new backup_nested_element('progresses');
        $progress = new backup_nested_element('progress', array('id'), array(
            'userid', 'state', 'statedata','grade1','grade2','grade3','grade4','grade5','totalgrade','timecreated'));

        // If we had more elements, we would build the tree here.
        $wordcards->add_child($terms);
        $terms->add_child($term);

        $term->add_child($seens);
        $seens->add_child($seen);

        $term->add_child($associations);
        $associations->add_child($association);

        $wordcards->add_child($progresses);
        $progresses->add_child($progress);

        // Define data sources.
        $wordcards->set_source_table('wordcards', array('id' => backup::VAR_ACTIVITYID));

        $term->set_source_sql('
            SELECT *
              FROM {wordcards_terms}
             WHERE modid = ?',
            array(backup::VAR_PARENTID));

        // All the rest of elements only happen if we are including user info
        if ($userinfo) {
            $seen->set_source_table('wordcards_seen', array('termid' => '../../id'));

            $association->set_source_table('wordcards_associations', array('termid' => '../../id'));

            $progress->set_source_sql('
            SELECT *
              FROM {wordcards_progress}
             WHERE modid = ?',
            array(backup::VAR_PARENTID));
        }

        // If we were referring to other tables, we would annotate the relation
        // with the element's annotate_ids() method.
        $seen->annotate_ids('user', 'userid');
        $association->annotate_ids('user', 'userid');
        $progress->annotate_ids('user', 'userid');

        // Define file annotations (we do not use itemid in this example).
        $wordcards->annotate_files('mod_wordcards', 'intro', null);
        $term->annotate_files('mod_wordcards', 'image', 'id');
        $term->annotate_files('mod_wordcards', 'audio', 'id');
        $term->annotate_files('mod_wordcards', 'model_sentence_audio', 'id');

        // Return the root element (wordcards), wrapped into standard activity structure.
        return $this->prepare_activity_structure($wordcards);
    }
}
