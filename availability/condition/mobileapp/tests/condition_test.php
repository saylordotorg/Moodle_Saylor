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
 * Unit tests for the Mobile app condition.
 *
 * @package availability_mobileapp
 * @copyright Juan Leyva <juan@moodle.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use availability_mobileapp\condition;

global $CFG;

/**
 * Unit tests for the Mobile app condition.
 *
 * @package availability_mobileapp
 * @copyright availability_mobileapp
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class availability_mobileapp_condition_testcase extends advanced_testcase {
    /**
     * Load required classes.
     */
    public function setUp() {
        // Load the mock info class so that it can be used.
        global $CFG, $DB;
        require_once($CFG->dirroot . '/availability/tests/fixtures/mock_info.php');
    }

    /**
     * Tests constructing and using condition as part of tree.
     */
    public function test_in_tree() {
        global $USER, $CFG;
        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $page = $generator->get_plugin_generator('mod_page')->create_instance(
                array('course' => $course->id));

        $modinfo = get_fast_modinfo($course);
        $cm = $modinfo->get_cm($page->cmid);
        $info = new \core_availability\mock_info($course, $USER->id);

        $structure = (object)array('op' => '|', 'show' => true, 'c' => array(
                (object)array('type' => 'mobileapp', 'cm' => (int)$cm->id,
                'e' => condition::NOT_MOBILE_APP)));
        $tree = new \core_availability\tree($structure);

        // Check it's true.
        $result = $tree->check_available(false, $info, true, $USER->id);
        $this->assertTrue($result->is_available());

        // We cannot mock the WS_SERVER, so we need to create a new condion tree.
        $structure = (object)array('op' => '|', 'show' => true, 'c' => array(
                (object)array('type' => 'mobileapp', 'cm' => (int)$cm->id,
                'e' => condition::MOBILE_APP)));
        $tree = new \core_availability\tree($structure);

        // Check it's false.
        $result = $tree->check_available(false, $info, true, $USER->id);
        $this->assertFalse($result->is_available());

    }

    /**
     * Tests the constructor including error conditions. Also tests the
     * string conversion feature (intended for debugging only).
     */
    public function test_constructor() {
        // No parameters.
        $structure = new stdClass();

        // Invalid $e.
        try {
            $cond = new condition($structure);
            $this->fail();
        } catch (coding_exception $e) {
            $this->assertContains('Missing or invalid ->e', $e->getMessage());
        }

        // Successful construct & display with all different expected values.
        $structure->e = condition::NOT_MOBILE_APP;
        $cond = new condition($structure);
        $this->assertEquals('{mobileapp:#2}', (string)$cond);

        $structure->e = condition::MOBILE_APP;
        $cond = new condition($structure);
        $this->assertEquals('{mobileapp:#1}', (string)$cond);
    }

    /**
     * Tests the save() function.
     */
    public function test_save() {
        $structure = (object)array('e' => condition::MOBILE_APP);
        $cond = new condition($structure);
        $structure->type = 'mobileapp';
        $this->assertEquals($structure, $cond->save());
    }

    /**
     * Tests the is_available and get_description functions.
     */
    public function test_usage() {
        global $USER;
        $this->resetAfterTest();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $info = new \core_availability\mock_info($course, $USER->id);

        $mobileapp = new condition((object)array('e' => condition::MOBILE_APP));
        $this->assertFalse($mobileapp->is_available(false, $info, true, $USER->id));

        $mobileapp = new condition((object)array('e' => condition::NOT_MOBILE_APP));
        $this->assertTrue($mobileapp->is_available(false, $info, true, $USER->id));

    }
}
