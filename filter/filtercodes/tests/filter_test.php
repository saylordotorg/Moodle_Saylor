<?php
// This file is part of FilterCodes for Moodle - http://moodle.org/
//
// FilterCodes is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// FilterCodes is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for FilterCodes filter.
 *
 * @package    filter_filtercodes
 * @copyright  2017-2021 TNG Consulting Inc. - www.tngconsulting.ca
 * @author     Michael Milette
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/filter/filtercodes/filter.php');

/**
 * Unit tests for FilterCodes filter.
 *
 * Test that the filter produces the right content. Note that this currently
 * only tests some of the filter logic. Future releases will test more of the tags.
 *
 * @copyright  2017-2021 TNG Consulting Inc. - www.tngconsulting.ca
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class filter_filtercodes_testcase extends advanced_testcase {

    /**
     * @var filter_filtercode $filter Instance of filtercodes.
     */
    protected $filter;

    /**
     * Setup the test framework
     *
     * @return void
     */
    protected function setUp() {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->filter = new filter_filtercodes(context_system::instance(), []);
    }

    /**
     * Filter test.
     *
     * @return void
     */
    public function test_filter_filtercodes() {
        global $CFG, $USER, $DB, $PAGE, $SITE;

        $PAGE->set_url(new moodle_url('/'));

        $this->setadminuser();
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);
        filter_set_local_state('filtercodes', $context->id, TEXTFILTER_ON);

        $tests = [
            [
                'before' => 'No langx tags',
                'after'  => 'No langx tags',
            ],
            [
                'before' => '{langx es}Todo el texto está en español{/langx}',
                'after'  => '<span lang="es">Todo el texto está en español</span>',
            ],
            [
                'before' => '{langx fr}Ceci est du texte en français{/langx}',
                'after'  => '<span lang="fr">Ceci est du texte en français</span>',
            ],
            [
                'before' => 'Some non-filtered content plus some content in Spanish' .
                        ' ({langx es}mejor dicho, en español{/langx})',
                'after' => 'Some non-filtered content plus some content in Spanish' .
                        ' (<span lang="es">mejor dicho, en español</span>)',
            ],
            [
                'before' => 'Some non-filtered content plus some content in French ({langx fr}mieux en français{/langx})',
                'after'  => 'Some non-filtered content plus some content in French (<span lang="fr">mieux en français</span>)',
            ],
            [
                'before' => '{langx es}Algo de español{/langx}{langx fr}Quelque chose en français{/langx}',
                'after'  => '<span lang="es">Algo de español</span><span lang="fr">Quelque chose en français</span>',
            ],
            [
                'before' => 'Non-filtered {begin}{langx es}Algo de español{/langx}{langx fr}Quelque chose en français{/langx}'.
                        ' Non-filtered{end}',
                'after'  => 'Non-filtered {begin}<span lang="es">Algo de español</span><span lang="fr">Quelque chose en français'.
                        '</span> Non-filtered{end}',
            ],
            [
                'before' => '{langx}Bad filter syntax{langx}',
                'after'  => '{langx}Bad filter syntax{langx}',
            ],
            [
                'before' => '{langx}Bad filter syntax{langx}{langx es}Algo de español{/langx}',
                'after'  => '{langx}Bad filter syntax{langx}<span lang="es">Algo de español</span>',
            ],
            [
                'before' => 'Before {langx}Bad filter syntax{langx} {langx es}Algo de español{/langx} After',
                'after'  => 'Before {langx}Bad filter syntax{langx} <span lang="es">Algo de español</span> After',
            ],
            [
                'before' => 'Before {langx non-existent-language}Some content{/langx} After',
                'after'  => 'Before <span lang="non-existent-language">Some content</span> After',
            ],
            [
                'before' => 'Before {langx en_ca}Some content{/langx} After',
                'after'  => 'Before <span lang="en_ca">Some content</span> After',
            ],
            [
                'before' => 'Before {langx en-ca}Some content{/langx} After',
                'after'  => 'Before <span lang="en-ca">Some content</span> After',
            ],
            [
                'before' => 'Before{nbsp}: Some content After',
                'after'  => 'Before&nbsp;: Some content After',
            ],
            [
                'before' => 'Before{-}: Some content After',
                'after'  => 'Before&shy;: Some content After',
            ],
            [
                'before' => '{firstname}',
                'after'  => $USER->firstname,
            ],
            [
                'before' => '{lastname}',
                'after'  => $USER->lastname,
            ],
            [
                'before' => '{alternatename}',
                'after'  => !empty(trim($USER->alternatename)) ? $USER->alternatename : $USER->firstname,
            ],
            [
                'before' => '{fullname}',
                'after'  => $USER->firstname . ' ' . $USER->lastname,
            ],
            [
                'before' => '{getstring}help{/getstring}',
                'after'  => 'Help',
            ],
            [
                'before' => '{getstring:filter_filtercodes}pluginname{/getstring}',
                'after'  => 'Filter Codes',
            ],
            [
                'before' => '{city}',
                'after'  => $USER->city,
            ],
            [
                'before' => '{country}',
                'after'  => !empty($USER->country) ? get_string($USER->country, 'countries') : '',
            ],
            [
                'before' => '{email}',
                'after'  => $USER->email,
            ],
            [
                'before' => '{userid}',
                'after'  => $USER->id,
            ],
            [
                'before' => '%7Buserid%7D',
                'after'  => $USER->id,
            ],
            [
                'before' => '{idnumber}',
                'after'  => $USER->idnumber,
            ],
            [
                'before' => '{institution}',
                'after'  => $USER->institution,
            ],
            [
                'before' => '{department}',
                'after'  => $USER->department,
            ],
            [
                'before' => '{usercount}',
                'after'  => $DB->count_records('user', ['deleted' => 0]) - 2,
            ],
            [
                'before' => '{usersactive}',
                'after'  => $DB->count_records('user', ['deleted' => 0, 'suspended' => 0, 'confirmed' => 1]) - 2,
            ],
            [
                'before' => '{courseid}',
                'after'  => $PAGE->course->id,
            ],
            [
                'before' => '{courseidnumber}',
                'after'  => $PAGE->course->idnumber,
            ],
            [
                'before' => '%7Bcourseid%7D',
                'after'  => $PAGE->course->id,
            ],
            [
                'before' => '{coursename}',
                'after'  => $PAGE->course->fullname,
            ],
            [
                'before' => '{courseshortname}',
                'after'  => $PAGE->course->shortname,
            ],
            [
                'before' => '{coursecount}',
                'after'  => $DB->count_records('course', []) - 1,
            ],
            [
                'before' => '{coursesactive}',
                'after'  => $DB->count_records('course', ['visible' => 1]) - 1,
            ],
            [
                'before' => '{coursesummary}',
                'after'  => $PAGE->course->summary,
            ],
            [
                'before' => '{siteyear}',
                'after'  => date('Y'),
            ],
            [
                'before' => '{editingtoggle}',
                'after'  => ($PAGE->user_is_editing() ? 'off' : 'on'),
            ],
            [
                'before' => '{wwwroot}',
                'after'  => $CFG->wwwroot,
            ],
            [
                'before' => '{wwwcontactform}',
                'after'  => $CFG->wwwroot . '/local/contact/index.php',
            ],
            [
                'before' => '{protocol}',
                'after'  => 'http' . (is_https() ? 's' : ''),
            ],
            [
                'before' => '{pagepath}',
                'after'  => '/?',
            ],
            [
                'before' => '{ipaddress}',
                'after'  => getremoteaddr(),
            ],
            [
                'before' => '{sesskey}',
                'after'  => sesskey(),
            ],
            [
                'before' => '%7Bsesskey%7D',
                'after'  => sesskey(),
            ],
            [
                'before' => '{sectionid}',
                'after'  => @$PAGE->cm->sectionnum,
            ],
            [
                'before' => '%7Bsectionid%7D',
                'after'  => @$PAGE->cm->sectionnum,
            ],
            [
                'before' => '{readonly}',
                'after'  => 'readonly="readonly"',
            ],
            [
                'before' => '{fa fa-icon-name}',
                'after'  => '<span class="fa fa-icon-name" aria-hidden="true"></span>',
            ],
            [
                'before' => '{glyphicon glyphicon-name}',
                'after'  => '<span class="glyphicon glyphicon-name" aria-hidden="true"></span>',
            ],
        ];

        foreach ($tests as $test) {
            $this->assertEquals($test['after'], $this->filter->filter($test['before']));
        }
    }
}
