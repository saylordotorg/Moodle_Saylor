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
 * Availability cohort - Tests for cohort restrictions
 *
 * @package     availability_cohort
 * @copyright   2018 Ulm University <kathrin.osswald@uni-ulm.de>
 *              based on code of availability_group 2014 The Open University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_cohort;

/**
 * Unit tests for the condition.
 *
 * @package     availability_cohort
 * @copyright   2018 Ulm University <kathrin.osswald@uni-ulm.de>
 *              based on code of availability_group 2014 The Open University
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition_test extends \advanced_testcase {
    /**
     * Load required classes.
     */
    public function setUp(): void {
        // Load the mock info class so that it can be used.
        global $CFG;
        require_once($CFG->dirroot . '/availability/tests/fixtures/mock_info.php');
    }

    /**
     * Tests constructing and using condition.
     */
    public function test_usage() {
        global $CFG, $USER;
        $this->resetAfterTest();
        $CFG->enableavailability = true;

        // Erase static cache before test.
        condition::wipe_static_cache();

        // Make a test course and user.
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id);
        $info = new \core_availability\mock_info($course, $user->id);

        // Make two cohorts.
        $cohort1 = $generator->create_cohort(array('idnumber' => 1, 'name' => 'Cohort 1'));
        $cohort2 = $generator->create_cohort(array('idnumber' => 2, 'name' => 'Cohort 2'));

        // Do test (not in cohort).
        $cond = new condition((object)array('id' => (int)$cohort1->id));

        // Check if available (when not available).
        $this->assertFalse($cond->is_available(false, $info, true, $user->id));
        $information = $cond->get_description(false, false, $info);
        $this->assertMatchesRegularExpression('~You belong to .*<strong>Cohort 1</strong>~', $information);
        $this->assertTrue($cond->is_available(true, $info, true, $user->id));

        // Add user to cohorts and refresh cache.
        cohort_add_member($cohort1->id, $user->id);
        cohort_add_member($cohort2->id, $user->id);
        get_fast_modinfo($course->id, 0, true);

        // Recheck.
        $this->assertTrue($cond->is_available(false, $info, true, $user->id));
        $this->assertFalse($cond->is_available(true, $info, true, $user->id));
        $information = $cond->get_description(false, true, $info);
        $this->assertMatchesRegularExpression('~do <strong>not</strong> belong to .*<strong>Cohort 1</strong>~', $information);

        // Check cohort 2 works also.
        $cond = new condition((object)array('id' => (int)$cohort2->id));
        $this->assertTrue($cond->is_available(false, $info, true, $user->id));

        // What about an 'any cohort' condition?
        $cond = new condition((object)array());
        $this->assertTrue($cond->is_available(false, $info, true, $user->id));
        $this->assertFalse($cond->is_available(true, $info, true, $user->id));
        $information = $cond->get_description(false, true, $info);
        $this->assertMatchesRegularExpression('~do <strong>not</strong> belong to <strong>any cohort</strong>~', $information);

        // Cohort that doesn't exist uses 'missing' text.
        $cond = new condition((object)array('id' => $cohort2->id + 1000));
        $this->assertFalse($cond->is_available(false, $info, true, $user->id));
        $information = $cond->get_description(false, false, $info);
        $this->assertMatchesRegularExpression('~You belong to.*\(Missing cohort\)~', $information);
    }

    /**
     * Tests the constructor including error conditions. Also tests the
     * string conversion feature (intended for debugging only).
     */
    public function test_constructor() {
        // Invalid id (not int).
        $structure = (object)array('id' => 'bourne');
        try {
            $cond = new condition($structure);
            $this->fail();
        } catch (\coding_exception $e) {
            $this->assertStringContainsString('Invalid ->id', $e->getMessage());
        }

        // Valid (with id).
        $structure->id = 123;
        $cond = new condition($structure);
        $this->assertEquals('{cohort:#123}', (string)$cond);

        // Valid (no id).
        unset($structure->id);
        $cond = new condition($structure);
        $this->assertEquals('{cohort:any}', (string)$cond);
    }

    /**
     * Tests the save() function.
     */
    public function test_save() {
        $structure = (object)array('id' => 123);
        $cond = new condition($structure);
        $structure->type = 'cohort';
        $this->assertEquals($structure, $cond->save());

        $structure = (object)array();
        $cond = new condition($structure);
        $structure->type = 'cohort';
        $this->assertEquals($structure, $cond->save());
    }
}
