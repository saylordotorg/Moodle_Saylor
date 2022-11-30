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
 * Unit tests for the relativedate condition.
 *
 * @package   availability_relativedate
 * @copyright 2022 eWallah.net
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace availability_relativedate;

use availability_relativedate\condition;
use \core_availability\tree;
use \core_availability\mock_info;
use \core_availability\info_module;

/**
 * Unit tests for the relativedate condition.
 *
 * @package   availability_relativedate
 * @copyright 2022 eWallah.net
 * @author    Renaat Debleu <info@eWallah.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @coversDefaultClass \availability_relativedate\condition
 */
class condition_test extends \advanced_testcase {

    /** @var stdClass course. */
    private $course;

    /** @var stdClass user. */
    private $user;

    /**
     * Create course and page.
     */
    public function setUp():void {
        global $CFG;
        require_once($CFG->dirroot . '/availability/tests/fixtures/mock_info.php');
        require_once($CFG->dirroot . '/availability/tests/fixtures/mock_info_module.php');
        require_once($CFG->libdir . '/completionlib.php');
        $this->resetAfterTest();
        $this->setAdminUser();
        $CFG->enablecompletion = true;
        $CFG->enableavailability = true;
        set_config('enableavailability', true);
        $dg = $this->getDataGenerator();
        $now = time();
        $this->course = $dg->create_course(['startdate' => $now, 'enddate' => $now + 7 * WEEKSECS, 'enablecompletion' => 1]);
        $this->user = $dg->create_user(['timezone' => 'UTC']);
        $dg->enrol_user($this->user->id, $this->course->id, 5, time());
    }

    /**
     * Relative dates tree provider.
     */
    public function tree_provider(): array {
        return [
            'Before start course' => [2, 1, 6, "-2 hour", "Until"],
            'After start course' => [2, 1, 1, "+2 hour", "From"],
            'Before end course' => [3, 2, 2, '-3 day', 'Until'],
            'After end course' => [3, 2, 5, '+3 day', 'From'],
            'After end enrol' => [4, 3, 3, '+4 week', 'From'],
            'After end method' => [4, 3, 4, '+4 week', 'From'],
        ];
    }

    /**
     * Test tree.
     *
     * @dataProvider tree_provider
     * @param int $n number to skip
     * @param int $d Minute - hour - day - week  - month
     * @param int $s relative to
     * @param string $str
     * @param string $result
     * @covers \availability_relativedate\condition
     */
    public function test_tree($n, $d, $s, $str, $result) {
        $arr = (object)['type' => 'relativedate', 'n' => $n, 'd' => $d, 's' => $s, 'm' => 99999];
        $stru = (object)['op' => '|', 'show' => true, 'c' => [$arr]];
        $tree = new tree($stru);
        $this->assertFalse($tree->is_available_for_all());
        $this->setUser($this->user);
        $info = new mock_info($this->course, $this->user->id);
        $strf = get_string('strftimedatetime', 'langconfig');
        $nau = 'Not available unless:';
        $calc = userdate(strtotime($str, $this->get_reldate($s)), $strf, 0);
        $this->assertEquals("$nau $result $calc", $tree->get_full_information($info));
        $tree->check_available(false, $info, false, $this->user->id)->is_available();
    }

    /**
     * Tests relative module.
     * @covers \availability_relativedate\condition
     */
    public function test_relative_module() {
        $this->setTimezone('UTC');
        $dg = $this->getDataGenerator();
        $page = $dg->get_plugin_generator('mod_page')->create_instance(['course' => $this->course]);
        $stru = (object)['op' => '|', 'show' => true,
            'c' => [(object)['type' => 'relativedate', 'n' => 7, 'd' => 0, 's' => 7, 'm' => $page->cmid]]];
        $tree = new tree($stru);
        $this->setUser($this->user);
        $info = new mock_info($this->course, $this->user->id);
        list($sql, $params) = $tree->get_user_list_sql(false, $info, false);
        $this->assertEquals('', $sql);
        $this->assertEquals([], $params);
        // 7 Minutes after completion of module.
        $this->assertStringContainsString('7 minutes after completion of activity', $tree->get_full_information($info));
        $this->do_cron();
        $this->assertFalse($tree->is_available_for_all());

    }

