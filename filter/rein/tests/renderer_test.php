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
 * Test the REIN library renderer functions.
 *
 * @package   filter_rein
 * @author    Amy Groshek <amy.groshek@remote-learner.net>
 * @copyright 2013 onwards Remote-Learner {@link http://www.remote-learner.net/}
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

defined('MOODLE_INTERNAL') || die();

global $OUTPUT, $CFG;
require_once(dirname(__FILE__).'/../../../config.php');
require_once(dirname(__FILE__).'/../renderer.php');
require_once($CFG->dirroot.'/lib/adminlib.php');

class filter_rein_renderer_testcase extends advanced_testcase {

    /**
     * Test that debug interface intro content prints.
     */
    public function test_print_debug_intro() {
        $dummypage = new moodle_page();
        $target = 'filter_rein';
        $renderer = new filter_rein_renderer($dummypage, $target);
        $output = $renderer->print_debug_intro();

        $this->assertContains('<h2>', $output);
    }

    /**
     * Test that debug interface accordion content prints.
     *
     * @param string $reinoptions Name of theme for setting database values
     * @param string $urlparams Name of database table item to set and test
     * @dataProvider renderer_provider
     */
    public function test_print_debug_accordion($reinoptions, $urlparams) {
        $dummypage = new moodle_page();
        $target = 'filter_rein';
        $renderer = new filter_rein_renderer($dummypage, $target);
        $output = $renderer->print_debug_accordion($reinoptions, $urlparams);

        $this->assertContains('<h2>', $output);
        $this->assertContains('class="desc"', $output);
        $this->assertContains('class="instr"', $output);
        $this->assertContains('class="btn"', $output);
        $this->assertContains('class="doAccordion rein-plugin"', $output);
    }

    /**
     * Test that debug interface tabs content prints.
     *
     * @param string $reinoptions Name of theme for setting database values
     * @param string $urlparams Name of database table item to set and test
     * @dataProvider renderer_provider
     */
    public function test_print_debug_tabs($reinoptions, $urlparams) {
        $dummypage = new moodle_page();
        $target = 'filter_rein';
        $renderer = new filter_rein_renderer($dummypage, $target);
        $output = $renderer->print_debug_tabs($reinoptions, $urlparams);

        $this->assertContains('<h2>', $output);
        $this->assertContains('class="desc"', $output);
        $this->assertContains('class="instr"', $output);
        $this->assertContains('class="btn"', $output);
        $this->assertContains('class="doTabs rein-plugin"', $output);
    }

    /**
     * Test that debug interface click hotspot content prints.
     *
     * @param string $reinoptions Name of theme for setting database values
     * @param string $urlparams Name of database table item to set and test
     * @dataProvider renderer_provider
     */
    public function test_print_debug_clickhotspot($reinoptions, $urlparams) {
        global $CFG;

        $dummypage = new moodle_page();
        $target = 'filter_rein';
        $renderer = new filter_rein_renderer($dummypage, $target);
        $imgpath = $CFG->wwwroot.'/filter/rein/pix/demo/';
        $output = $renderer->print_debug_clickhotspot($reinoptions, $imgpath, $urlparams);

        $this->assertContains('<h2>', $output);
        $this->assertContains('class="desc"', $output);
        $this->assertContains('class="instr"', $output);
        $this->assertContains('class="btn"', $output);
        $this->assertContains('class="clickHotspot rein-plugin', $output);
    }

    /**
     * Test that debug interface sort multiple lists content prints.
     *
     * @param string $reinoptions Name of theme for setting database values
     * @param string $urlparams Name of database table item to set and test
     * @dataProvider renderer_provider
     */
    public function test_print_debug_sortmultiple($reinoptions, $urlparams) {
        $dummypage = new moodle_page();
        $target = 'filter_rein';
        $renderer = new filter_rein_renderer($dummypage, $target);
        $output = $renderer->print_debug_sortmultiple($reinoptions, $urlparams);

        $this->assertContains('<h2>', $output);
        $this->assertContains('class="desc"', $output);
        $this->assertContains('class="instr"', $output);
        $this->assertContains('class="btn"', $output);
        $this->assertContains('class="sortMultipleLists rein-plugin', $output);
    }

    /**
     * Test that debug interface drop bubble content prints.
     *
     * @param string $reinoptions Name of theme for setting database values
     * @param string $urlparams Name of database table item to set and test
     * @dataProvider renderer_provider
     */
    public function test_print_debug_dropbubble($reinoptions, $urlparams) {
        $dummypage = new moodle_page();
        $target = 'filter_rein';
        $renderer = new filter_rein_renderer($dummypage, $target);
        $output = $renderer->print_debug_dropbubble($reinoptions, $urlparams);

        $this->assertContains('<h2>', $output);
        $this->assertContains('class="desc"', $output);
        $this->assertContains('class="instr"', $output);
        $this->assertContains('class="btn"', $output);
        $this->assertContains('class="do-multi-drop rein-plugin', $output);
    }

    /**
     * Test that debug interface stepwise process content prints.
     *
     * @param string $reinoptions Name of theme for setting database values
     * @param string $urlparams Name of database table item to set and test
     * @dataProvider renderer_provider
     */
    public function test_print_debug_stepwise($reinoptions, $urlparams) {
        $dummypage = new moodle_page();
        $target = 'filter_rein';
        $renderer = new filter_rein_renderer($dummypage, $target);
        $output = $renderer->print_debug_stepwise($reinoptions, $urlparams);

        $this->assertContains('<h2>', $output);
        $this->assertContains('class="desc"', $output);
        $this->assertContains('class="instr"', $output);
        $this->assertContains('class="btn"', $output);
        $this->assertContains('class="do-stepwise rein-plugin', $output);
    }

    /**
     * Test that debug interface stepwise process content prints.
     *
     * @param string $reinoptions Name of theme for setting database values
     * @param string $urlparams Name of database table item to set and test
     * @dataProvider renderer_provider
     */
    public function test_print_debug_sequential($reinoptions, $urlparams) {
        global $CFG;

        $dummypage = new moodle_page();
        $target = 'filter_rein';
        $renderer = new filter_rein_renderer($dummypage, $target);
        $imgpath = $CFG->wwwroot.'/filter/rein/pix/demo/';
        $output = $renderer->print_debug_sequential($reinoptions, $imgpath, $urlparams);

        $this->assertContains('<h2>', $output);
        $this->assertContains('class="desc"', $output);
        $this->assertContains('class="instr"', $output);
        $this->assertContains('class="btn"', $output);
        $this->assertContains('class="do-sequential rein-plugin', $output);
    }

    /**
     * Test that debug interface rotator content prints.
     *
     * @param string $reinoptions Name of theme for setting database values
     * @param string $urlparams Name of database table item to set and test
     * @dataProvider renderer_provider
     */
    public function test_print_debug_rotator($reinoptions, $urlparams) {
        global $CFG;

        $dummypage = new moodle_page();
        $target = 'filter_rein';
        $renderer = new filter_rein_renderer($dummypage, $target);
        $imgpath = $CFG->wwwroot.'/filter/rein/pix/demo/';
        $output = $renderer->print_debug_rotator($reinoptions, $imgpath, $urlparams);

        $this->assertContains('<h2>', $output);
        $this->assertContains('class="desc"', $output);
        $this->assertContains('class="instr"', $output);
        $this->assertContains('class="btn"', $output);
        $this->assertContains('class="rotator rein-plugin', $output);
    }

    /**
     * Test that debug interface MarkIt content prints.
     *
     * @param string $reinoptions Name of theme for setting database values
     * @param string $urlparams Name of database table item to set and test
     * @dataProvider renderer_provider
     */
    public function test_print_debug_markit($reinoptions, $urlparams) {
        global $CFG;

        $dummypage = new moodle_page();
        $target = 'filter_rein';
        $renderer = new filter_rein_renderer($dummypage, $target);
        $imgpath = $CFG->wwwroot.'/filter/rein/pix/demo/';
        $output = $renderer->print_debug_markit($reinoptions, $imgpath, $urlparams);

        $this->assertContains('<h2>', $output);
        $this->assertContains('class="desc"', $output);
        $this->assertContains('class="instr"', $output);
        $this->assertContains('class="btn"', $output);
        $this->assertContains('class="rein-plugin markit', $output);
    }

    /**
     * Test that debug interface tooltip content prints.
     *
     * @param string $reinoptions Name of theme for setting database values
     * @param string $urlparams Name of database table item to set and test
     * @dataProvider renderer_provider
     */
    public function test_print_debug_tooltip($reinoptions, $urlparams) {
        global $CFG;

        $dummypage = new moodle_page();
        $target = 'filter_rein';
        $renderer = new filter_rein_renderer($dummypage, $target);
        $imgpath = $CFG->wwwroot.'/filter/rein/pix/demo/';
        $output = $renderer->print_debug_tooltip($reinoptions, $imgpath, $urlparams);

        $this->assertContains('<h2>', $output);
        $this->assertContains('class="desc"', $output);
        $this->assertContains('class="instr"', $output);
        $this->assertContains('class="btn"', $output);
        $this->assertContains('class="tip-trigger"', $output);
    }

    /**
     * Test that debug interface flipcard content prints.
     *
     * @param string $reinoptions Name of theme for setting database values
     * @param string $urlparams Name of database table item to set and test
     * @dataProvider renderer_provider
     */
    public function test_print_debug_flipcard($reinoptions, $urlparams) {
        global $CFG;

        $dummypage = new moodle_page();
        $target = 'filter_rein';
        $renderer = new filter_rein_renderer($dummypage, $target);
        $output = $renderer->print_debug_flipcard($reinoptions, $urlparams);

        $this->assertContains('<h2>', $output);
        $this->assertContains('class="desc"', $output);
        $this->assertContains('class="instr"', $output);
        $this->assertContains('class="btn"', $output);
        $this->assertContains('class="rein-plugin flipcard-grid doFlipcard"', $output);
    }

    /**
     * Test that debug interface flipcard content prints.
     *
     * @param string $reinoptions Name of theme for setting database values
     * @param string $urlparams Name of database table item to set and test
     * @dataProvider renderer_provider
     */
    public function test_print_debug_overlay($reinoptions, $urlparams) {
        global $CFG;

        $dummypage = new moodle_page();
        $target = 'filter_rein';
        $renderer = new filter_rein_renderer($dummypage, $target);
        $imgpath = $CFG->wwwroot.'/filter/rein/pix/demo/';
        $output = $renderer->print_debug_overlay($reinoptions, $imgpath, $urlparams);

        $this->assertContains('<h2>', $output);
        $this->assertContains('class="desc"', $output);
        $this->assertContains('class="instr"', $output);
        $this->assertContains('class="btn"', $output);
        $this->assertContains('class="rein-plugin overlay"', $output);
    }

    /**
     * Test that debug interface swiper content prints.
     *
     * @param string $reinoptions Name of theme for setting database values
     * @param string $urlparams Name of database table item to set and test
     * @dataProvider renderer_provider
     */
    public function test_print_debug_swiper($reinoptions, $urlparams) {
        global $CFG;

        $dummypage = new moodle_page();
        $target = 'filter_rein';
        $renderer = new filter_rein_renderer($dummypage, $target);
        $imgpath = $CFG->wwwroot.'/filter/rein/pix/demo/';
        $output = $renderer->print_debug_swiper($reinoptions, $imgpath, $urlparams);

        $this->assertContains('<h2>', $output);
        $this->assertContains('class="desc"', $output);
        $this->assertContains('class="instr"', $output);
        $this->assertContains('class="btn"', $output);
        $this->assertContains('class="rein-plugin swiper-container"', $output);
    }

    /**
     * Provider of rein markup output options.
     *
     * @return array Array of rein markup output options
     */
    public function renderer_provider() {

        return array(
            array(
                    array(
                        'noclean' => true,
                        'trusted' => true,
                        'nocache' => true,
                        'allowid' => true
                    ),
                    array(
                        'class' => 'btn',
                        'target' => '_blank'
                    )
            )
        );

    }

}
