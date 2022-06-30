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
 * My programs overview block.
 *
 * @package    block_myprograms
 * @copyright  Copyright (c) 2022 Open LMS (https://www.openlms.net/)
 * @author     Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * My programs overview block.
 *
 * @package    block_myprograms
 * @copyright  Copyright (c) 2022 Open LMS (https://www.openlms.net/)
 * @author     Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_myprograms extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_myprograms');
    }

    public function get_content() {
        if (isset($this->content)) {
            return $this->content;
        }

        if (!enrol_is_enabled('programs') || !isloggedin() || isguestuser()) {
            return null;
        }

        /** @var \enrol_programs\output\my\renderer $myouput */
        $myouput = $this->page->get_renderer('enrol_programs', 'my');

        $this->content = new stdClass();
        $this->content->text = $myouput->render_block_content();
        $this->content->footer = $myouput->render_block_footer();

        return $this->content;
    }

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my' => true);
    }

    /**
     * Allow the block to have a configuration page.
     *
     * @return boolean
     */
    public function has_config() {
        return false;
    }
}

