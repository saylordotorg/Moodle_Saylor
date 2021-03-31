<?php
/**
 * @package     block_sharing_cart
 * @copyright   2020 Praxis
 * @companyinfo https://praxis.dk
 */

use block_sharing_cart\repositories\course_repository;
use block_sharing_cart\tests\sharing_chart_test_tools;

defined('MOODLE_INTERNAL') || die();


/**
 * Class block_sharing_cart_course_repository_testcase
 */
class block_sharing_cart_course_repository_testcase extends advanced_testcase
{
    use sharing_chart_test_tools;

    /**
     * This method is called before each test.
     */
    protected function setUp() {
        $this->resetAfterTest();
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_get_course_fullnames_from_sharing_chart_entities() {

        $user = $this->create_user();
        self::setUser($user);

        $course1 = $this->create_course();
        $course2 = $this->create_course();

        $item1 = $this->create_sharing_chart_record($user, $course1);
        $item2 = $this->create_sharing_chart_record($user, $course2);
        $items = [
            (int)$item1->id => $item1,
            (int)$item2->id => $item2,
        ];

        $repo = new course_repository($this->db());
        $actual_course_fullnames = $repo->get_course_fullnames_by_sharing_carts($items);
        $expect_course_fullnames = [
            (int)$course1->id => $course1->fullname,
            (int)$course2->id => $course2->fullname,
        ];

        natsort($actual_course_fullnames);
        natsort($expect_course_fullnames);

        $this->assertSame($expect_course_fullnames, $actual_course_fullnames);
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_get_course_fullnames_from_empty_entity_list() {
        $user = $this->create_user();
        self::setUser($user);

        $this->create_course();
        $this->create_course();

        $repo = new course_repository($this->db());
        $actual_course_fullnames = $repo->get_course_fullnames_by_sharing_carts([]);

        $this->assertEmpty($actual_course_fullnames);
    }

    /**
     * @return moodle_database|null
     */
    private function db() {
        global $DB;
        return $DB;
    }
}
