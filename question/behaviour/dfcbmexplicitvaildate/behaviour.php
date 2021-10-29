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
 * Question behaviour that is like deferred feedback with CBM, but where students must
 * put in their answer once, then check that it has been interpreted OK, before
 * it is accepted as valid. To help with this validation step, and 'Check' button
 * is shown.
 *
 * @package    qbehaviour_dfcbmexplicitvaildate
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/../deferredcbm/behaviour.php');


/**
 * Question behaviour that is like deferred feedback with CBM, but where students must
 * put in their answer once, then check that it has been interpreted OK, before
 * it is accepted as valid. To help with this validation step, and 'Check' button
 * is shown.
 *
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbehaviour_dfcbmexplicitvaildate extends qbehaviour_deferredcbm {
    const IS_ARCHETYPAL = false;

    public static function get_unused_display_options() {
        return array('correctness', 'marks', 'specificfeedback', 'generalfeedback',
                'rightanswer');
    }

    // Note that we do not override get_expected_data. Although a check button
    // is displayed, and students can use it to trigger a save and validate
    // of the current page, this is not significantly different from any other
    // save, so we don't need to record that the button was clicked.
}
