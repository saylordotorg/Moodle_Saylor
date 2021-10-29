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
 * Defines the renderer for the deferred feedback with explicit validation
 * question behaviour.
 *
 * @package    qbehaviour_dfcbmexplicitvaildate
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/../deferredcbm/renderer.php');


/**
 * Renderer for outputting parts of a question using to the deferred
 * feedback with CBM and explicit validation behaviour.
 *
 * @copyright  2012 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qbehaviour_dfcbmexplicitvaildate_renderer extends qbehaviour_deferredcbm_renderer {

    public function controls(question_attempt $qa, question_display_options $options) {
        return parent::controls($qa, $options) . $this->submit_button($qa, $options);
    }
}
