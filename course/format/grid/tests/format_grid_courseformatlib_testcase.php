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
 * Grid Format - A topics based format that uses a grid of user selectable images to popup a light box of the section.
 *
 * @package    format_grid
 * @version    See the value of '$plugin->version' in version.php.
 * @copyright  &copy; 2016+ G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - {@link http://about.me/gjbarnard} and
 *                           {@link http://moodle.org/user/profile.php?id=442195}
 * @author     Based on code originally written by Paul Krix and Julian Ridden.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Lib unit tests for the Grid course format.
 * @group format_grid
 */
class format_grid_courseformatlib_testcase extends advanced_testcase {
    protected $courseone;
    protected $coursetwo;
    protected $courseformatone;
    protected $courseformattwo;

    protected function setUp(): void {
        $this->resetAfterTest(true);

        set_config('theme', 'boost');
        // Ref: https://docs.moodle.org/dev/Writing_PHPUnit_tests.
        $this->courseone = $this->getDataGenerator()->create_course(
            array('format' => 'grid',
                'numsections' => 1,
                'hidesectiontitle' => 2,
                'imagecontaineralignment' => 'left',
                'sectiontitlegridlengthmaxoption' => 24,
                'currentselectedimagecontainertextcolour' => '#ffffff',
                'sectiontitleboxposition' => 1,
                'sectiontitleboxinsideposition' => 2,
                'sectiontitleboxheight' => 42,
                'sectiontitleboxopacity' => '.3',
                'sectiontitlefontsize' => 24,
                'sectiontitlealignment' => 'left',
                'sectiontitleinsidetitletextcolour' => '#ffffff',
                'sectiontitleinsidetitlebackgroundcolour' => '#000000',
                'showsectiontitlesummary' => 1,
                'setshowsectiontitlesummaryposition' => 2,
                'sectiontitlesummarymaxlength' => 20,
                'sectiontitlesummarytextcolour' => '#ffffff',
                'sectiontitlesummarybackgroundcolour' => '#000000',
                'sectiontitlesummarybackgroundopacity' => '.3',
                'setsection0ownpagenogridonesection' => 1),
            array('createsections' => true));
        $this->courseformatone = course_get_format($this->courseone);
        $this->coursetwo = $this->getDataGenerator()->create_course(
            array('format' => 'grid',
                'numsections' => 1,
                'hidesectiontitle' => 2,
                'imagecontaineralignment' => 'right',
                'sectiontitlegridlengthmaxoption' => 12,
                'currentselectedimagecontainertextcolour' => '#244896',
                'sectiontitleboxposition' => 1,
                'sectiontitleboxinsideposition' => 3,
                'sectiontitleboxheight' => 34,
                'sectiontitleboxopacity' => '.7',
                'sectiontitlefontsize' => 12,
                'sectiontitlealignment' => 'right',
                'sectiontitleinsidetitletextcolour' => '#ffffff',
                'sectiontitleinsidetitlebackgroundcolour' => '#000000',
                'showsectiontitlesummary' => 1,
                'setshowsectiontitlesummaryposition' => 3,
                'sectiontitlesummarymaxlength' => 10,
                'sectiontitlesummarytextcolour' => '#ffffff',
                'sectiontitlesummarybackgroundcolour' => '#000000',
                'sectiontitlesummarybackgroundopacity' => '.7',
                'setsection0ownpagenogridonesection' => 2),
            array('createsections' => true));
        $this->courseformattwo = course_get_format($this->coursetwo);
    }

    public function test_reset_image_container_alignment() {
        $this->setAdminUser();
        $data = new stdClass;
        $data->resetimagecontaineralignment = true;
        $this->courseformatone->update_course_format_options($data);
        $cfo1 = $this->courseformatone->get_format_options();
        $cfo2 = $this->courseformattwo->get_format_options();

        $this->assertEquals('-', $cfo1['imagecontaineralignment']);
        $this->assertEquals('right', $cfo2['imagecontaineralignment']);
    }

    public function test_reset_all_image_container_alignments() {
        $this->setAdminUser();
        $data = new stdClass;
        $data->resetallimagecontaineralignment = true;
        $this->courseformatone->update_course_format_options($data);
        $cfo1 = $this->courseformatone->get_format_options();
        $cfo2 = $this->courseformattwo->get_format_options();

        $this->assertEquals('-', $cfo1['imagecontaineralignment']);
        $this->assertEquals('-', $cfo2['imagecontaineralignment']);
    }

    public function test_reset_image_container_navigation() {
        $this->setAdminUser();
        $data = new stdClass;
        $data->resetimagecontainernavigation = true;
        $this->courseformatone->update_course_format_options($data);
        $cfo1 = $this->courseformatone->get_format_options();
        $cfo2 = $this->courseformattwo->get_format_options();

        $this->assertEquals(0, $cfo1['setsection0ownpagenogridonesection']);
        $this->assertEquals(2, $cfo2['setsection0ownpagenogridonesection']);
    }

    public function test_reset_all_image_container_navigations() {
        $this->setAdminUser();
        $data = new stdClass;
        $data->resetallimagecontainernavigation = true;
        $this->courseformatone->update_course_format_options($data);
        $cfo1 = $this->courseformatone->get_format_options();
        $cfo2 = $this->courseformattwo->get_format_options();

        $this->assertEquals(0, $cfo1['setsection0ownpagenogridonesection']);
        $this->assertEquals(0, $cfo2['setsection0ownpagenogridonesection']);
    }

    public function test_reset_image_container_style() {
        $this->setAdminUser();
        $data = new stdClass;
        $data->resetimagecontainerstyle = true;
        $this->courseformatone->update_course_format_options($data);
        $cfo1 = $this->courseformatone->get_format_options();
        $cfo2 = $this->courseformattwo->get_format_options();

        $this->assertEquals('-', $cfo1['currentselectedimagecontainertextcolour']);
        $this->assertEquals('244896', $cfo2['currentselectedimagecontainertextcolour']);
    }

    public function test_reset_all_image_container_styles() {
        $this->setAdminUser();
        $data = new stdClass;
        $data->resetallimagecontainerstyle = true;
        $this->courseformatone->update_course_format_options($data);
        $cfo1 = $this->courseformatone->get_format_options();
        $cfo2 = $this->courseformatone->get_format_options();

        $this->assertEquals('-', $cfo1['currentselectedimagecontainertextcolour']);
        $this->assertEquals('-', $cfo2['currentselectedimagecontainertextcolour']);
    }

    public function test_reset_section_title_options() {
        $this->setAdminUser();
        $data = new stdClass;
        $data->resetsectiontitleoptions = true;
        $this->courseformatone->update_course_format_options($data);
        $cfo = $this->courseformatone->get_format_options();

        $this->assertEquals(0, $cfo['hidesectiontitle']);
        $this->assertEquals('-', $cfo['sectiontitlegridlengthmaxoption']);
        $this->assertEquals(0, $cfo['sectiontitleboxposition']);
        $this->assertEquals(0, $cfo['sectiontitleboxinsideposition']);
        $this->assertEquals('-', $cfo['sectiontitleboxheight']);
        $this->assertEquals('-', $cfo['sectiontitleboxopacity']);
        $this->assertEquals('-', $cfo['sectiontitlefontsize']);
        $this->assertEquals('-', $cfo['sectiontitlealignment']);
        $this->assertEquals('-', $cfo['sectiontitleinsidetitletextcolour']);
        $this->assertEquals('-', $cfo['sectiontitleinsidetitlebackgroundcolour']);
        $this->assertEquals(0, $cfo['showsectiontitlesummary']);
        $this->assertEquals(0, $cfo['setshowsectiontitlesummaryposition']);
        $this->assertEquals('-', $cfo['sectiontitlesummarymaxlength']);
        $this->assertEquals('-', $cfo['sectiontitlesummarytextcolour']);
        $this->assertEquals('-', $cfo['sectiontitlesummarybackgroundcolour']);
        $this->assertEquals('-', $cfo['sectiontitlesummarybackgroundopacity']);
    }

    public function test_reset_all_section_title_options() {
        $this->setAdminUser();
        $data = new stdClass;
        $data->resetallsectiontitleoptions = true;
        $this->courseformatone->update_course_format_options($data);
        $cfo1 = $this->courseformatone->get_format_options();
        $cfo2 = $this->courseformattwo->get_format_options();

        $this->assertEquals(0, $cfo1['hidesectiontitle']);
        $this->assertEquals('-', $cfo1['sectiontitlegridlengthmaxoption']);
        $this->assertEquals(0, $cfo1['sectiontitleboxposition']);
        $this->assertEquals(0, $cfo1['sectiontitleboxinsideposition']);
        $this->assertEquals('-', $cfo1['sectiontitleboxheight']);
        $this->assertEquals('-', $cfo1['sectiontitleboxopacity']);
        $this->assertEquals('-', $cfo1['sectiontitlefontsize']);
        $this->assertEquals('-', $cfo1['sectiontitlealignment']);
        $this->assertEquals('-', $cfo1['sectiontitleinsidetitletextcolour']);
        $this->assertEquals('-', $cfo1['sectiontitleinsidetitlebackgroundcolour']);
        $this->assertEquals(0, $cfo1['showsectiontitlesummary']);
        $this->assertEquals(0, $cfo1['setshowsectiontitlesummaryposition']);
        $this->assertEquals('-', $cfo1['sectiontitlesummarymaxlength']);
        $this->assertEquals('-', $cfo1['sectiontitlesummarytextcolour']);
        $this->assertEquals('-', $cfo1['sectiontitlesummarybackgroundcolour']);
        $this->assertEquals('-', $cfo1['sectiontitlesummarybackgroundopacity']);

        $this->assertEquals(0, $cfo2['hidesectiontitle']);
        $this->assertEquals('-', $cfo2['sectiontitlegridlengthmaxoption']);
        $this->assertEquals(0, $cfo2['sectiontitleboxposition']);
        $this->assertEquals(0, $cfo2['sectiontitleboxinsideposition']);
        $this->assertEquals('-', $cfo2['sectiontitleboxheight']);
        $this->assertEquals('-', $cfo2['sectiontitleboxopacity']);
        $this->assertEquals('-', $cfo2['sectiontitlefontsize']);
        $this->assertEquals('-', $cfo2['sectiontitlealignment']);
        $this->assertEquals('-', $cfo2['sectiontitleinsidetitletextcolour']);
        $this->assertEquals('-', $cfo2['sectiontitleinsidetitlebackgroundcolour']);
        $this->assertEquals(0, $cfo2['showsectiontitlesummary']);
        $this->assertEquals(0, $cfo2['setshowsectiontitlesummaryposition']);
        $this->assertEquals('-', $cfo2['sectiontitlesummarymaxlength']);
        $this->assertEquals('-', $cfo2['sectiontitlesummarytextcolour']);
        $this->assertEquals('-', $cfo2['sectiontitlesummarybackgroundcolour']);
        $this->assertEquals('-', $cfo2['sectiontitlesummarybackgroundopacity']);
    }

    public function test_get_set_show_section_title_summary_position() {
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course(
            array('format' => 'grid',
                'numsections' => 1,
                'setshowsectiontitlesummaryposition' => 1),
            array('createsections' => false));
        $this->assertEquals('top', course_get_format($course)->get_set_show_section_title_summary_position());

        $course = $this->getDataGenerator()->create_course(
            array('format' => 'grid',
                'numsections' => 1,
                'setshowsectiontitlesummaryposition' => 2),
            array('createsections' => false));
        $this->assertEquals('bottom', course_get_format($course)->get_set_show_section_title_summary_position());

        $course = $this->getDataGenerator()->create_course(
            array('format' => 'grid',
                'numsections' => 1,
                'setshowsectiontitlesummaryposition' => 3),
            array('createsections' => false));
        $this->assertEquals('left', course_get_format($course)->get_set_show_section_title_summary_position());

        $course = $this->getDataGenerator()->create_course(
            array('format' => 'grid',
                'numsections' => 1,
                'setshowsectiontitlesummaryposition' => 4),
            array('createsections' => false));
        $this->assertEquals('right', course_get_format($course)->get_set_show_section_title_summary_position());
    }
}