    /**
     * Relative dates description provider.
     */
    public function description_provider(): array {
        return [
            'Before start course' => [2, 1, 6, '-2 hour', 'Until', 'From', '2 hours before course start date'],
            'After start course' => [2, 1, 1, '+2 hour', 'From', 'Until', '2 hours after course start date'],
            'Before end course' => [3, 2, 2, '-3 day', 'Until', 'From', '3 days before course end date'],
            'After end course' => [3, 2, 5, '+3 day', 'From', 'Until', '3 days after course end date'],
            'After end enrol' => [4, 3, 3, '+4 week', 'From', 'Until', '4 weeks after user enrolment date'],
            'After end method' => [4, 3, 4, '+4 week', 'From', 'Until', '4 weeks after enrolment method end date'],
        ];
    }

    /**
     * Test descrtiption.
     *
     * @dataProvider description_provider
     * @param int $n number to skip
     * @param int $d Minute - hour - day - week  - month
     * @param int $s relative to
     * @param string $str
     * @param string $result1
     * @param string $result2
     * @param string $result3
     * @covers \availability_relativedate\condition
     */
    public function test_description($n, $d, $s, $str, $result1, $result2, $result3): void {
        $strf = get_string('strftimedatetime', 'langconfig');
        $nau = 'Not available unless:';
        $info = new mock_info($this->course, $this->user->id);
        $this->setUser($this->user);
        $cond = new condition((object)['type' => 'relativedate', 'n' => $n, 'd' => $d, 's' => $s, 'm' => 99999]);
        $calc = userdate(strtotime($str, $this->get_reldate($s)), $strf);
        $this->assertEquals("$result1 $calc", $cond->get_description(true, false, $info));
        $this->assertEquals("$result2 $calc", $cond->get_description(true, true, $info));
        $this->assertEquals("$result1 $calc", $cond->get_description(false, false, $info));
        $this->assertEquals("$result2 $calc", $cond->get_description(false, true, $info));
        $this->assertEquals("$nau $result1 $calc", $cond->get_standalone_description(false, false, $info));
        $this->assertEquals("$nau $result2 $calc", $cond->get_standalone_description(false, true, $info));

        $this->setAdminUser();
        $this->assertStringContainsString($result3, $cond->get_description(true, false, $info));
        $this->assertNotEmpty($cond->get_standalone_description(false, false, $info));
    }

    /**
     * Tests the get_description and get_standalone_description functions.
     * @covers \availability_relativedate\condition
     */
    public function test_get_description() {
        global $DB;
        $this->get_reldate(4);
        $info = new mock_info($this->course, $this->user->id);
        $this->setUser($this->user);

        $pg = $this->getDataGenerator()->get_plugin_generator('mod_page');
        $page0 = $pg->create_instance(['course' => $this->course, 'completion' => COMPLETION_TRACKING_MANUAL]);
        $page1 = $pg->create_instance(['course' => $this->course, 'completion' => COMPLETION_TRACKING_MANUAL]);

        $str = '{"op":"|","show":true,"c":[{"type":"relativedate","n":4,"d":4,"s":7,"m":' . $page1->cmid . '}]}';
        $DB->set_field('course_modules', 'availability', $str, ['id' => $page0->cmid]);
        rebuild_course_cache($this->course->id, true);
        $cond = new condition((object)['type' => 'relativedate', 'n' => 4, 'd' => 4, 's' => 7, 'm' => $page1->cmid]);
        $this->assertStringContainsString("4 months after completion of activity", $cond->get_description(true, false, $info));
        $this->assertStringContainsString("4 months after completion of activity", $cond->get_description(true, true, $info));
        $this->assertStringContainsString("4 months after completion of activity", $cond->get_description(false, false, $info));
        $this->assertStringContainsString("4 months after completion of activity", $cond->get_description(false, true, $info));
        $this->assertFalse($cond->completion_value_used($this->course, $page0->cmid));
        $this->assertTrue($cond->completion_value_used($this->course, $page1->cmid));

        $modinfo = get_fast_modinfo($this->course);
        $str = '{"op":"|","show":true,"c":[{"type":"relativedate","n":4,"d":4,"s":7,"m":' . $page0->cmid . '}]}';
        foreach ($modinfo->get_section_info_all() as $section) {
            $DB->set_field('course_sections', 'availability', $str, ['id' => $section->id]);
        }
        $this->do_cron();
        $cond = new condition((object)['type' => 'relativedate', 'n' => 4, 'd' => 4, 's' => 7, 'm' => $page1->cmid]);
        $this->assertTrue($cond->completion_value_used($this->course, $page0->cmid));
        $this->assertTrue($cond->completion_value_used($this->course, $page1->cmid));
        $completion = new \completion_info($this->course);
        $completion->reset_all_state($modinfo->get_cm($page1->cmid));

        $cond = new condition((object)['type' => 'relativedate', 'n' => 4, 'd' => 4, 's' => 7, 'm' => $page0->cmid]);
        $this->assertFalse($cond->update_dependency_id('courses', $page0->cmid, 3));
        $this->assertTrue($cond->update_dependency_id('course_modules', $page0->cmid, 3));
    }

