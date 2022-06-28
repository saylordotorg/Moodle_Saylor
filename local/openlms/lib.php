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
 * Utility code for OpenLMS plugins.
 *
 * @package    local_openlms
 * @copyright  Copyright (c) 2022 Open LMS (https://www.openlms.net/)
 * @author     Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function local_openlms_extend_navigation(global_navigation $navigation) {
    if (isloggedin()
        && file_exists(__DIR__ . '/../../enrol/programs/version.php')
        && enrol_is_enabled('programs')) {

        $n = $navigation->create(get_string('myprograms', 'enrol_programs'),
            new moodle_url('/enrol/programs/my/index.php'),
            global_navigation::TYPE_CUSTOM,
            null,
            'myprograms',
            new pix_icon('myprograms', '', 'enrol_programs'));
        $n->showinflatnavigation = true;
        $navigation->add_node($n, 'mycourses');

        if (has_capability('enrol/programs:viewcatalogue', context_system::instance())) {
            $n = $navigation->create(get_string('catalogue', 'enrol_programs'),
                new moodle_url('/enrol/programs/catalogue/index.php'),
                global_navigation::TYPE_CUSTOM,
                null,
                'programscatalogue',
                new pix_icon('catalogue', '', 'enrol_programs'));
            $n->showinflatnavigation = true;
            $navigation->add_node($n, null);
        }
    }
}
