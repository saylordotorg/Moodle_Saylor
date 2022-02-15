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
 * Tests for basic facetoface plugin functionnality.
 *
 * @package    mod_facetoface
 * @category   test
 * @copyright  2021 Catalyst IT Australia Pty Ltd
 * @author     Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot  . '/course/modlib.php');

/**
 * Class for unit testing mod_facetoface.
 *
 * @copyright 2021 Catalyst IT Australia Pty Ltd
 * @author    Ghaly Marc-Alexandre <marc-alexandreghaly@catalyst-ca.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class facetoface_test extends advanced_testcase {
    /**
     * @test
     *
     * Test to check facetoface is added properly.
     */
    public function add_facetofaceactivity() {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $facetofacerecords = $DB->get_records('facetoface');
        $module = $DB->get_record('modules', ['name' => 'facetoface']);
        $this->assertEmpty($facetofacerecords);
        $course = $this->getDataGenerator()->create_course();
        $module = (object) [
            'name' => 'Face to face name',
            'modulename' => 'facetoface',
            'module' => $module->id,
            'visible' => 1,
            'section' => 0
        ];
        $updatedmodule = add_moduleinfo($module, $course);
        $facetofacerecord = $DB->get_record('facetoface', ['course' => $updatedmodule->course]);
        $this->assertNotEmpty($facetofacerecord);
        $this->assertEquals($course->id, $facetofacerecord->course);
        $this->assertEquals($module->name, $facetofacerecord->name);
    }
}