    /**
     * Tests a course with no enddate.
     * @covers \availability_relativedate\condition
     */
    public function test_noenddate() {
        global $DB, $USER;
        $dg = $this->getDataGenerator();
        $now = time();
        $course1 = $dg->create_course(['enablecompletion' => 1]);
        $course2 = $dg->create_course(['enddate' => $now + 14 * WEEKSECS, 'enablecompletion' => 1]);
        $user = $dg->create_user();
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'student']);
        $dg->enrol_user($user->id, $course1->id, $roleid);
        $dg->enrol_user($user->id, $course2->id, $roleid);
        $pg = $this->getDataGenerator()->get_plugin_generator('mod_page');
        $page1 = $pg->create_instance(['course' => $course1, 'completion' => COMPLETION_TRACKING_MANUAL]);
        $page2 = $pg->create_instance(['course' => $course2, 'completion' => COMPLETION_TRACKING_MANUAL]);
        $modinfo1 = get_fast_modinfo($course1);
        $modinfo2 = get_fast_modinfo($course2);
        $cm1 = $modinfo1->get_cm($page1->cmid);
        $cm2 = $modinfo2->get_cm($page2->cmid);
        $info = new info_module($cm1);
        $cond = new condition((object)['type' => 'relativedate', 'n' => 7, 'd' => 2, 's' => 2, 'm' => 1]);
        $information = $cond->get_description(true, false, $info);
        $this->assertEquals('This course has no end date', $information);
        $this->assertEquals('{relativedate: 7 days before course end date}', "$cond");
        // No enddate => Never available.
        $this->assertFalse($cond->is_available(false, $info, false, $user->id));
        $this->assertFalse($cond->is_available(true, $info, false, $user->id));
        $info = new info_module($cm2);
        $information = $cond->get_description(true, false, $info);
        $strf = get_string('strftimedatetime', 'langconfig');
        $this->assertStringNotContainsString('(No course enddate)', $information);
        $str = userdate($course2->enddate - (7 * 24 * 3600), $strf);
        $this->assertEquals("Until $str (7 days before course end date)", $information);
        $this->assertEquals('{relativedate: 7 days before course end date}', "$cond");
        $this->assertFalse($cond->is_available(false, $info, false, $user->id));
        $this->assertTrue($cond->is_available(true, $info, false, $user->id));
        $this->assertFalse($cond->is_available(false, $info, false, null));
        $this->assertTrue($cond->is_available(true, $info, false, null));

        $cond = new condition((object)['type' => 'relativedate', 'n' => 7, 'd' => 2, 's' => 3, 'm' => 1]);
        $information = $cond->get_description(true, false, $info);
        $this->assertEquals('(7 days after user enrolment date)', $information);
        $this->assertFalse($cond->is_available(false, $info, false, $USER->id));
        $this->assertFalse($cond->is_available(true, $info, false, $USER->id));

        $cond = new condition((object)['type' => 'relativedate', 'n' => 7, 'd' => 2, 's' => 4, 'm' => 1]);
        $information = $cond->get_description(false, false, $info);
        $this->assertEquals('(7 days after enrolment method end date)', $information);
    }

    /**
     * Tests a reflection.
     * @covers \availability_relativedate\condition
     */
    public function test_reflection() {
        $cond = new condition((object)['type' => 'relativedate', 'n' => 3, 'd' => 1, 's' => 7, 'm' => 999999]);
        $condition = new \availability_relativedate\condition($cond);
        $name = 'availability_relativedate\condition';
        $result = \phpunit_util::call_internal_method($condition, 'get_debug_string', [], $name);
        $this->assertEquals(' 1 day after course start date', $result);
        $result = \phpunit_util::call_internal_method($condition, 'calc', [$this->course, $this->user->id], $name);
        $this->assertEquals($this->course->startdate + 24 * 3600, $result);
        $result = \phpunit_util::call_internal_method($cond, 'get_debug_string', [], $name);
        $this->assertStringContainsString('missing', $result);

        $cond = new condition((object)['type' => 'relativedate', 'n' => 3, 'd' => 1, 's' => 9, 'm' => 999999]);
        $condition = new \availability_relativedate\condition($cond);
        $result = \phpunit_util::call_internal_method($condition, 'calc', [$this->course, $this->user->id], $name);
        $this->assertEquals($this->course->startdate + 24 * 3600, $result);
    }

    /**
     * Tests the autoupdate event.
     * @covers \availability_relativedate\autoupdate
     */
    public function test_autoupdate() {
        global $DB;
        $pg = $this->getDataGenerator()->get_plugin_generator('mod_page');
        $page0 = $pg->create_instance(['course' => $this->course, 'completion' => COMPLETION_TRACKING_MANUAL]);
        $page1 = $pg->create_instance(['course' => $this->course, 'completion' => COMPLETION_TRACKING_MANUAL]);
        $str = '{"op":"|","show":true,"c":[{"type":"relativedate","n":4,"d":4,"s":7,"m":' . $page0->cmid . '}]}';
        $DB->set_field('course_modules', 'availability', $str, ['id' => $page1->cmid]);
        $this->do_cron();
        $event = \core\event\course_module_updated::create([
            'objectid' => $page0->cmid,
            'relateduserid' => 1,
            'context' => \context_course::instance($this->course->id),
            'courseid' => $this->course->id,
            'other' => [
                'relateduserid' => 1,
                'modulename' => 'page',
                'instanceid' => $page0->cmid,
                'name' => $page0->name]]);
        $event->trigger();
        \availability_relativedate\autoupdate::update_from_event($event);
    }

    /**
     * Cron function.
     * @coversNothing
     */
    private function do_cron() {
        $task = new \core\task\completion_regular_task();
        ob_start();
        $task->execute();
        sleep(1);
        $task->execute();
        \phpunit_util::run_all_adhoc_tasks();
        ob_end_clean();
        get_fast_modinfo(0, 0, true);
        rebuild_course_cache($this->course->id, true, true);
    }

    /**
     * Which date.
     * @coversNothing
     *
     * @param int $s
     * @return int
     */
    private function get_reldate($s): int {
        global $DB;
        switch ($s) {
            case 1:
            case 6:
                return $this->course->startdate;
            case 2;
            case 5;
                return $this->course->enddate;
            case 3:
            case 4:
                $now = time();
                $selfplugin = enrol_get_plugin('self');
                $instance = $DB->get_record('enrol', ['courseid' => $this->course->id, 'enrol' => 'self'], '*', MUST_EXIST);
                $DB->set_field('enrol', 'enrolenddate', $now + 1000, ['id' => $instance->id]);
                $instance = $DB->get_record('enrol', ['courseid' => $this->course->id, 'enrol' => 'self'], '*', MUST_EXIST);
                $selfplugin->enrol_user($instance, $this->user->id, 5, $now);
                return ($s === 3) ? $now : $now + 1000;
        }
        return 0;
    }
}
