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
 * Search area for mod_facetoface activities.
 *
 * @package    mod_facetoface
 * @copyright  2016 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_facetoface\search;

defined('MOODLE_INTERNAL') || die();

/**
 * Search area for mod_facetoface activities.
 *
 * @package    mod_facetoface
 * @copyright  2016 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity extends \core_search\base_activity {
    /**
     * Activities with a time created field can overwrite this constant.
     */
    const CREATED_FIELD_NAME = 'timecreated';

    /**
     * Returns the document associated with this activity.
     *
     * @param stdClass $record Post info.
     * @return \core_search\document
     */
    public function get_document($record, $options = array()) {
        $doc = parent::get_document($record, $options);
        if (!$doc) {
            return false;
        }

        if (!empty($record->shortname)) {
            $doc->set_extra('shortname', "{$record->shortname}");
        }

        return $doc;
    }
}
